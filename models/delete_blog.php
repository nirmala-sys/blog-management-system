<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../views/login.php');
    exit;
}

require '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the blog from the database
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect to the dashboard after deletion
    header('Location: ../views/dashboard.php?section=blog');
    exit;
} else {
    // If no ID is provided, redirect to the dashboard
    header('Location: ../views/dashboard.php?section=blog');
    exit;
}
?>
