<?php
session_start();
if (!isset($_COOKIE['status'])) {
    header('location: login.html');  
    exit;
}
 
// Fetch users from session
$users = isset($_SESSION['users']) ? $_SESSION['users'] : [];
?>
<html lang="en">
<head>
    <title>Userlist</title>
</head>
<body>
    <h2>User List</h2>    
    <a href="home.php">Back</a> |
    <a href="logout.php">Logout</a>
    <br><br>
    <a href="create.html">Add New User</a>
    <br><br>
 
    <table border=1>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user) { ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['username'] ?></td>
            <td><?= $user['email'] ?></td>
            <td>
                <a href="edit.php?id=<?= $user['id'] ?>">EDIT</a> |
                <a href="delete.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">DELETE</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
 