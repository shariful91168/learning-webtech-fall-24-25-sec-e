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
    header("Location: ../View/singin.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    $resume_query = "SELECT cv FROM users WHERE id = ?";
    $stmt = $conn->prepare($resume_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($resume_path);
    $stmt->fetch();
    $stmt->close();

    if (!empty($resume_path)) {
        header("Location: ../Controller/uploads/" . urlencode($resume_path));
        exit;
    } else {
        $message = "This user has not uploaded a resume.";
    }
} else {
    $message = "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume</title>
</head>
<body>
    <h1>View Resume</h1>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="manage_applications.php">Back to Applications</a>
</body>
</html>

<?php
$conn->close();
?>
