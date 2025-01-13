<?php
session_start();

$host = 'localhost';     
$dbname = 'blog_system'; 
$dbuser = 'root';         
$dbpass = '';            

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "Email and Password are required.";
    } else {
        $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $email = $conn->real_escape_string($email);

        $sql = "SELECT id, username, email, password FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];

                header('Location: ../Controller/dashboard.php'); 
                exit();
            } else {
                echo "Invalid email or password.";
            }
        } else {
            echo "No user found with this email.";
        }

        $conn->close();
    }
} else {
    header('Location: ../View/login.html');
    exit();
}
?>
