<?php
include('connection.php');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();

function generate_unique_token() {
    global $conn;
    
    do {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        
        // Check if token already exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM remember_tokens_web WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $exists = $row['count'] > 0;
        $stmt->close();
        
    } while ($exists); // Keep generating until we get a unique token
    
    return $token;
}

function register_token($userID, $token) {
    global $conn;
    
    // Optional: Remove old tokens for this user (single device login)
    // $stmt = $conn->prepare("DELETE FROM remember_tokens_web WHERE user_id = ?");
    // $stmt->bind_param("i", $userID);
    // $stmt->execute();
    // $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO remember_tokens_web (user_id, token) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $token);
    $stmt->execute();
    $stmt->close();
}

if(isset($_POST['submit'])){
    $username = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email=? AND inactive=0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $userID = $row['id'];
        
        $login_successful = false;
        
      /*  if(!str_starts_with($row['password'], '$2y$')) {
            // Plain text password
            if($password === $row['password']) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $updateStmt->bind_param("si", $hashedPassword, $userID);
                $updateStmt->execute();
                $updateStmt->close();
                $login_successful = true;
            }
        }*/ 
            // Hashed password
            if(password_verify($password, $row['password'])) {
                $login_successful = true;
            }
        
        
        if($login_successful) {
            $_SESSION['userID'] = $userID;
            $token = generate_unique_token(); // Generate UNIQUE token
            register_token($userID, $token);
            setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true);
            header("Location: welcome.php");
            exit();
        }
    }
    
    // Login failed
    echo "<script>alert('Email sau parola incorecte!');</script>";
    echo "<script>window.location.href='index.php';</script>";
    
    $stmt->close();
}
?>