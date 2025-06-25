<?php
session_start();
include('connection.php');

// Clear remember token from database if user is logged in
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE users SET remember_token=NULL WHERE id='$user_id'";
    mysqli_query($conn, $sql);
}

// Clear remember me cookie
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, "/");
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>
