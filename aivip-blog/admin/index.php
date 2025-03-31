<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin or author
if (!in_array($_SESSION['role'], ['admin', 'author'])) {
    header('Location: ../index.php');
    exit;
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIVIP Blog Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            position: relative;
            z-index: 1030;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
        }
        .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .main-content {
            min-height: 100vh;
            background: #f8f9fa;
        }
        /* Dropdown styles */
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            position: absolute;
            z-index: 1035;
            min-width: 10rem;
            margin-top: 0.125rem;
        }
        .dropdown-toggle {
            white-space: nowrap;
        }
        .dropdown-toggle:after {
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-4">AIVIP Blog</span>
                    </a>
                    <hr class="text-white">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="?page=dashboard" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?page=posts" class="nav-link <?php echo $current_page === 'posts' ? 'active' : ''; ?>">
                                <i class="bi bi-file-text me-2"></i>
                                Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?page=new-post" class="nav-link <?php echo $current_page === 'new-post' ? 'active' : ''; ?>">
                                <i class="bi bi-plus-circle me-2"></i>
                                New Post
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a href="?page=users" class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                                <i class="bi bi-people me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?page=categories" class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>">
                                <i class="bi bi-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?page=subscribers" class="nav-link <?php echo $current_page === 'subscribers' ? 'active' : ''; ?>">
                                <i class="bi bi-envelope me-2"></i>
                                Subscribers
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="?page=media" class="nav-link <?php echo $current_page === 'media' ? 'active' : ''; ?>">
                                <i class="bi bi-images me-2"></i>
                                Media
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="?page=profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <?php
                switch($current_page) {
                    case 'dashboard':
                        include 'pages/dashboard.php';
                        break;
                    case 'posts':
                        include 'pages/posts.php';
                        break;
                    case 'new-post':
                        include 'pages/new-post.php';
                        break;
                    case 'users':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/users.php';
                        }
                        break;
                    case 'media':
                        include 'pages/media.php';
                        break;
                    case 'profile':
                        include 'pages/profile.php';
                        break;
                    case 'categories':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/categories.php';
                        }
                        break;
                    case 'subscribers':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/subscribers.php';
                        }
                        break;
                    default:
                        include 'pages/dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/tinymce/tinymce.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // Initialize all dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>
</body>
</html> 