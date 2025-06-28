<?php
    include('connection.php');

    

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


</div>



</body>
</html>