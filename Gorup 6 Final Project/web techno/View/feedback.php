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

$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$feedback_status = null;
if ($role === 'Job Seeker' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_text'])) {
    $feedback_text = $_POST['feedback_text'];

    $employer_id_query = "SELECT id FROM users WHERE role = 'Employer' LIMIT 1";
    $employer_result = $conn->query($employer_id_query);
    $employer = $employer_result->fetch_assoc();

    if ($employer) {
        $employer_id = $employer['id'];

        $insert_sql = "INSERT INTO feedback (job_seeker_id, employer_id, feedback_text, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iis", $user_id, $employer_id, $feedback_text);

        if ($stmt->execute()) {
            $feedback_status = "Feedback submitted successfully.";
        } else {
            $feedback_status = "Error submitting feedback: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $feedback_status = "No employer found to receive feedback.";
    }
}

$feedback_list = [];
if ($role === 'Employer') {
    $sql = "
        SELECT feedback.feedback_text, feedback.created_at, users.first_name AS job_seeker_name
        FROM feedback
        JOIN users ON feedback.job_seeker_id = users.id
        WHERE feedback.employer_id = ?
        ORDER BY feedback.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback_list = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link rel="stylesheet" href="feedback.css">

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

.container {
    padding: 20px;
    background-color: #fff;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #444;
    margin-bottom: 15px;
}

form {
    margin-top: 20px;
}

form textarea {
    width: 100%;
    height: 120px;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    resize: vertical;
}

form button {
    background-color: #007BFF;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

form button:hover {
    background-color: #0056b3;
}

.message {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-weight: bold;
    text-align: center;
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
    <div class="container">
        <h1>Feedback</h1>

        <?php if ($role === 'Job Seeker'): ?>
            <h2>Submit Your Feedback</h2>
            <?php if ($feedback_status): ?>
                <p><?php echo $feedback_status; ?></p>
            <?php endif; ?>
            <form method="POST">
                <label for="feedback_text">Your Feedback:</label>
                <textarea id="feedback_text" name="feedback_text" required></textarea>
                <button type="submit">Submit Feedback</button>
            </form>
        <?php elseif ($role === 'Employer'): ?>
            <h2>Feedback Received</h2>
            <?php if (!empty($feedback_list)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Job Seeker Name</th>
                            <th>Feedback</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback_list as $feedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['job_seeker_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No feedback received yet.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
