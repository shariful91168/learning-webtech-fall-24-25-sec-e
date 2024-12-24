<?php
    session_start();
    if(isset($_REQUEST['submit'])){
        $username = trim($_REQUEST['username']);
        $password = trim($_REQUEST['password']);
 
        if($username == null || empty($password)){
            echo "Null username/password";
        }elseif($_SESSION['user']['username'] == $username && $_SESSION['user']['password']==$password){
            setcookie('status', 'true', time()+3600, '/');
            $_SESSION['username'] = $username;
 
            header('location: home.php');
        }
   
    }else{
        //echo "invalid request!";
        header('location: login.html');
    }
 
?>