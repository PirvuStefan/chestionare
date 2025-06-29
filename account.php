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

$date_employed = get_user_employment_date($employee_mark);

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
      <!-- Help Button -->
      <button class="btn" title="Help">
        <svg viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
            10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 
            13.9 12 14.5 12 16h-2v-.5c0-.8.45-1.5 1.17-2.08l1.24-1.26c.37-.36.59-.86.59-1.41 
            0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 
            1.68-.93 2.25z"/>
        </svg>
      </button>

      <!-- Logout Button -->
       <a href="logout.php" class="btn" title="Logout" style="display: inline-flex; align-items: center; justify-content: center;">
        <svg viewBox="0 0 24 24">
          <path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm3-10H5c-1.1 0-2 .9-2 
            2v4h2V5h14v14H5v-4H3v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 
            2-2V5c0-1.1-.9-2-2-2z"/>
        </svg>
      </a>
    </div>

    <h1><?php echo $user_details['name']?></h1>
    <p><?php echo $user_details['email']?></p>
    <p>Angajat Robest din <?php echo $date_employed ?></p>
  </div>

</body>
</html>
