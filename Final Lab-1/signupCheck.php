<?php
session_start();
if (isset($_REQUEST['submit'])) {
    $username = trim($_REQUEST['username']);
    $password = trim($_REQUEST['password']);
    $email = trim($_REQUEST['email']);
 
    if ($username == null || empty($password) || empty($email)) {
        echo "Null username/password/email";
    } else {
        // Add user to session array
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
        header('location: login.html');
    }
} else {
    header('location: signup.html');
}
?>