<?php
require '../includes/db.php';

if (!isset($_GET['id'])) {
    echo "Blog ID is missing!";
    exit;
}

$blogId = (int)$_GET['id'];

// Fetch blog details
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $blogId]);
$blog = $stmt->fetch();

if (!$blog) {
    echo "Blog not found!";
    exit;
}

// Fetch comments for the blog
$commentsStmt = $pdo->prepare("SELECT * FROM comments WHERE blog_id = :blog_id ORDER BY created_at DESC");
$commentsStmt->execute(['blog_id' => $blogId]);
$comments = $commentsStmt->fetchAll();

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $name = $_POST['name'] ?? 'Anonymous';
    $content = $_POST['content'] ?? '';

    if (!empty($content)) {
        $insertComment = $pdo->prepare("INSERT INTO comments (blog_id, user_name, comment,approved, created_at) VALUES (:blog_id, :name, :content,0, NOW())");
        $insertComment->execute([
            'blog_id' => $blogId,
            'name' => htmlspecialchars($name),
            'content' => htmlspecialchars($content),
        ]);
        header("Location: single.php?id=$blogId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Gradient Styling */
        .navbar, .footer {
            background: linear-gradient(90deg, #1e3c72, #2a5298); /* Dark Blue Gradient */
            color: white;
        }
        .navbar a, .footer {
            color: white !important;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72, #2a5298); /* Purple to Blue Gradient */
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 0px;
            text-align:center;
        }
        /* Content Styling */
        .content-header {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .blog-content {
            font-size: 1.1rem;
            color: #444;
            line-height: 1.8;
        }
        .publisher-info {
            font-size: 1rem;
            color: white;
        }
        .maincontainer {
            margin:10px;
            padding: 10px;
            
        }
        /* Blog Section */
        .content-section {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        /* Content Column Styling */
        .blog-column {
            flex: 0 0 70%;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #2a5298;
        }
        /* Comment Section */
        .comment-column {
            flex: 0 0 28%;
            background: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #2a5298;
        }
        .comment-form {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .comments-section {
            margin-top: 20px;
            height: 350px;
            overflow-y: scroll;
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .comment {
            margin-bottom: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-left: 5px solid #2a5298;
            border-radius: 8px;
        }
        .comment .comment-author {
            font-weight: bold;
            font-size: 1rem;
            color: #333;
        }
        .comment .comment-content {
            font-size: 0.9rem;
            color: #555;
        }
        /* New Comment Form */
        .comment-form textarea {
            resize: none;
        }
        /* Footer Styling */
        .footer {
            padding: 20px;
            font-size: 1rem;
            text-align: center;
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <!-- <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?= htmlspecialchars($blog['title']) ?></a>
            <p class="publisher-info">
            Published by <?= htmlspecialchars($blog['author']) ?> on <?= htmlspecialchars($blog['publish_date']) ?>
        </p>
        </div>
    </nav> -->

    <!-- Header -->
    <div class="header">
        <h1 class="content-header"><?= htmlspecialchars($blog['title']) ?></h1>
        <p class="publisher-info">
            Published by <?= htmlspecialchars($blog['author']) ?> on <?= htmlspecialchars($blog['publish_date']) ?>
        </p>
    </div>

    <!-- Main Content -->
    <div class="maincontainer">
        <div class="content-section">
            <!-- Blog Content -->
            <div class="blog-column">
                <div class="blog-content">
                    <?= nl2br(htmlspecialchars($blog['content'])) ?>
                </div>
            </div>

            <!-- Comment Section -->
            <div class="comment-column">
                <!-- New Comment Form -->
                <div class="comment-form">
                    <h5>Leave a Comment</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Your name (optional)">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Comment</label>
                            <textarea name="content" id="content" rows="2" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="comment" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>

                <!-- Previous Comments -->
                <div class="comments-section">
                    <h5>Previous Comments</h5>
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <p class="comment-author"><?= htmlspecialchars($comment['user_name']) ?> <small class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></small></p>
                                <p class="comment-content"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 Professional Blog. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
