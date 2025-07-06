<?php
    include('connection.php');
    session_start();

    // Check if user has valid cookie, not just session
    if(isset($_SESSION['userID']) && isset($_COOKIE['remember_token'])) {
        // Verify the cookie is still valid
        $cookie_token = $_COOKIE['remember_token'];
        $sql = "SELECT user_id, created_at FROM remember_tokens_web WHERE token = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $cookie_token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $created_at = strtotime($row['created_at']);
            $expiry = strtotime('+30 days', $created_at);
            if (time() <= $expiry) {
                // Cookie is valid, redirect to welcome
                header("Location: welcome.php");
                die();
            }
        }
        // If we reach here, cookie is invalid - clear session
        unset($_SESSION['userID']);
    }
?>




<?php echo "."  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="logo_robest.png">
    <link rel="shortcut icon" type="image/png" href="logo_robest.png">
    <link rel="apple-touch-icon" href="logo_robest.png">
    <link rel="stylesheet" href="css.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<style>
.error-message {
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid #dc3545;
    color: #dc3545;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container">
    <div class="heading">Bine ati venit!</div>
    <div class="agreement1">Conectati-va pentru a continua.</div>
    <form class="form" action="login.php" method="post">
        <input
                placeholder="E-mail"
                id="email"
                name="email"
                type="email"
                class="input"
                required=""
        />
        <input
                placeholder="Parola"
                id="password"
                name="password"
                type="password"
                class="input"
                required=""
        />
        <input value="Conectare" name="submit" type="submit" class="login-button" />
    </form>

    <?php

    if (isset($_SESSION['login_error'])) {
    echo '<div class="error-message">' . $_SESSION['login_error'] . '</div>';
    unset($_SESSION['login_error']); // golim dupa afisare
    }

    

    ?>


</div>



</body>
</html>