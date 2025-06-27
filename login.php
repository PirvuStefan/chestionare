<?php

include('connection.php');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();

if(isset($_POST['submit'])){
    $username = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $username); // Changed from "ss" to "s" - only 1 parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;
    
    if($count == 1) { // Check if user exists FIRST
        $row = $result->fetch_assoc();
        $userID = $row['id'];
        // Check if password needs hashing
        if (!password_verify($password, $row['password'])) {
            // If direct match and not hashed, update with hash
            if ($password === $row['password']) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $updateStmt->bind_param("si", $hashedPassword, $userID);
                $updateStmt->execute();
                $updateStmt->close();
                $row['password'] = $hashedPassword;
            }
        }
        
        if(password_verify($password, $row['password'])) {
            $_SESSION['userID'] = $userID;
            header("Location: welcome.php");
            exit(); // Added exit() after redirect
        } else {
            echo "<script>alert('Email sau parola incorecte!');</script>";
            echo "<script>window.location.href='index.php';</script>";
        }
    } 
    
    $stmt->close();
}
?>