<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_my_claims':
        getMyClaims();
        break;
    case 'get_claims_on_my_items':
        getClaimsOnMyItems();
        break;
    case 'update_claim_status':
        updateClaimStatus();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getMyClaims() {
    global $pdo, $user_id;
    try {
        $stmt = $pdo->prepare('
            SELECT c.*, 
                   CASE 
                       WHEN c.item_type = "lost" THEN li.description 
                       ELSE fi.description 
                   END as item_description,
                   CASE 
                       WHEN c.item_type = "lost" THEN li.image_path 
                       ELSE fi.image_path 
                   END as item_image,
                   u.name as item_owner_name
            FROM claims c 
            LEFT JOIN lost_items li ON c.item_type = "lost" AND c.item_id = li.id 
            LEFT JOIN found_items fi ON c.item_type = "found" AND c.item_id = fi.id 
            LEFT JOIN users u ON (li.user_id = u.id OR fi.user_id = u.id)
            WHERE c.claimer_id = ?
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([$user_id]);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getClaimsOnMyItems() {
    global $pdo, $user_id;
    try {
        $stmt = $pdo->prepare('
            SELECT c.*, 
                   CASE 
                       WHEN c.item_type = "lost" THEN li.description 
                       ELSE fi.description 
                   END as item_description,
                   u.name as claimer_name,
                   u.email as claimer_email,
                   u.phone as claimer_phone
            FROM claims c 
            LEFT JOIN lost_items li ON c.item_type = "lost" AND c.item_id = li.id 
            LEFT JOIN found_items fi ON c.item_type = "found" AND c.item_id = fi.id 
            LEFT JOIN users u ON c.claimer_id = u.id 
            WHERE (li.user_id = ? OR fi.user_id = ?)
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([$user_id, $user_id]);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function updateClaimStatus() {
    global $pdo, $user_id;
    
    $claim_id = $_POST['claim_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$claim_id || !$status) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing claim_id or status']);
        return;
    }

    if (!in_array($status, ['pending', 'verified', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }

    try {
        // Verify the user owns the item
        $stmt = $pdo->prepare('
            SELECT c.*, li.user_id as lost_owner, fi.user_id as found_owner
            FROM claims c 
            LEFT JOIN lost_items li ON c.item_type = "lost" AND c.item_id = li.id 
            LEFT JOIN found_items fi ON c.item_type = "found" AND c.item_id = fi.id 
            WHERE c.id = ?
        ');
        $stmt->execute([$claim_id]);
        $claim = $stmt->fetch();

        if (!$claim) {
            http_response_code(404);
            echo json_encode(['error' => 'Claim not found']);
            return;
        }

        $owner_id = $claim['item_type'] === 'lost' ? $claim['lost_owner'] : $claim['found_owner'];
        if ($owner_id !== $user_id) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Update claim status
        $update_stmt = $pdo->prepare('
            UPDATE claims 
            SET status = ?, verification_notes = ?, verified_by = ?, verified_at = NOW()
            WHERE id = ?
        ');
        $update_stmt->execute([$status, $notes, $user_id, $claim_id]);

        // If verified, update item status
        if ($status === 'verified') {
            if ($claim['item_type'] === 'lost') {
                $item_stmt = $pdo->prepare('UPDATE lost_items SET status = "claimed" WHERE id = ?');
            } else {
                $item_stmt = $pdo->prepare('UPDATE found_items SET status = "claimed" WHERE id = ?');
            }
            $item_stmt->execute([$claim['item_id']]);
        }

        echo json_encode(['success' => true, 'message' => 'Claim status updated']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
