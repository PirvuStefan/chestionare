<?php
$servername = "localhost";
$username = "robestnew_chestionareutil";
$password = "mgNK?y!cYvqe";
$dbname = "robestnew_chestionare";
$conn = new mysqli($servername, $username, $password, $dbname, 3306);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
?>