<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ..View/login.html');
    exit();
}


$user = $_SESSION['user'];
?>

<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    
    <p><strong>What would you like to do?</strong></p>
    
    <div>
        <a href="register_author.php">
            <button>Register Author</button>
        </a>
        <br><br>

        <a href="author_list.php">
            <button>Author List</button>
        </a>
        <br><br>

        <a href="logout.php">
            <button>Logout</button>
        </a>
    </div>
</body>
</html>
