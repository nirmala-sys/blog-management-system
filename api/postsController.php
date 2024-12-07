<?php
require '../includes/db.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Search functionality
$search = $_GET['search'] ?? '';

// Pagination setup
$limit = 5; // Number of blogs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch blogs from the database
$stmt = $pdo->prepare("
    SELECT blogs.*, categories.name AS category_name 
    FROM blogs
    LEFT JOIN categories ON blogs.category = categories.id
    WHERE blogs.title LIKE :search
    ORDER BY blogs.publish_date DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of blogs for pagination
$totalBlogs = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE title LIKE :search");
$totalBlogs->execute(['search' => "%$search%"]);
$totalPages = ceil($totalBlogs->fetchColumn() / $limit);

// Return the results as JSON
echo json_encode([
    'status' => 'success',
    'data' => $blogs,
    'totalPages' => $totalPages
]);
?>
