<?php
    include('connection.php');
    session_start();

    echo ".";
    if(isset($_SESSION['userID'])) {
        header("Location: welcome.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
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