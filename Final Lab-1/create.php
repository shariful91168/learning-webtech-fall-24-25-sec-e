<?php
session_start();
if (!isset($_COOKIE['status'])) {
    header('location: login.html');  
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
 
    if (!isset($_SESSION['users'])) {
        $_SESSION['users'] = [];
    }
    $new_user = [
        'id' => count($_SESSION['users']) + 1,
        'username' => $username,
        'password' => $password,
        'email' => $email
    ];
    $_SESSION['users'][] = $new_user;
 
    header('location: userlist.php');
}
?>