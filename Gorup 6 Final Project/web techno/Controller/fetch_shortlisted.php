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

$sql = "SELECT sc.id AS shortlist_id, sc.shortlisted_at, u.first_name, u.last_name, 
        j.title AS job_title, j.id AS job_id
        FROM shortlisted_candidates sc
        INNER JOIN jobs j ON sc.job_id = j.id
        INNER JOIN users u ON sc.user_id = u.id
        WHERE j.employer_id = ?
        ORDER BY sc.shortlisted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$shortlisted = [];
while ($row = $result->fetch_assoc()) {
    $shortlisted[] = $row;
}

echo json_encode($shortlisted);

$stmt->close();
$conn->close();
?>
