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
    case 'get_notifications':
        getNotifications();
        break;
    case 'mark_as_read':
        markAsRead();
        break;
    case 'get_unread_count':
        getUnreadCount();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function getNotifications() {
    global $pdo, $user_id;
    try {
        $limit = $_GET['limit'] ?? 20;
        $stmt = $pdo->prepare('
            SELECT n.*, u.name as sender_name 
            FROM notifications n 
            LEFT JOIN users u ON n.sender_id = u.id 
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$user_id, (int)$limit]);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function markAsRead() {
    global $pdo, $user_id;
    $notification_id = $_POST['notification_id'] ?? null;

    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id']);
        return;
    }

    try {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?');
        $stmt->execute([$notification_id, $user_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getUnreadCount() {
    global $pdo, $user_id;
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE');
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        echo json_encode(['unread_count' => $result['count']]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
