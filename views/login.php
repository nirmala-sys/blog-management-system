<?php
session_start();
require '../includes/db.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid login credentials";
    }
}

// Handle Forgot Password
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token and expiry in the database
        $stmt = $pdo->prepare("UPDATE admin_users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);

        // Send reset email
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.yourmailserver.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // Replace with your email
            $mail->Password = 'your_email_password'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@example.com', 'Admin Panel');
            $mail->addAddress($email);

            // Email content
            $resetLink = "http://yourwebsite.com/reset_password.php?token=$token";
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi,<br><br>Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a><br><br>This link will expire in 1 hour.";

            $mail->send();
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Error sending reset email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Admin Login</h2>
        <form method="POST">
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <?php if (isset($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>
            
            <!-- Login Form -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>

        <!-- Forgot Password Section -->
        <hr>
        <h4>Forgot Password?</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Enter Your Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" name="forgot_password" class="btn btn-secondary">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
