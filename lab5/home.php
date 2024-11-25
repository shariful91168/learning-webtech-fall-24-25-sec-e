<?php
session_start();
if (!isset($_SESSION['status'])) {
    header('location: login.html');
    exit();
}
?>
 
<html lang="en">
<head>
    <title>Home</title>
</head>
<body>
    <h1>Welcome Home! <?= $_SESSION['username'] ?></h1>
    <a href="logout.php">Logout</a>
</body>
</html>