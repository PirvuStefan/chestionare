<?php

include('connection.php');

$manual = mysqli_query($conn, "SELECT content FROM documents WHERE id = 4");
$manual = mysqli_fetch_assoc($manual)['content'];

$compatibilitati = mysqli_query($conn, "SELECT content FROM documents WHERE id = 8");
$compatibilitati = mysqli_fetch_assoc($compatibilitati)['content'];

if($compatibilitati == null) {  // Fixed: "compatibilitati"
    $compatibilitati = "Nu exista fisierul compatibilitati in baza de date. ";
}
if($manual == null) {
    $manual = "Nu exista fisierul manual in baza de date.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body {
            color: #ffffff;
            background-color: #1a1a1a;
        }
        a {
            color: #66b3ff;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #ffffff;
        }
        p, div, span {
            color: #e6e6e6;
        }
        table {
            background-color: #262626;
            border-color: #404040;
        }
        th, td {
            color: #e6e6e6;
            border-color: #404040;
        } 


        </style> <!-- fortam la 'dark-mode' aceasta pagina ca sa fie textul lizibil indiferent -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentatie</title>
    <h1> Compatibilitati:<?php echo $compatibilitati; ?> </h1>
    <?php echo "<br><br>    "; ?>
    
    <?php echo $manual; ?>
</head>
<body>
    
</body>
</html>