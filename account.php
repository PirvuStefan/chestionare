<?php
session_start();
include('connection.php');


$userID = $_SESSION['userID'] ?? null;

if (!$userID) {
    header("Location: welcome.php");
    exit();
}// daca nu exista userID in sesiune, redirectionam la pagina main ( acolo testam daca se poate autentifica bazat pe cookie, daca nu, redirectionam la index.php )

// Get user information
function get_user_details($userID) {
    global $conn;
    $sql = "SELECT name, email, employee_mark FROM users WHERE id = ?";  // Added employee_mark
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

$user_details = get_user_details($userID);
$employee_mark = $user_details['employee_mark'] ?? null;

function get_user_employment_date($employee_mark) {
    global $conn;
    $sql = "SELECT employed_at FROM employees WHERE mark = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employee_mark);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['employed_at'];
    }
    return null;
}

function get_char_count($string) {
    return mb_strlen($string, 'UTF-8'); // Use mb_strlen for multibyte character support
}

$date_employed = get_user_employment_date($employee_mark);

$count = get_char_count($user_details['name']);



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Account Details</title>
    <link rel="stylesheet" href="account.css" />
</head>
<body>

  <div class="card">
    <div class="buttons">

      <a href="welcome.php" class="btn" title="Home" style="display: inline-flex; align-items: center; justify-content: center;">
        <svg viewBox="0 0 24 24">
          <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
        </svg>
      </a>
      <!-- Help Button -->
      <a href="documentatie.php" class="btn" title="Documentatie" style="display: inline-flex; align-items: center; justify-content: center;">
        <svg viewBox="0 0 24 24">
          <path d="M19.14,12.94c0.04-0.31,0.06-0.63,0.06-0.94s-0.02-0.63-0.06-0.94l2.03-1.58
            c0.18-0.14,0.23-0.41,0.12-0.61l-1.92-3.32c-0.11-0.2-0.36-0.28-0.57-0.22l-2.39,0.96
            c-0.5-0.38-1.03-0.7-1.62-0.94L14.5,2.81C14.47,2.58,14.27,2.42,14.04,2.42h-4.08
            c-0.23,0-0.43,0.16-0.46,0.39L9.02,5.26c-0.59,0.24-1.13,0.56-1.62,0.94L4.99,5.24
            c-0.21-0.06-0.46,0.02-0.57,0.22L2.5,8.78c-0.11,0.2-0.06,0.47,0.12,0.61l2.03,1.58
            c-0.04,0.31-0.06,0.63-0.06,0.94s0.02,0.63,0.06,0.94L2.62,14.43c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32
            c0.11,0.2,0.36,0.28,0.57,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.44,2.44c0.03,0.23,0.23,0.39,0.46,0.39h4.08
            c0.23,0,0.43-0.16,0.46-0.39l0.44-2.44c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96c0.21,0.06,0.46-0.02,0.57-0.22l1.92-3.32
            c0.11-0.2,0.06-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.99,0-3.6-1.61-3.6-3.6s1.61-3.6,3.6-3.6s3.6,1.61,3.6,3.6
            S13.99,15.6,12,15.6z"/>
        </svg>
      </a>

      <!-- Logout Button -->
       <a href="logout.php" class="btn" title="Logout" style="display: inline-flex; align-items: center; justify-content: center;">
        <svg viewBox="0 0 24 24">
          <path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm3-10H5c-1.1 0-2 .9-2 
            2v4h2V5h14v14H5v-4H3v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 
            2-2V5c0-1.1-.9-2-2-2z"/>
        </svg>
      </a>

      
    </div>
    <?php if($count >= 16) echo "<br>" ?>
    
    <h1><?php echo $user_details['name']?></h1>
    <p><?php echo $user_details['email']?></p>
    <p>Angajat Robest din <?php echo $date_employed ?></p>
  </div>

</body>
</html>
