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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Job Seeker') {
    header("Location: ../View/signin.html");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT first_name, last_name, email, phone_number, address, profile_picture, cv, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = $user['role']; 
} else {
    die("Error: User not found.");
}

$stmt->close();

$user_id = $_SESSION['user_id'];

$shortlist_query = "
    SELECT s.id AS shortlist_id, j.job_title, j.description AS job_description, 
           e.first_name AS employer_first_name, e.last_name AS employer_last_name, 
           e.email AS employer_email, e.phone_number AS employer_phone, s.shortlisted_at
    FROM shortlisted_candidates AS s
    JOIN jobs AS j ON s.job_id = j.id
    JOIN users AS e ON j.user_id = e.id
    WHERE s.user_id = ?
    ORDER BY s.shortlisted_at DESC
";
$stmt = $conn->prepare($shortlist_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Notifications</title>
    <link rel="stylesheet" href="../css/interview_page.css">
    <style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
    color: #333;
}
        header {
    background-color: #333;
    color: #fff;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header .logo h1 {
    margin: 0;
    font-size: 24px;
}

header .menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}

header .menu ul li {
    margin: 0 10px;
}

header .menu ul li a {
    color: #fff;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 3px;
    transition: background-color 0.3s;
}

header .menu ul li a:hover {
    background-color: #555;
}

h1{
    text-align: center;
}
    </style>
</head>
<body>
<header>
        <div class="logo">
            <h1>Job Marketplace</h1>
        </div>
        <nav class="menu">
            <ul>
                <li><a href="../View/dashboard.php">Dashboard</a></li>
                <?php if ($role === 'Employer') : ?>
                    <li><a href="../View/post_job.php">Post Job</a></li>
                    <li><a href="../View/analytics.php">Analytics</a></li>
                    <li><a href="../View/manage_applications.php">Manage Applications</a></li>
                    <li><a href="../View/shortlist_candidate.php">Shortlisted Candidates</a></li>
                    <li><a href="../View/feedback.php">View Feedback</a></li>
                    <li><a href="../View/ats.php">Applicant Tracking System</a></li>
                    <li><a href="../View/chat.php">ChatBox</a></li>
                <?php elseif ($role === 'Job Seeker') : ?>
                    <li><a href="../View/view_jobs.php">Jobs</a></li>
                    <li><a href="../View/applied_jobs.php">Applications</a></li>
                    <li><a href="../View/feedback.php">Feedback</a></li>
                    <li><a href="../View/resume_builder.php">Build Resume</a></li>
                    <li><a href="../View/interview_page.php">Interview</a></li>
                    <li><a href="../View/chat.php">ChatBox</a></li>
                <?php endif; ?>
                <li><a href="../View/profile.php">Profile</a></li>
                <?php if ($_SESSION['role'] === 'Employer'): ?>
                    <li><a href="../View/notification.php">Manage Notifications</a></li>
                <?php else: ?>
                    <li><a href="../View/notification.php">Notifications</a></li>
                <?php endif; ?>
                <li>
                    <a href="../View/logout.php" 
                       onclick="return confirmLogout();" 
                       style="text-decoration: none; color: #fff; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">
                        Logout
                    </a>
                </li>
            </ul>
        </nav>

        
    </header>
    <h1>Interviews</h1>
    <main>
        <h2>Notifications</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="notification">
                    <h3>Congratulations!</h3>
                    <p>You have been shortlisted for an interview for the following job:</p>
                    <p><strong>Job Title:</strong> <?php echo htmlspecialchars($row['job_title']); ?></p>
                    <p><strong>Job Description:</strong> <?php echo htmlspecialchars($row['job_description']); ?></p>
                    <p><strong>Shortlisted At:</strong> <?php echo htmlspecialchars($row['shortlisted_at']); ?></p>

                    <h4>Employer Details:</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['employer_first_name'] . ' ' . $row['employer_last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['employer_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['employer_phone']); ?></p>
                </div>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No notifications available at the moment.</p>
        <?php endif; ?>
    </main>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
