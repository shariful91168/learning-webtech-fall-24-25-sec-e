<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../View/signin.html");
    exit;
}

$user_role = $_SESSION['role'];
$first_name = $_SESSION['first_name'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "job_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Job Marketplace</h1>
        </div>
        <nav class="menu">
            <ul>
                <li><a href="../View/dashboard.php">Dashboard</a></li>
                <?php if ($user_role === 'Employer') : ?>
                    <li><a href="../View/post_job.php">Post Job</a></li>
                    <li><a href="../View/analytics.php">Analytics</a></li>
                    <li><a href="../View/manage_applications.php">Manage Applications</a></li>
                    <li><a href="../View/shortlist_candidate.php">Shortlisted Candidates</a></li>
                    <li><a href="../View/feedback.php">View Feedback</a></li>
                    <li><a href="../View/ats.php">Applicant Tracking System</a></li>
                    <li><a href="../View/chat.php">ChatBox</a></li>
                <?php elseif ($user_role === 'Job Seeker') : ?>
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

    <main>
        <div class="welcome-message">
            <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p>Your dashboard is tailored for your role as a <strong><?php echo htmlspecialchars($user_role); ?></strong>.</p>
        </div>

        <?php if ($user_role === 'Employer') : ?>
            <section class="dashboard-content">
    <h3>Job Posting Analytics</h3>
    <p>Track views, applications, and performance of your job postings.</p>
    <div class="cards">
        <?php
        $employer_id = intval($_SESSION['user_id']);
        
        $jobs_query = "SELECT COUNT(*) AS total_jobs FROM jobs WHERE user_id = ?";
        $jobs_stmt = $conn->prepare($jobs_query);
        $jobs_stmt->bind_param("i", $employer_id);
        $jobs_stmt->execute();
        $jobs_result = $jobs_stmt->get_result();
        $jobs_data = $jobs_result->fetch_assoc();
        $total_jobs = $jobs_data['total_jobs'] ?? 0;
        $jobs_stmt->close();

        $applications_query = "
            SELECT COUNT(*) AS total_applications 
            FROM applications 
            WHERE job_id IN (SELECT id FROM jobs WHERE user_id = ?)";
        $applications_stmt = $conn->prepare($applications_query);
        $applications_stmt->bind_param("i", $employer_id);
        $applications_stmt->execute();
        $applications_result = $applications_stmt->get_result();
        $applications_data = $applications_result->fetch_assoc();
        $total_applications = $applications_data['total_applications'] ?? 0;
        $applications_stmt->close();

        $shortlisted_query = "
            SELECT COUNT(*) AS total_shortlisted
            FROM shortlisted_candidates
            WHERE job_id IN (SELECT id FROM jobs WHERE user_id = ?)";
        $shortlisted_stmt = $conn->prepare($shortlisted_query);
        $shortlisted_stmt->bind_param("i", $employer_id);
        $shortlisted_stmt->execute();
        $shortlisted_result = $shortlisted_stmt->get_result();
        $shortlisted_data = $shortlisted_result->fetch_assoc();
        $total_shortlisted = $shortlisted_data['total_shortlisted'] ?? 0;
        $shortlisted_stmt->close();
        ?>
        
        
        <div class="card">Jobs Posted: <?php echo $total_jobs; ?></div>
        <div class="card">Applications Received: <?php echo $total_applications; ?></div>
        <div class="card">Shortlisted Candidates: <?php echo $total_shortlisted; ?></div>
    </div>

    <h3>Manage Your Jobs</h3>
    <p>
        <a href="../View/post_job.php">Post a New Job</a> 

        <a href="../View/application.php">Application</a>
        or <a href="../View/employer_jobs.php">view/edit existing job postings</a>.
    </p>
</section>

        <?php elseif ($user_role === 'Job Seeker') : ?>
            <section class="dashboard-content">
                <h3>Job Recommendations</h3>
                <p>Based on your selected interests.</p>
                <div class="cards">
                    <?php
                    $user_id = intval($_SESSION['user_id']);
                    $sql = "SELECT interests FROM users WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if ($user && !empty($user['interests'])) {
                        $interests = explode(',', $user['interests']);
                        $placeholders = rtrim(str_repeat('?,', count($interests)), ',');
                        $job_query = "SELECT * FROM jobs WHERE job_title IN ($placeholders) LIMIT 10";
                        $job_stmt = $conn->prepare($job_query);
                        $job_stmt->bind_param(str_repeat('s', count($interests)), ...$interests);
                        $job_stmt->execute();
                        $jobs = $job_stmt->get_result();

                        while ($job = $jobs->fetch_assoc()) {
                            echo "<div class='card'>";
                            echo "<p><strong>Job Title:</strong> " . htmlspecialchars($job['job_title']) . "</p>";
                            echo "<p><strong>Company:</strong> " . htmlspecialchars($job['company_name']) . "</p>";
                            echo "<form action='../View/view_jobs.php' method='GET' style='display:inline;'>
                                    <button type='submit' name='job_id' value='" . $job['id'] . "'>Details</button>
                                  </form>";
                            echo "<form action='../View/application.php' method='GET' style='display:inline;'>
                                    <button type='submit' name='job_id' value='" . $job['id'] . "'>Apply</button>
                                  </form>";
                            echo "</div>";
                        }

                        $job_stmt->close();
                    } else {
                        echo "<p>No recommendations available. Update your profile with interests to see recommended jobs.</p>";
                    }

                    $stmt->close();
                    ?>
                </div>
                <h3>Manage Your Applications</h3>
                <p><a href="../View/application.php">View your applications</a> or explore new opportunities.</p>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Job Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>
