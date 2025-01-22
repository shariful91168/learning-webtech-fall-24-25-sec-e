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

if (!isset($_SESSION['user_id'])) {
    die("Please log in to apply for jobs.");
}

if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    die("Invalid request.");
}

$job_id = intval($_GET['job_id']);
$user_id = intval($_SESSION['user_id']);

$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Job not found.");
}

$job = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $insert_sql = "INSERT INTO applications (user_id, job_id, applied_at) VALUES (?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $job_id);

    if ($insert_stmt->execute()) {
        echo "<p>Application submitted successfully!</p>";
        echo '<a href="applied_jobs.php">See Applied Jobs</a>';
    } else {
        echo "<p>Error applying for the job: " . $insert_stmt->error . "</p>";
    }

    $insert_stmt->close();
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
</head>
<body>
    <h1>Job Details</h1>
    <p><strong>Title:</strong> <?php echo htmlspecialchars($job['job_title']); ?></p>
    <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company_name']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
    <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>

    <form method="POST">
        <button type="submit" name="apply">Apply</button>
    </form>
</body>
</html>
