<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../views/login.php');
    exit;
}

require '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the user from the database
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect to the user management section after deletion
    header('Location: ../views/dashboard.php?section=user');
    exit;
} else {
    // If no ID is provided, redirect to the user management section
    header('Location: ../views/dashboard.php?section=user');
    exit;
}
?>
