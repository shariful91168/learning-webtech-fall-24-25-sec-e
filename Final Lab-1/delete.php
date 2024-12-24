<?php
session_start();
if (!isset($_COOKIE['status'])) {
    header('location: login.html');  
    exit;
}
 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
 
    foreach ($_SESSION['users'] as $key => $user) {
        if ($user['id'] == $id) {
            unset($_SESSION['users'][$key]);
            break;
        }
    }
 
    header('location: userlist.php');
}
?>