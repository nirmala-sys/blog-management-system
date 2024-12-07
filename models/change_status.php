<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../public/login.php');
    exit;
}

require '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the current status of the user
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user) {
        // Toggle the status (active <-> inactive)
        $newStatus = ($user['status'] == 'active') ? 'inactive' : 'active';

        // Update the user's status in the database
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        // Redirect back to the user management page
        header('Location: ../views/dashboard.php?section=user');
        exit;
    }
}

// If no ID is provided or the user does not exist, redirect to the user management section
header('Location: ../views/dashboard.php?section=user');
exit;
?>
