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
    die("Unauthorized access.");
}

$employer_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id']) && isset($_POST['user_id'])) {
    $job_id = intval($_POST['job_id']);
    $user_id = intval($_POST['user_id']);

    $job_query = "SELECT id FROM jobs WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($job_query);
    $stmt->bind_param("ii", $job_id, $employer_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $check_query = "SELECT id FROM shortlisted_candidates WHERE job_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $job_id, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Candidate already shortlisted.";
        } else {
            $shortlist_query = "INSERT INTO shortlisted_candidates (job_id, user_id, shortlisted_at) VALUES (?, ?, NOW())";
            $shortlist_stmt = $conn->prepare($shortlist_query);
            $shortlist_stmt->bind_param("ii", $job_id, $user_id);

            if ($shortlist_stmt->execute()) {
                $message = "Candidate successfully shortlisted.";
            } else {
                $message = "Failed to shortlist the candidate.";
            }
            

            $shortlist_stmt->close();
        }

        $check_stmt->close();
    } else {
        $message = "You do not have permission to manage this job.";
    }

    $stmt->close();
}

$shortlist_sql = "
    SELECT s.id, j.job_title, u.first_name, u.last_name, s.shortlisted_at 
    FROM shortlisted_candidates AS s
    JOIN jobs AS j ON s.job_id = j.id
    JOIN users AS u ON s.user_id = u.id
    WHERE j.user_id = ?
";
$shortlist_stmt = $conn->prepare($shortlist_sql);
$shortlist_stmt->bind_param("i", $employer_id);
$shortlist_stmt->execute();
$shortlisted_result = $shortlist_stmt->get_result();


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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shortlisted Candidates</title>
    <link rel="stylesheet" href="../css/shortlist_candidates.css">

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
    margin-top: 30px;
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

.message {
    text-align: center;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-weight: bold;
}

.message.success {
    background-color: #28a745;
    color: white;
}

.message.error {
    background-color: #dc3545;
    color: white;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

table th {
    background-color: #f2f2f2;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

button {
    background-color: #007BFF;
    color: white;
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
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

    <h1>Shortlisted Candidates</h1>

    <main>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <h2>Shortlisted Candidates</h2>

        <?php if ($shortlisted_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Candidate Name</th>
                        <th>Shortlisted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($candidate = $shortlisted_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['shortlisted_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No candidates have been shortlisted yet.</p>
        <?php endif; ?>
    </main>

    <?php
    $shortlist_stmt->close();
    $conn->close();
    ?>
</body>
</html>
