<?php
session_start();
 
$valid_users = [
    'Shamrat' => 'sam',
    'user2' => 'user2',
    'user3' => 'user3'
];
 
if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
 
    if (!isset($_SESSION['registered_user']) || $_SESSION['registered_user'] !== $username) {
        echo "You need to register first. <a href='register.html'>Click here to register</a>";
    } else {
    
        if (array_key_exists($username, $valid_users) && $valid_users[$username] === $password) {
  
            $_SESSION['status'] = true;
            $_SESSION['username'] = $username;
            header('location: home.php');
        } else {
            echo "Invalid username or password.";
        }
    }
} else {
    header('location: login.html');
}
?>