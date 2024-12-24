<?php
session_start();
if (!isset($_COOKIE['status'])) {
    header('location: login.html');  
    exit;
}
 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $users = $_SESSION['users'];
    foreach ($users as $user) {
        if ($user['id'] == $id) {
            $current_user = $user;
            break;
        }
    }
} else {
    header('location: userlist.php');
}
?>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form method="post" action="update.php">
        <input type="hidden" name="id" value="<?= $current_user['id'] ?>" />
        Name: <input type="text" name="username" value="<?= $current_user['username'] ?>" required /><br>
        Password: <input type="password" name="password" value="<?= $current_user['password'] ?>" required /><br>
        Email: <input type="email" name="email" value="<?= $current_user['email'] ?>" required /><br>
        <input type="submit" value="Update" />
    </form>
</body>
</html>