<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $description = $_POST['description'] ?? '';
    $color = $_POST['color'] ?? '';
    $object = $_POST['object'] ?? '';
    $location = $_POST['location'] ?? '';
    $date_found = $_POST['date_found'] ?? date('Y-m-d');

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

    // Save data to database
    try {
        $stmt = $pdo->prepare('INSERT INTO found_items (user_id, description, color, object_type, location_found, date_found, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $description, $color, $object, $location, $date_found, $image_path]);

        // Redirect to success page
        header("Location: report_submitted.html");
        exit();
    } catch (PDOException $e) {
        die('Error saving report: ' . $e->getMessage());
    }
}
?>