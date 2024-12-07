<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../views/login.php');
    exit;
}

require '../includes/db.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../views/dashboard.php?section=blog');
    exit;
}

$blogId = $_GET['id'];

// Fetch the blog from the database
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$blogId]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: ../views/dashboard.php?section=blog');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    
    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = 'uploads/' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $imagePath);
    } else {
        $imagePath = $blog['image']; // Keep the existing image if no new image is uploaded
    }

    // Update the blog data in the database
    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, content = ?, author = ?, category = ?, image = ? WHERE id = ?");
    $stmt->execute([$title, $content, $author, $category, $imagePath, $blogId]);

    // Redirect to the blog management page after updating
    header('Location: ../views/dashboard.php?section=blog');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
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
      
        .list-group-item a {
            color: #000000;
            text-decoration: none;
            display: block;
            padding: 5px 10px;
        }
        .list-group-item:hover {
            background-color: #555;
        }


#maincontent {
    margin-left: 250px; /* Offset the content to the right of the sidebar */
    padding: 20px;
}

        </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div id="sidebar" class="col-md-3 bg-dark text-white p-3">
                <h3>Admin Dashboard</h3>
                <ul class="list-group">
                    <li class="list-group-item">
                        <a href="../views/dashboard.php?section=blog" class="list-group-item-action">Blog Management</a>
                    </li>
                    <li class="list-group-item">
                        <a href="../views/dashboard.php?section=user" class="list-group-item-action">User Management</a>
                    </li>
                    <li class="list-group-item">
                        <a href="../views/dashboard.php?section=comment" class="list-group-item-action">Comment Moderation</a>
                    </li>
                    <li class="list-group-item">
    <a href="../views/dashboard.php?section=category" class="list-group-item-action">Category Management</a>
</li>
<li class="list-group-item">
    <a href="../views/dashboard.php?section=activity_log" class="list-group-item-action">Activity Log</a>
</li>
                    <li class="list-group-item">
                        <a href="../views/logout.php" class="list-group-item-action">Logout</a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div id="maincontent" class="col-md-9 p-4">
                <h2>Edit Blog</h2>
                <form method="POST" action="edit_blog.php?id=<?php echo $blog['id']; ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="3" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($blog['author']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($blog['category']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                        <small>Current Image: <img src="../<?php echo $blog['image']; ?>" alt="Blog Image" width="100"></small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Blog</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
