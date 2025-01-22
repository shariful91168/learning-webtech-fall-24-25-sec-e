<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employer') {
    header("Location: ../signin/signin.html");
    exit;
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_jobs_posted = "SELECT COUNT(*) AS total_jobs FROM jobs WHERE user_id = ?";
$stmt_jobs_posted = $conn->prepare($sql_jobs_posted);
$stmt_jobs_posted->bind_param("i", $user_id);
$stmt_jobs_posted->execute();
$result_jobs_posted = $stmt_jobs_posted->get_result();
$jobs_posted = $result_jobs_posted->fetch_assoc()['total_jobs'];
$stmt_jobs_posted->close();

$sql_applications = "
    SELECT COUNT(*) AS total_applications 
    FROM applications 
    INNER JOIN jobs ON applications.job_id = jobs.id 
    WHERE jobs.user_id = ?";
$stmt_applications = $conn->prepare($sql_applications);
$stmt_applications->bind_param("i", $user_id);
$stmt_applications->execute();
$result_applications = $stmt_applications->get_result();
$applications_received = $result_applications->fetch_assoc()['total_applications'];
$stmt_applications->close();

$sql_shortlisted = "
    SELECT COUNT(*) AS total_shortlisted 
    FROM shortlisted_candidates 
    INNER JOIN jobs ON shortlisted_candidates.job_id = jobs.id 
    WHERE jobs.user_id = ?";
$stmt_shortlisted = $conn->prepare($sql_shortlisted);
$stmt_shortlisted->bind_param("i", $user_id);
$stmt_shortlisted->execute();
$result_shortlisted = $stmt_shortlisted->get_result();
$candidates_shortlisted = $result_shortlisted->fetch_assoc()['total_shortlisted'] ?? 0;
$stmt_shortlisted->close();


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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="stylesheet" href="analytics.css">
    <style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
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

h1 {
    text-align: center;
    color: #333;
    margin-top: 20px;
}

main {
    padding: 20px;
    background-color: #fff;
    margin: 20px;
    border-radius: 8px;
}

section h2 {
    text-align: center;
    color: #444;
    margin-bottom: 20px;
}

.analytics-cards {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
}

.card {
    background-color: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 250px;
    text-align: center;
}

.card h3 {
    font-size: 20px;
    color: #555;
    margin-bottom: 10px;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
}

footer {
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 10px;
    position: absolute;
    bottom: 0;
    width: 100%;
}

footer p {
    margin: 0;
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
    
    <h1>Analytics Dashboard</h1>
    <main>
        <section>
            <h2>Your Job Postings Performance</h2>
            <div class="analytics-cards">
                <div class="card">
                    <h3>Jobs Posted</h3>
                    <p><?php echo $jobs_posted; ?></p>
                </div>
                <div class="card">
                    <h3>Applications Received</h3>
                    <p><?php echo $applications_received; ?></p>
                </div>
                <div class="card">
                    <h3>Candidates Shortlisted</h3>
                    <p><?php echo $candidates_shortlisted; ?></p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Job Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>
