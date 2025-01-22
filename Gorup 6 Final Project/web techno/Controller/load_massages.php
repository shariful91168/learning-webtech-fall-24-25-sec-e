<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$other_user_id = intval($_GET['user_id']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT sender_id, receiver_id, message
    FROM messages
    WHERE (sender_id = ? AND receiver_id = ?)
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC
");
$stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>
