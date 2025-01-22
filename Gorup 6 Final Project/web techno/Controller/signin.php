<?php
session_start();

if (isset($_REQUEST['submit'])) {
    $email = trim($_REQUEST['email']);
    $password = trim($_REQUEST['password']);

    if ($email == null || $password == null) {
        echo "Null email/password";
    } else {
        $servername = "localhost";
        $username = "root";
        $db_password = "";
        $dbname = "job_marketplace";

        $conn = new mysqli($servername, $username, $db_password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];

                header('location: ../View/dashboard.php');
            } else {
                echo "Invalid password";
            }
        } else {
            echo "Invalid email";
        }

        $stmt->close();
        $conn->close();
    }
} else {
    header('location: signin.html');
}
?>
