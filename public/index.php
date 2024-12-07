<?php
// Initializing the search term (optional, as we fetch from the API)
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Gradient styling */
        .navbar {
            background: linear-gradient(90deg, navy, blue);
        }
        .navbar a {
            color: white !important;
        }
        .header {
            background: linear-gradient(135deg, blue, navy);
            color: white;
            padding: 10px;
            text-align: center;
        }
        .footer {
            background: linear-gradient(90deg, blue, navy);
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 10px;
        }
        /* Blog card styling */
        .card {
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            font-size: 0.9rem; /* Smaller font size */
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .card-text {
            font-size: 0.9rem;
            color: #555;
        }
        .published-info {
            text-align: right; /* Align to the right */
            font-size: 1rem;
            color: #000000;
        }
        .search-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->

    <!-- Header -->
    <div class="header">
        <h1>Welcome to the Professional Blog</h1>
        <p>Explore articles on various topics!</p>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Search Bar -->
        <form method="GET" class="search-bar">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search blogs..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <!-- Blog Cards -->
        <div id="blog-posts-container"></div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center" id="pagination-container"></ul>
        </nav>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 Professional Blog. All rights reserved.</p>
    </div>

    <script>
        // Fetch blog posts and handle pagination using the API
        const searchQuery = new URLSearchParams(window.location.search).get('search') || '';
        const currentPage = new URLSearchParams(window.location.search).get('page') || 1;

        function fetchBlogs() {
            fetch(`../api/postsController.php?search=${searchQuery}&page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const postsContainer = document.getElementById('blog-posts-container');
                        postsContainer.innerHTML = ''; // Clear existing posts

                        // Append blog posts
                        data.data.forEach(post => {
                            const postHTML = `
                                <div class="card">
    <div class="card-body d-flex justify-content-between">
        <div class="left-content">
            <h5 class="card-title">${post.title}</h5>
            <p class="card-text">${post.content.substring(0, 150)}...</p>
            <p class="published-info">
                <small>By ${post.author} | ${post.publish_date}</small>
            </p>
            <a href="single.php?id=${post.id}" class="btn btn-primary btn-sm">Read More</a>
        </div>
        <div class="right-content">
            <span class="badge bg-primary">${post.category_name}</span>
        </div>
    </div>
</div>

                            `;
                            postsContainer.innerHTML += postHTML;
                        });

                        // Handle pagination
                        const paginationContainer = document.getElementById('pagination-container');
                        paginationContainer.innerHTML = '';

                        // Previous button
                        const prevPage = currentPage > 1 ? currentPage - 1 : 1;
                        paginationContainer.innerHTML += `
                            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="?page=${prevPage}&search=${searchQuery}">Previous</a>
                            </li>
                        `;

                        // Page numbers
                        for (let i = 1; i <= data.totalPages; i++) {
                            paginationContainer.innerHTML += `
                                <li class="page-item ${currentPage == i ? 'active' : ''}">
                                    <a class="page-link" href="?page=${i}&search=${searchQuery}">${i}</a>
                                </li>
                            `;
                        }

                        // Next button
                        const nextPage = currentPage < data.totalPages ? currentPage + 1 : data.totalPages;
                        paginationContainer.innerHTML += `
                            <li class="page-item ${currentPage >= data.totalPages ? 'disabled' : ''}">
                                <a class="page-link" href="?page=${nextPage}&search=${searchQuery}">Next</a>
                            </li>
                        `;
                    }
                })
                .catch(error => console.error('Error fetching blog posts:', error));
        }

        // Fetch blogs on page load
        fetchBlogs();
    </script>
</body>
</html>
