<?php
require '../includes/db.php';

// Only allow admins to access this page
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../views/login.php");
    exit();
}

// Fetch unapproved comments from the database
$stmt = $pdo->prepare("SELECT * FROM comments WHERE approved = 0 ORDER BY created_at DESC");
$stmt->execute();
$comments = $stmt->fetchAll();

// Approve comment
if (isset($_GET['approve'])) {
    $commentId = (int)$_GET['approve'];
    $updateStmt = $pdo->prepare("UPDATE comments SET approved = 1 WHERE id = :id");
    $updateStmt->execute(['id' => $commentId]);
    header("Location: comment_moderation.php");
    exit();
}

// Delete comment
if (isset($_GET['delete'])) {
    $commentId = (int)$_GET['delete'];
    $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
    $deleteStmt->execute(['id' => $commentId]);
    header("Location: comment_moderation.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Moderation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Comment Moderation</h2>

        <!-- Comment Table -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Comment</th>
                    <th>Author</th>
                    <th>Posted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($comments): ?>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= htmlspecialchars($comment['id']) ?></td>
                            <td><?= htmlspecialchars(substr($comment['content'], 0, 50)) ?>...</td>
                            <td><?= htmlspecialchars($comment['name']) ?></td>
                            <td><?= htmlspecialchars($comment['created_at']) ?></td>
                            <td>
                                <a href="models/comment_moderation.php?approve=<?= $comment['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                <a href="models/comment_moderation.php?delete=<?= $comment['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No unapproved comments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
