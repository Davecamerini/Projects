<?php
session_start();

require_once '../config/database.php';

// Clear remember me token if exists
if (isset($_COOKIE['remember_token'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Delete the token from database
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->bind_param("s", $_COOKIE['remember_token']);
    $stmt->execute();
    
    $db->closeConnection();
    
    // Remove the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

// Redirect to login page
header('Location: login.php');
exit; 