<?php

include('connection.php');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if(isset($_POST['submit'])){
    $username = $_POST['email'] ;
    $password = $_POST['password'] ;

    
    
    $sql = "SELECT * FROM users WHERE email='$username' AND password='$password' AND inactive=0"; // verficicam daca utilizatorul este inactiv
   $rezultat = mysqli_query($conn, $sql);
   $row = mysqli_fetch_array($rezultat, MYSQLI_ASSOC);
   $count = mysqli_num_rows($rezultat);
   
    $userID = $row['id'];
    $_SESSION['userID'] = $userID;
   
    

    if($count == 1) {
        ///echo $userID;
        session_start();
        $_SESSION['userID'] = $userID;
        header("Location: welcome.php");  
    }
    else {
        echo "<script>alert('Email sau parola incorecte!');</script>";
        echo "<script>window.location.href='index.php';</script>";

    }
}
?>