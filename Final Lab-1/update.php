<?php
session_start();
if (!isset($_COOKIE['status'])) {
    header('location: login.html');  
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
 
    foreach ($_SESSION['users'] as &$user) {
        if ($user['id'] == $id) {
            $user['username'] = $username;
            $user['password'] = $password;
            $user['email'] = $email;
            break;
        }
    }
 
    header('location: userlist.php');
}
?>