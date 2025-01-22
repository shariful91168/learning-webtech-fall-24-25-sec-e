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
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employer') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$employer_id = intval($_SESSION['user_id']);

$sql = "SELECT aj.id AS application_id, aj.applied_at, u.first_name, u.last_name, u.id AS user_id, 
        j.title AS job_title, j.id AS job_id
        FROM applied_jobs aj
        INNER JOIN jobs j ON aj.job_id = j.id
        INNER JOIN users u ON aj.user_id = u.id
        WHERE j.employer_id = ?
        ORDER BY aj.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

echo json_encode($applications);

$stmt->close();
$conn->close();
?>
