<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "table";
$conn = new mysqli($servername, $username, $password, $dbname, 3306);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
?>
