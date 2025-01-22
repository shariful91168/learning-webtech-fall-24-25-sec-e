<?php
session_start();

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employer') {
    echo json_encode(["success" => false, "message" => "Unauthorized access. You must be logged in as an employer to post jobs."]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = trim($input['job_title']);
    $company_name = trim($input['company_name']);
    $location = trim($input['location']);
    $salary = trim($input['salary']);
    $job_type = $input['job_type'];
    $experience_level = $input['experience_level'];
    $description = trim($input['description']);
    $user_id = $_SESSION['user_id'];

    if (empty($job_title) || empty($company_name) || empty($location) || empty($salary) || empty($job_type) || empty($experience_level) || empty($description)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $sql = "INSERT INTO jobs (job_title, company_name, location, salary, job_type, experience_level, description, posted_at, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $job_title, $company_name, $location, $salary, $job_type, $experience_level, $description, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Job posted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: Could not post the job. Please try again."]);
    }

    $stmt->close();
    $conn->close();
}
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
    <form id="postJobForm">
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

        <button type="submit" id="submitButton">Post Job</button>

        <div id="responseMessage" style="text-align: center; margin-top: 20px; color: red;"></div>
    </form>
</main>

<script>
    document.getElementById("postJobForm").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "../controller/post_job.php", true);
        xhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let response = JSON.parse(this.responseText);
                let message = document.getElementById("responseMessage");

                if (response.success) {
                    message.style.color = "green";
                    message.textContent = response.message || "Job posted successfully!";
                    document.getElementById("postJobForm").reset(); 
                } else {
                    message.style.color = "red";
                    message.textContent = response.message || "Failed to post the job.";
                }
            }
        };

        xhttp.send(JSON.stringify(data));
    });
</script>

</body>
</html>