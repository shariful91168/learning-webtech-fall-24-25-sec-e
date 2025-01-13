<?php
session_start();

if (isset($_REQUEST['submit'])) {
    $username = trim($_REQUEST['username']);
    $email = trim($_REQUEST['email']);
    $password = trim($_REQUEST['password']);

    if ($username == null || empty($email) || empty($password)) {
        echo "Null username/password/email";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $host = 'localhost';      
        $dbname = 'blog_system'; 
        $dbuser = 'root';          
        $dbpass = '';             
        $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $username = $conn->real_escape_string($username);
        $email = $conn->real_escape_string($email);
        $hashedPassword = $conn->real_escape_string($hashedPassword);

        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['user'] = ['username' => $username, 'email' => $email];
            header('location: ../View/login.html');
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
} else {
    header('location: ../View/register.html');
}
?>
