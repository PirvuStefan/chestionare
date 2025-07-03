<?php 
include('connection.php'); 

session_start(); // Start the session

$_SESSION['quiz_start_time'] = null; // Reset the quiz start time
$_SESSION['results_written'] = null; // Reset the results written flag
$_SESSION['csrf_token'] = null; // Reset the CSRF token


header("Location: welcome.php"); // Redirect to the index page
exit(); // Ensure no further code is executed after the redirect


?>