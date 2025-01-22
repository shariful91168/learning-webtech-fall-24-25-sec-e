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
    header("Location: signin.html");
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5; 
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
    transition: background-color 0.3s ease, color 0.3s ease;
}

header .menu ul li a:hover {
    background-color: #555;
    color: #ffeb3b; /* On hover, change text color to a yellow shade */
}

h1 {
    text-align: center;
    margin-top: 20px;
    color: #333;
}

.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.hi {
    text-align: center;
}

h2 {
    text-align: center;
    color: #333;
}

p {
    font-size: 16px;
    line-height: 1.5;
    color: #555;
    margin-bottom: 10px;
}

strong {
    color: #333;
}

.hi a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 15px;
    text-decoration: none;
    color: #fff;
    background-color: #007bff;
    border-radius: 4px;
    font-size: 14px;
    text-align: center;
    transition: background-color 0.3s ease;
}

.hi a:hover {
    background-color: #0056b3;
}

img {
    height: 100px;
    width: 100px;
    border-radius: 50%;
    margin: 10px 0;
}

.profile-picture {
    text-align: center;
}

.profile-picture img {
    max-width: 150px;
    border-radius: 50%;
    border: 3px solid #007bff;
    padding: 5px;
}

.no-data {
    color: #777;
    font-style: italic;
}

footer {
    text-align: center;
    font-size: 12px;
    padding: 10px;
    background-color: #333;
    color: white;
    position: fixed;
    width: 100%;
    bottom: 0;
}

footer a {
    color: white;
    text-decoration: none;
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

    <h1>Profile</h1>
<div class="hi">
    <?php if (!empty($user['profile_picture'])): ?>
        <img src="../Controller/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
    <?php else: ?>
        No profile picture uploaded.
    <?php endif; ?>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? 'Not Provided'); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Not Provided'); ?></p>
    <p><strong>CV:</strong><br>
        <?php if (!empty($user['cv'])): ?>
            <a href="../Controller/uploads/<?php echo htmlspecialchars($user['cv']); ?>" target="_blank">View CV</a>
        <?php else: ?>
            No CV uploaded.
        <?php endif; ?>
    </p>
    <a href="edit_profile.php">Edit Profile</a>
</div>
</body>
</html>
