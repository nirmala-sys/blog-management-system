<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require '../includes/db.php';

// Fetch categories from the database
$stmt = $pdo->prepare("SELECT id, name FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = 'uploads/' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $imagePath);
    } else {
        $imagePath = null;
    }

    // Insert blog data into the database
    $stmt = $pdo->prepare("INSERT INTO blogs (title, content, author, category, image, publish_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $author, $category, $imagePath]);

    // Redirect to the blog management page after saving
    header('Location: ../views/dashboard.php?section=blog');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Blog</title>
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
            height: 100vh; /* Full viewport height */
            background-color: #343a40;
            padding-top: 20px;
        }

        #maincontent {
            margin-left: 300px; /* Offset the content to the right of the sidebar */
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div id="sidebar" class="col-md-3 bg-dark text-white p-3">
                <h5>Admin Dashboard</h5>
                <ul class="list-group">
                    <li class="list-group-item bg-dark text-white">
                        <a href="../views/dashboard.php?section=blog" class="text-white">Blog Management</a>
                    </li>
                    <li class="list-group-item bg-dark text-white">
                        <a href="../views/dashboard.php?section=user" class="text-white">User Management</a>
                    </li>
                    <li class="list-group-item bg-dark text-white">
                        <a href="../views/logout.php" class="text-white">Logout</a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div id="maincontent" class="col-md-9 p-4">
                <h2>Create New Blog</h2>
                <form method="POST" action="create_blog.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" id="author" name="author" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Blog</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
