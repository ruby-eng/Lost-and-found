<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'] ?? null;
    $item_type = $_POST['item_type'] ?? null;
    $description = $_POST['description'] ?? '';
    $details = $_POST['details'] ?? '';

    $image_path = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image_path = 'uploads/' . $fileName;
        }
    }

    // Save claim to database
    try {
        $stmt = $pdo->prepare('INSERT INTO claims (item_id, item_type, claimer_id, details) VALUES (?, ?, ?, ?)');
        $stmt->execute([$item_id, $item_type, $user_id, $details]);

        // Notify the item owner via notification system
        if ($item_type === 'lost') {
            $item_stmt = $pdo->prepare('SELECT user_id FROM lost_items WHERE id = ?');
            $item_stmt->execute([$item_id]);
        } else {
            $item_stmt = $pdo->prepare('SELECT user_id FROM found_items WHERE id = ?');
            $item_stmt->execute([$item_id]);
        }
        
        $item = $item_stmt->fetch();
        if ($item) {
            $notif_stmt = $pdo->prepare('INSERT INTO notifications (user_id, sender_id, type, title, message, related_item_id) VALUES (?, ?, ?, ?, ?, ?)');
            $notif_stmt->execute([
                $item['user_id'],
                $user_id,
                'claim',
                'New Claim on Your Item',
                'Someone claimed your ' . ($item_type === 'lost' ? 'lost' : 'found') . ' item. Please review.',
                $item_id
            ]);
        }

        // Redirect to success page
        header("Location: claim_submitted.html");
        exit();
    } catch (PDOException $e) {
        die('Error submitting claim: ' . $e->getMessage());
    }
}
?>