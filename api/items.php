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

switch ($action) {
    case 'get_lost_items':
        getLostItems();
        break;
    case 'get_found_items':
        getFoundItems();
        break;
    case 'get_item_details':
        getItemDetails();
        break;
    case 'search_items':
        searchItems();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getLostItems() {
    global $pdo;
    try {
        $stmt = $pdo->prepare('
            SELECT li.*, u.name as reporter_name, u.email, u.phone 
            FROM lost_items li 
            JOIN users u ON li.user_id = u.id 
            WHERE li.status = "open"
            ORDER BY li.created_at DESC 
            LIMIT 50
        ');
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getFoundItems() {
    global $pdo;
    try {
        $stmt = $pdo->prepare('
            SELECT fi.*, u.name as reporter_name, u.email, u.phone 
            FROM found_items fi 
            JOIN users u ON fi.user_id = u.id 
            WHERE fi.status = "available"
            ORDER BY fi.created_at DESC 
            LIMIT 50
        ');
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getItemDetails() {
    global $pdo;
    $item_id = $_GET['item_id'] ?? null;
    $item_type = $_GET['item_type'] ?? 'lost';

    if (!$item_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing item_id']);
        return;
    }

    try {
        if ($item_type === 'found') {
            $stmt = $pdo->prepare('
                SELECT fi.*, u.name as reporter_name, u.email, u.phone 
                FROM found_items fi 
                JOIN users u ON fi.user_id = u.id 
                WHERE fi.id = ?
            ');
        } else {
            $stmt = $pdo->prepare('
                SELECT li.*, u.name as reporter_name, u.email, u.phone 
                FROM lost_items li 
                JOIN users u ON li.user_id = u.id 
                WHERE li.id = ?
            ');
        }
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            return;
        }

        echo json_encode($item);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function searchItems() {
    global $pdo;
    $query = $_GET['q'] ?? '';
    $type = $_GET['type'] ?? 'all';

    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing search query']);
        return;
    }

    try {
        $results = [];
        $search_term = '%' . $query . '%';

        if ($type === 'lost' || $type === 'all') {
            $stmt = $pdo->prepare('
                SELECT id, description, color, object_type, image_path, "lost" as type, created_at 
                FROM lost_items 
                WHERE (description LIKE ? OR object_type LIKE ? OR color LIKE ?) 
                AND status = "open"
                LIMIT 20
            ');
            $stmt->execute([$search_term, $search_term, $search_term]);
            $results = array_merge($results, $stmt->fetchAll());
        }

        if ($type === 'found' || $type === 'all') {
            $stmt = $pdo->prepare('
                SELECT id, description, color, object_type, image_path, "found" as type, created_at 
                FROM found_items 
                WHERE (description LIKE ? OR object_type LIKE ? OR color LIKE ?) 
                AND status = "available"
                LIMIT 20
            ');
            $stmt->execute([$search_term, $search_term, $search_term]);
            $results = array_merge($results, $stmt->fetchAll());
        }

        echo json_encode($results);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
