<?php
session_start();
include('connection.php');

$start_time = date('Y-m-d H:i:s');
$_SESSION['quiz_start_time'] = $start_time; 

function initialise_user($coockie_now){
    global $conn;
    $sql = "SELECT user_id FROM remember_tokens_web WHERE token = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $coockie_now);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if token is expired (30 days from created_at)
    $sql_check = "SELECT created_at FROM remember_tokens_web WHERE token = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $coockie_now);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if ($row_check = mysqli_fetch_assoc($result_check)) {
        $created_at = strtotime($row_check['created_at']);
        $expiry = strtotime('+30 days', $created_at);
        if (time() > $expiry) {
            // Token expired - delete from database and unset cookie
            $sql_delete = "DELETE FROM remember_tokens_web WHERE token = ?";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "s", $coockie_now);
            mysqli_stmt_execute($stmt_delete);
            setcookie('remember_token', '', time() - 3600, '/');
            return null;
        }
    }

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['user_id'];
    }
    return null;
}

function get_name($userID) {
    global $conn;
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Split the name and return only the first part
        $nameParts = explode(' ', $row['name']);
        return $nameParts[0];
    }
    return null;
}

// AUTHENTICATION LOGIC - MUST BE BEFORE ANY HTML OUTPUT
$userID = $_SESSION['userID'] ?? null;
$coockie_now = $_COOKIE['remember_token'] ?? null;
 
if(!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])) {
    header("Location: index.php");
    exit();
}

$userID = initialise_user($coockie_now);

if($userID === null) {
    // Cookie exists but is invalid/expired, redirect to login
    setcookie('remember_token', '', time() - 3600, '/'); // Clear the invalid cookie
    header("Location: index.php");
    exit();
}

$_SESSION['userID'] = $userID; // salvam userID in sesiune pentru a fi folosit in alte pagini  

// chestionare completate 
$sql = "SELECT COUNT(*) as count FROM user_questionnaires WHERE user_id = ? AND (answered_correct + answered_incorrect) > 9";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$chestionare_completate = $row['count'] ?? 0;

// chestionare perfecte
$sql1 = "SELECT COUNT(*) as count FROM user_questionnaires WHERE user_id = ? AND answered_correct = 10";
$stmt1 = mysqli_prepare($conn, $sql1);
mysqli_stmt_bind_param($stmt1, "i", $userID);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
$row1 = mysqli_fetch_assoc($result1);
$chestionare_perfecte = $row1['count'] ?? 0;

$name = get_name($userID);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="logo_robest.png">
    <link rel="shortcut icon" type="image/png" href="logo_robest.png">
    <link rel="apple-touch-icon" href="logo_robest.png">
    <link rel="stylesheet" href="mainpage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <!-- Rest of your HTML remains the same -->
    <div class="card">
        <div class="bg"></div>
        <div class="blob"></div>
        <div class="card-content">
            <div class="modern-text23"><?php echo $chestionare_completate ?? 0; ?></div>
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
                    color: #333 !important;
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
    <div class="floating-text">Salut, <?php echo $name ?>! Bine te am regasit! 👋️</div>

    <div class="card2">
        <div class="bg2"></div>
        <div class="blob2"></div>
        <div class="card-content">
            <div class="modern-text"><?php echo $chestionare_perfecte ?? 0; ?></div>
            <div class="modern-text1">Chestionare Perfecte</div>
            <div class="subtitle">In total ati completat <?php echo $chestionare_perfecte ?? 0; ?> chestionare perfect.</div>
        </div>
    </div>

    <div class="card3">
        <div class="modern-text1">Încă un chestionar și devii expert!</div>
        <div class="subtitle">Ce zici, mai încerci unul? 🧠</div>  
        <a href="question.php"><button class="button">Incepe</button></a>
    </div>

    <a href="account.php" class="profile-button" title="Account Details">
        <svg viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
        </svg>
    </a>
</body>
</html>