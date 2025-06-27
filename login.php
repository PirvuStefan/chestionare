<?php

include('connection.php');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();

if(isset($_POST['submit'])){
    $username = $_POST['email'];
    $password = $_POST['password'];

    // Only select needed fields
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $userID = $row['id'];
        
        // Check if password is hashed (bcrypt starts with $2y$)
        if(!str_starts_with($row['password'], '$2y$')) {
            // Plain text password - check direct match and hash
            if($password === $row['password']) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $updateStmt->bind_param("si", $hashedPassword, $userID);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Login successful after hashing
                $_SESSION['userID'] = $userID;
                header("Location: welcome.php");
                exit();
            }
        } else {
            // Hashed password - use password_verify
            if(password_verify($password, $row['password'])) {
                $_SESSION['userID'] = $userID;
                header("Location: welcome.php");
                exit();
            }
        }
        
        // If we reach here, password is incorrect
        echo "<script>alert('Email sau parola incorecte!');</script>";
        echo "<script>window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Email sau parola incorecte!');</script>";
        echo "<script>window.location.href='index.php';</script>";
    }
    
    $stmt->close();
}
?>