<?php
session_start();

if (isset($_POST['confirm_logout'])) {
    if ($_POST['confirm_logout'] === 'yes') {
        session_unset();
        session_destroy();
        header("Location: ../View/landingpage.html");
        exit;
    } else {
        header("Location: ../View/dashboard.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .button {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
        }
        .yes {
            background-color: #ff4d4d;
            color: white;
            border: none;
            cursor: pointer;
        }
        .no {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Are you sure you want to log out?</h1>
    <form method="post">
        <button type="submit" name="confirm_logout" value="yes" class="button yes">Yes, Logout</button>
        <button type="submit" name="confirm_logout" value="no" class="button no">Cancel</button>
    </form>
</body>
</html>
