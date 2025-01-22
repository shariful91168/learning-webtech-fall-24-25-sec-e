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
    die("Unauthorized access. You must be logged in as an employer to post jobs.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $job_title = trim($_POST['job_title']);
    $company_name = trim($_POST['company_name']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);
    $job_type = $_POST['job_type'];
    $experience_level = $_POST['experience_level'];
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id']; 
    if (empty($job_title) || empty($company_name) || empty($location) || empty($salary) || empty($job_type) || empty($experience_level) || empty($description)) {
        die("All fields are required. Please fill in the form completely.");
    }

    $sql = "INSERT INTO jobs (job_title, company_name, location, salary, job_type, experience_level, description, posted_at, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $job_title, $company_name, $location, $salary, $job_type, $experience_level, $description, $user_id);

    if ($stmt->execute()) {
        header("Location: ../View/view_jobs.php");
        exit;
    } else {
        echo "Error: Could not post the job. Please try again.";
    }

    $stmt->close();
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link rel="stylesheet" href="../View/post_jobs.css">
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
    <h1>Post a Job</h1>
    

        
    <main>
        <form action="post_job.php" method="POST">
            <label for="job_title">Job Title</label>
            <input type="text" id="job_title" name="job_title" required>

            <label for="company_name">Company Name</label>
            <input type="text" id="company_name" name="company_name" required>

            <label for="location">Location</label>
            <input type="text" id="location" name="location" required>

            <label for="salary">Salary</label>
            <input type="number" id="salary" name="salary" required>

            <label for="job_type">Job Type</label>
            <select id="job_type" name="job_type" required>
                <option value="Full-Time">Full-Time</option>
                <option value="Part-Time">Part-Time</option>
                <option value="Internship">Internship</option>
                <option value="Contract">Contract</option>
            </select>

            <label for="experience_level">Experience Level</label>
            <select id="experience_level" name="experience_level" required>
                <option value="Entry Level">Entry Level</option>
                <option value="Mid Level">Mid Level</option>
                <option value="Senior Level">Senior Level</option>
            </select>

            <label for="description">Job Description</label>
            <textarea id="description" name="description" rows="5" required></textarea>

            <button type="submit" name="submit">Post Job</button>

            <div style="text-align: center; margin-top: 20px;">
                <a href="../View/view_jobs.php" class="btn btn-secondary" style="text-decoration: none;">View Posted Jobs</a>
            </div>
        </form>
    </main>
</body>
</html>