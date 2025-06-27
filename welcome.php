<!DOCTYPE html>
<html lang="en">
<?php
session_start();
include('connection.php');





 $userID = $_SESSION['userID'] ?? null;
 echo $userID;



 $sql = "SELECT COUNT(*) as count FROM user_questionnaires WHERE user_id='$userID' AND (answered_correct + answered_incorrect) > 9";
 $result = mysqli_query($conn, $sql);
 $row = mysqli_fetch_assoc($result);
 $chestionare_completate = $row['count'] ?? 0; // in cazul in care nu exista chestionare, setam la 0


 $sql1 = "SELECT COUNT(*) as count FROM user_questionnaires WHERE user_id='$userID' AND answered_correct = 10";
$result1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_assoc($result1);
$chestionare_perfecte = $row1['count'] ?? 0; // in cazul in care nu exista chestionare perfecte, setam la 0


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
    
    <div class="card">
        <div class="bg"></div>
        <div class="blob"></div>
        <div class="card-content">
            <div class="modern-text"><?php echo $chestionare_completate ?? 0; ?></div>
            <style>
                .modern-text1 {
                    font-family: 'Roboto', sans-serif;
                    font-size: 1.2em;
                    color: #333;
                    text-align: center;
                    margin-top: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .subtitle {
                    font-family: 'Roboto', sans-serif;
                    font-size: 1.2em;
                    color: #333 !important; /* Force dark color with !important */
                    margin-top: 15px;
                    letter-spacing: 0.5px;
                    line-height: 1.4;
                    font-weight: 300;
                }
            </style>
            <div class="modern-text1">Chestionare Completate</div>
            <div class="subtitle">In total ati completat <?php echo $chestionare_completate ?? 0; ?>  chestionare.</div>
            
            
        </div>
    </div>

    <div class="card2">
    <div class="bg2"></div>
    <div class="blob2"></div>
    <div class=card-content>
    
    <div class="modern-text"><?php echo $chestionare_perfecte ?? 0; ?></div>
    <div class ="modern-text1">Chestionare Perfecte</div>
    <div class="subtitle">In total ati completat <?php echo $chestionare_perfecte ?? 0; ?> chestionare perfect.</div>
            

    </div>


    </div>

    <div class="card3"></div>

    
   
    
</body>
</html>