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
 

    if (array_key_exists($username, $valid_users)) {
        
        $_SESSION['registered_user'] = $username;
        $_SESSION['password'] = $password; 
        echo "Registration successful! You can now log in.";
        echo '<br><a href="login.html">Login here</a>';
    } else {
        echo "Username is not valid for registration.";
    }
} else {
    echo "Invalid request.";
}
?>