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
<html lang="en"></html>
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
            position: fixed;
            z-index: 1030;
            width: 250px;
            overflow-y: auto;
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
            margin-left: 250px;
            width: calc(100% - 250px);
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
                        <img src="assets/images/logo sidebar.svg" alt="AIVIP Blog" style="height: 100px;">
                    </a>
                    <hr class="text-white">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'posts' ? 'active' : ''; ?>" href="?page=posts">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'new-post' ? 'active' : ''; ?>" href="?page=new-post">
                                <i class="bi bi-plus-circle me-2"></i>
                                New Post
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>" href="?page=users">
                                <i class="bi bi-people me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>" href="?page=categories">
                                <i class="bi bi-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'newsletter' ? 'active' : ''; ?>" href="?page=newsletter">
                                <i class="bi bi-envelope me-2"></i>
                                Newsletter
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'contact-form' ? 'active' : ''; ?>" href="?page=contact-form">
                                <i class="bi bi-chat-dots me-2"></i>
                                Contact Form
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'digital_analysis' ? 'active' : ''; ?>" href="?page=digital_analysis">
                                <i class="bi bi-graph-up me-2"></i>
                                Digital Analysis
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'media' ? 'active' : ''; ?>" href="?page=media">
                                <i class="bi bi-image me-2"></i>
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
                    case 'newsletter':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/newsletter.php';
                        }
                        break;
                    case 'contact-form':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/contact-form.php';
                        }
                        break;
                    case 'digital_analysis':
                        if ($_SESSION['role'] === 'admin') {
                            include 'pages/digital_analysis.php';
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