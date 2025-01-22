<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']); 
    $interests = isset($_POST['interests']) ? $_POST['interests'] : []; 

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        die("Error: All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    if (strlen($password) < 6) {
        die("Error: Password must be at least 6 characters long.");
    }

    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    $sql_check = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Error: Email is already registered.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $interests_string = implode(',', $interests);

    $sql_insert = "INSERT INTO users (first_name, last_name, email, password, role, interests) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $hashed_password, $role, $interests_string);

    if ($stmt->execute()) {
        $_SESSION['registered_user'] = $email;
        echo "Registration successful! You can now log in.";
        echo '<br><a href="../View/signin.html">Login here</a>';
    } else {
        echo "Error: Could not complete the registration.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
