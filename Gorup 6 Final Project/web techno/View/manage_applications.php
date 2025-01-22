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
    header("Location: ../signin/signin.html");
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

$employer_id = $_SESSION['user_id'];

$sql = "
    SELECT a.id AS application_id, a.user_id, a.job_id, a.applied_at, 
           j.job_title, u.first_name, u.last_name, u.cv 
    FROM applications AS a
    JOIN jobs AS j ON a.job_id = j.id
    JOIN users AS u ON a.user_id = u.id
    WHERE j.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$success_message = $_GET['success'] ?? null;
$error_message = $_GET['error'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications</title>
    <link rel="stylesheet" href="../css/manage_applications.css">

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

.success, .error {
    text-align: center;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.success {
    background-color: #28a745;
    color: white;
}

.error {
    background-color: #dc3545;
    color: white;
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
    <h1>Manage Applications</h1>
    <main>
        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php elseif ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <h2>Applications</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant Name</th>
                        <th>Resume</th>
                        <th>Applied At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($application = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
                            <td>
                                <?php if (!empty($application['resume_path'])): ?>
                                    <a href="../uploads/resumes/<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank">View Resume</a>
                                <?php else: ?>
                                    No Resume
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($application['applied_at']); ?></td>
                            <td>
                                <form method="POST" action="shortlist_candidate.php" style="display:inline;">
                                    <input type="hidden" name="job_id" value="<?php echo $application['job_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>">
                                    <button type="submit">Shortlist</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No applications found.</p>
        <?php endif; ?>

        <h2>Shortlisted Candidates</h2>
        <?php
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
        ?>

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
            <p>No shortlisted candidates yet.</p>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
$stmt->close();
$shortlist_stmt->close();
$conn->close();
?>
