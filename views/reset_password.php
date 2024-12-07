<?php
session_start();
require 'includes/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Fetch user by token
    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // If token is valid, show reset form
        if (isset($_POST['reset_password'])) {
            $newPassword = $_POST['password'];
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password and clear token
            $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hashedPassword, $user['id']]);

            echo "Password has been reset. <a href='login.php'>Login now</a>";
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <form method="POST">
        <div>
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</body>
</html>
