<!DOCTYPE html>
<html lang="en">
<?php
session_start();
include('connection.php');



 $userID = $_SESSION['userID'] ?? null;
 echo $userID;



 $sql = "SELECT COUNT(*) as count FROM user_questionnaires WHERE user_id='$userID'";
 $result = mysqli_query($conn, $sql);
 $row = mysqli_fetch_assoc($result);
 $chestionare_completate = $row['count'] ?? 0; // in cazul in care nu exista chestionare, setam la 0


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
            <div class="modern-text"><?php echo $chestionare_completate ?? 0; ?></div>
            <h3>Card Title</h3>
            <p>This is some descriptive text inside the card. You can add any content you want here.</p>
            <button>Action Button</button>
        </div>
    </div>
    
   
    
</body>
</html>