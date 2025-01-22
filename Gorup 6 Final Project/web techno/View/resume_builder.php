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
$responseMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['skills'], $_POST['experience'], $_POST['education'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $skills = $conn->real_escape_string($_POST['skills']);
    $experience = $conn->real_escape_string($_POST['experience']);
    $education = $conn->real_escape_string($_POST['education']);

    $stmt = $conn->prepare("INSERT INTO resumes (name, skills, experience, education) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $skills, $experience, $education);

    if ($stmt->execute()) {
        $responseMessage = "Resume built successfully!";
    } else {
        $responseMessage = "Error saving resume.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder</title>
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
        h1 {
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        #resumeResponse {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>

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

<body>
    <h1>Build Your Resume</h1>
    <form method="POST" id="resumeBuilderForm">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required placeholder="Your Full Name">

        <label for="skills">Skills:</label>
        <textarea id="skills" name="skills" required placeholder="List your skills, separated by commas"></textarea>

        <label for="experience">Experience:</label>
        <textarea id="experience" name="experience" required placeholder="Describe your work experience"></textarea>

        <label for="education">Education:</label>
        <textarea id="education" name="education" required placeholder="Describe your educational background"></textarea>

        <button type="submit">Save Resume</button>
    </form>

    <div id="resumeResponse"><?php echo htmlspecialchars($responseMessage); ?></div>
    <?php if (!empty($responseMessage) && $responseMessage === "Resume built successfully!"): ?>
        <button id="downloadPDF">Download as PDF</button>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script>
        document.getElementById('downloadPDF')?.addEventListener('click', function () {
            const name = document.getElementById('name').value.trim();
            const skills = document.getElementById('skills').value.trim();
            const experience = document.getElementById('experience').value.trim();
            const education = document.getElementById('education').value.trim();

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text(`Resume: ${name}`, 10, 10);
            doc.text("Skills:", 10, 20);
            doc.text(skills, 10, 30);
            doc.text("Experience:", 10, 50);
            doc.text(experience, 10, 60);
            doc.text("Education:", 10, 80);
            doc.text(education, 10, 90);

            doc.save(`${name}_Resume.pdf`);
        });
    </script>
</body>
</html>
