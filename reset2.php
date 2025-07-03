<?php


session_start(); // Start the session
include('connection.php'); // Include the database connection file
$start_time = date('Y-m-d H:i:s');;
$_SESSION['quiz_start_time'] = $start_time; 
$_SESSION['results_written'] = null; // Reset the results written flag
$_SESSION['csrf_token'] = null; // Reset the CSRF token

header("Location: question.php"); // Redirect to the welcome page
exit(); // Ensure no further code is executed after the redirect
?>