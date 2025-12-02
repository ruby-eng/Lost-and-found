<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_all_claims':
        getAllClaims();
        break;
    case 'broadcast_message':
        broadcastMessage();
        break;
    case 'get_statistics':
        getStatistics();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getAllClaims() {
    global $pdo;
    try {
        $status = $_GET['status'] ?? null;
        
        $query = '
            SELECT c.*, 
                   CASE 
                       WHEN c.item_type = "lost" THEN li.description 
                       ELSE fi.description 
                   END as item_description,
                   claimer.name as claimer_name,
                   owner.name as item_owner_name
            FROM claims c 
            LEFT JOIN lost_items li ON c.item_type = "lost" AND c.item_id = li.id 
            LEFT JOIN found_items fi ON c.item_type = "found" AND c.item_id = fi.id 
            LEFT JOIN users claimer ON c.claimer_id = claimer.id 
            LEFT JOIN users owner ON (li.user_id = owner.id OR fi.user_id = owner.id)
        ';
        
        if ($status) {
            $query .= ' WHERE c.status = ?';
            $stmt = $pdo->prepare($query . ' ORDER BY c.created_at DESC');
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->prepare($query . ' ORDER BY c.created_at DESC');
            $stmt->execute();
        }
        
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function broadcastMessage() {
    global $pdo, $user_id;
    
    $title = $_POST['title'] ?? null;
    $message = $_POST['message'] ?? null;

    if (!$title || !$message) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing title or message']);
        return;
    }

    try {
        // Send notification to all users
        $users_stmt = $pdo->prepare('SELECT id FROM users WHERE id != ?');
        $users_stmt->execute([$user_id]);
        $users = $users_stmt->fetchAll();

        $notif_stmt = $pdo->prepare('INSERT INTO notifications (user_id, sender_id, type, title, message) VALUES (?, ?, ?, ?, ?)');
        
        foreach ($users as $user) {
            $notif_stmt->execute([$user['id'], $user_id, 'broadcast', $title, $message]);
        }

        echo json_encode(['success' => true, 'recipients' => count($users)]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getStatistics() {
    global $pdo;
    try {
        $stats = [];

        // Total users
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
        $stats['total_users'] = $stmt->fetch()['count'];

        // Open lost items
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM lost_items WHERE status = "open"');
        $stats['open_lost_items'] = $stmt->fetch()['count'];

        // Available found items
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM found_items WHERE status = "available"');
        $stats['available_found_items'] = $stmt->fetch()['count'];

        // Pending claims
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM claims WHERE status = "pending"');
        $stats['pending_claims'] = $stmt->fetch()['count'];

        // Verified claims
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM claims WHERE status = "verified"');
        $stats['verified_claims'] = $stmt->fetch()['count'];

        echo json_encode($stats);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
