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

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = intval($_POST['receiver_id']);

    // Insert message into database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $current_user_id, $receiver_id, $message);
    if ($stmt->execute()) {
        header("Location: chat.php?user_id=$receiver_id");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
