<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require '../includes/db.php';

// Pagination setup
$limit = 10; // Number of items per page

// Get the current page from the URL, default is 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get the section parameter from the URL (defaults to 'blog' if not set)
$section = isset($_GET['section']) ? $_GET['section'] : 'blog';
$activityStmt = $pdo->prepare("INSERT INTO activity_log (action, created_at) VALUES (:action, NOW())");
$activityStmt->execute(['action' => 'Admin action details here']);

// Fetch blogs, users, or comments based on section
if ($section == 'blog') {
    // Fetch blogs with pagination
    $stmt = $pdo->prepare("SELECT * FROM blogs LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $blogs = $stmt->fetchAll();

    // Count the total number of blogs for pagination
    $total_blogs = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $total_pages = ceil($total_blogs / $limit);
} elseif ($section == 'user') {
    // Fetch users with pagination
    $stmt = $pdo->prepare("SELECT * FROM users LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Count the total number of users for pagination
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_pages = ceil($total_users / $limit);
} elseif ($section == 'comment') {
    // Fetch unapproved comments for moderation
    $stmt = $pdo->prepare("SELECT * FROM comments LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll();

    // Count the total number of unapproved comments for pagination
    $total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $total_pages = ceil($total_comments / $limit);

    // Approve comment
    if (isset($_GET['approve'])) {
        $commentId = (int)$_GET['approve'];
        $updateStmt = $pdo->prepare("UPDATE comments SET approved = 1 WHERE id = :id");
        $updateStmt->execute(['id' => $commentId]);
        header("Location: dashboard.php?section=comment");
        exit();
    }

    // Delete comment
    if (isset($_GET['delete'])) {
        $commentId = (int)$_GET['delete'];
        $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
        $deleteStmt->execute(['id' => $commentId]);
        header("Location: dashboard.php?section=comment");
        exit();
    }
}
    // Fetch categories with pagination (for category management)
if ($section == 'category') {
    // Fetch categories with pagination
    $stmt = $pdo->prepare("SELECT * FROM categories LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Count the total number of categories for pagination
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $total_pages = ceil($total_categories / $limit);

    // Add a new category
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'];
        $insertStmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $insertStmt->execute(['name' => $category_name]);

        // Log activity
        $activityStmt = $pdo->prepare("INSERT INTO activity_log (action, created_at) VALUES (:action, NOW())");
        $activityStmt->execute(['action' => 'Added a new category: ' . $category_name]);

        header("Location: dashboard.php?section=category");
        exit();
    }

    // Delete category
    if (isset($_GET['delete_category'])) {
        $categoryId = (int)$_GET['delete_category'];
        $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $deleteStmt->execute(['id' => $categoryId]);

        // Log activity
        $activityStmt = $pdo->prepare("INSERT INTO activity_log (action, created_at) VALUES (:action, NOW())");
        $activityStmt->execute(['action' => 'Deleted category with ID: ' . $categoryId]);

        header("Location: dashboard.php?section=category");
        exit();
    }
}
    if ($section == 'activity_log') {
        // Fetch the activity logs with pagination
        $stmt = $pdo->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();
    
        // Count the total number of logs for pagination
        $total_logs = $pdo->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
        $total_pages = ceil($total_logs / $limit);
    }
    



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        .container-fluid {
            height: 100%;
        }
        .row {
            height: 100%;
        }
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
        }
        #content {
            margin-left: 250px;
            padding: 20px;
        }
        .list-group-item a {
            color: #000000;
            text-decoration: none;
            display: block;
            padding: 5px 10px;
        }
        .list-group-item:hover {
            background-color: #555;
        }
        .pagination a {
            color: #000;
        }
        .pagination .active a {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div id="sidebar" class="col-md-3">
                <h5 style="color: #fff; font-size: 1.5rem; text-align: center; margin-bottom: 30px;">Admin Dashboard</h5>
                <ul class="list-group">
                    <li class="list-group-item">
                        <a href="dashboard.php?section=blog" class="list-group-item-action">Blog Management</a>
                    </li>
                    <li class="list-group-item">
                        <a href="dashboard.php?section=user" class="list-group-item-action">User Management</a>
                    </li>
                    <li class="list-group-item">
                        <a href="dashboard.php?section=comment" class="list-group-item-action">Comment Moderation</a>
                    </li>
                    <li class="list-group-item">
    <a href="dashboard.php?section=category" class="list-group-item-action">Category Management</a>
</li>
<li class="list-group-item">
    <a href="dashboard.php?section=activity_log" class="list-group-item-action">Activity Log</a>
</li>

                    <li class="list-group-item">
                        <a href="logout.php" class="list-group-item-action">Logout</a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div id="content" class="col-md-9">
                <?php if ($section == 'blog'): ?>
                    <h2>Blog Management</h2>
                    <a href="../models/create_blog.php" class="btn btn-primary mb-3">Create New Blog</a>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['category']); ?></td>
                                    <td>
                                        <a href="../models/edit_blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="../models/delete_blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif ($section == 'user'): ?>
                    <h2>User Management</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                                    <td>
                                        <a href="../models/change_status.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">
                                            <?php echo ($user['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="../models/delete_user.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif ($section == 'comment'): ?>
                    <h2>Comment Moderation</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Comment</th>
                                <th>Author</th>
                                <th>Posted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($comment['comment'], 0, 50)); ?>...</td>
                                    <td><?php echo htmlspecialchars($comment['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($comment['created_at']); ?></td>
                                    <td>
                                        <a href="?section=comment&approve=<?php echo $comment['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                        <a href="?section=comment&delete=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php elseif ($section == 'category'): ?>
    <h2>Category Management</h2>
    <form method="POST" action="dashboard.php?section=category" class="mb-3">
        <input type="text" name="category_name" class="form-control" placeholder="New Category Name" required>
        <button type="submit" name="add_category" class="btn btn-primary mt-2">Add Category</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td>
                        <a href="?section=category&delete_category=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ($section == 'activity_log'): ?>
    <h2>Activity Log</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Action</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

                
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?section=<?php echo $section; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
