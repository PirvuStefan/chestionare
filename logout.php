<?php
session_start();
include('connection.php');

// Handle both POST (secure) and GET (direct access) requests
if (isset($_POST['logout'])) {
    // Secure POST logout with CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: index.php");
        exit();
    }
} 

// Proceed with logout for both POST and GET requests
// Remove the remember token from the database
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("DELETE FROM remember_tokens_web WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->close();
}

// Clear the session and cookies
session_unset();
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');

header("Location: index.php");
exit();
?>