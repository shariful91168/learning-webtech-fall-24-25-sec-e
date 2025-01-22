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

$notification_status = null;
if ($role === 'Employer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'], $_POST['message'])) {
        $title = $_POST['title'];
        $message = $_POST['message'];

        $job_seeker_query = "SELECT id FROM users WHERE role = 'Job Seeker'";
        $job_seeker_result = $conn->query($job_seeker_query);

        if ($job_seeker_result->num_rows > 0) {
            $insert_sql = "INSERT INTO notifications (employer_id, user_id, title, message, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_sql);

            while ($row = $job_seeker_result->fetch_assoc()) {
                $job_seeker_id = $row['id'];
                $stmt->bind_param("iiss", $user_id, $job_seeker_id, $title, $message);
                $stmt->execute();
            }

            $notification_status = "Notification sent successfully to all job seekers.";
            $stmt->close();
        } else {
            $notification_status = "No job seekers found to send the notification.";
        }
    }
}

$sent_notifications = [];
if ($role === 'Employer') {
    $sql = "
        SELECT notifications.id, notifications.title, notifications.message, notifications.created_at
        FROM notifications
        WHERE employer_id = ?
        ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sent_notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$received_notifications = [];
if ($role === 'Job Seeker') {
    $sql = "
        SELECT notifications.id, notifications.title, notifications.message, notifications.created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $received_notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="notifications.css">
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
    <div class="container">
        <h1>Notifications</h1>

        <?php if ($role === 'Employer'): ?>
            <h2>Send a Notification</h2>
            <?php if ($notification_status): ?>
                <p><?php echo $notification_status; ?></p>
            <?php endif; ?>
            <form method="POST">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>

                <button type="submit">Send Notification</button>
            </form>

            <h2>Sent Notifications</h2>
            <?php if (!empty($sent_notifications)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sent_notifications as $notification): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                <td><?php echo htmlspecialchars($notification['message']); ?></td>
                                <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No notifications sent yet.</p>
            <?php endif; ?>

        <?php elseif ($role === 'Job Seeker'): ?>
            <h2>Your Notifications</h2>
            <?php if (!empty($received_notifications)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($received_notifications as $notification): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                <td><?php echo htmlspecialchars($notification['message']); ?></td>
                                <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No notifications available.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
