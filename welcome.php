<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$userID = $_SESSION['user_id'] ?? null;
if ($userID) {
    echo "User ID is: " . $userID;
} else {
    echo "No user ID found in session";
}

$count = 0;
//if ($userID) {
    // Create a new MySQL connection
   // $conn = new mysqli("localhost", "root", "", "user_questionnaires", 3306);
    
    // Check if connection was successful
   // if ($conn->connect_error) {
   //     die("Connection failed: " . $conn->connect_error);
   // }
    
    // Prepare SQL statement to count questionnaires for the user
   // $stmt = $conn->prepare("SELECT COUNT(*) FROM user_questionnaires WHERE user_id = ?");
    
    // Bind the user ID parameter
   // $stmt->bind_param("i", $userID);
    
    // Execute the prepared statement
   // $stmt->execute();
    
    // Bind the result to $count variable
  //  $stmt->bind_result($count);
    
    // Fetch the result
    //$stmt->fetch();
    
    // Close the prepared statement
   // $stmt->close();
 
    // Close the database connection
   // $conn->close();
//}
?>
    
<head>
    <link rel="stylesheet" href="mainpage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Bine ati venit!</h1>
    <div class="card">
        <div class="bg"></div>
        <div class="blob"></div>
        <div class="card-content">
            <div class="modern-text">11</div>
            <h3>Card Title</h3>
            <p>This is some descriptive text inside the card. You can add any content you want here.</p>
            <button>Action Button</button>
        </div>
    </div>
    
   
    
</body>
</html>