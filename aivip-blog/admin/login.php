<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token'])) {
    require_once '../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT u.id, u.username, u.role FROM users u 
                           JOIN remember_tokens rt ON u.id = rt.user_id 
                           WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 'active'");
    $stmt->bind_param("s", $_COOKIE['remember_token']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        
        header('Location: index.php');
        exit;
    }
    
    $db->closeConnection();
}

// Initialize error message
$error = '';
$isLocked = false;
$debug_info = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../config/database.php';
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) ? true : false;
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                throw new Exception("Failed to connect to database");
            }
            
            // Check for too many failed attempts
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                                  WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("s", $ip);
            $stmt->execute();
            $attempts = $stmt->get_result()->fetch_assoc()['attempts'];
            
            if ($attempts >= 5) {
                $error = 'Too many failed attempts. Please try again in 15 minutes';
                $isLocked = true;
            } else {
                // Check if input is email or username
                $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("ss", $username, $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($user = $result->fetch_assoc()) {
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Handle remember me
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                            
                            // Store token in database
                            $tokenStmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                            $tokenStmt->bind_param("iss", $user['id'], $token, $expires);
                            $tokenStmt->execute();
                            
                            // Set cookie
                            setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                        }
                        
                        // Update last login timestamp
                        $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $updateStmt->bind_param("i", $user['id']);
                        $updateStmt->execute();
                        
                        // Clear failed attempts for this IP
                        $clearStmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
                        $clearStmt->bind_param("s", $ip);
                        $clearStmt->execute();
                        
                        // Redirect to dashboard
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Invalid credentials';
                        // Log failed attempt
                        $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
                        $logStmt->bind_param("ss", $ip, $username);
                        $logStmt->execute();
                    }
                } else {
                    $error = 'Invalid credentials';
                    // Log failed attempt
                    $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
                    $logStmt->bind_param("ss", $ip, $username);
                    $logStmt->execute();
                }
            }
            
            $db->closeConnection();
        }
    } catch (Exception $e) {
        $error = 'An error occurred. Please try again later.';
        $debug_info = $e->getMessage();
        error_log("Login Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AIVIP Blog</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .login-body {
            padding: 30px;
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.svg" alt="AIVIP Blog" class="brand-logo">
                <h4 class="mb-0">Admin Login</h4>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <?php if (!empty($debug_info)): ?>
                    <div class="mt-2 small text-muted">
                        <strong>Debug Info:</strong> <?php echo htmlspecialchars($debug_info); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="login.php" <?php echo $isLocked ? 'class="d-none"' : ''; ?>>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me for 30 days</label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                        <a href="forgot-password.php" class="btn btn-link text-decoration-none">
                            <i class="bi bi-key me-2"></i>Forgot Password?
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="../" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Blog
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
    </script>
</body>
</html> 