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

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: login.php");
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

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];
$opposite_role = $current_user_role === 'Employer' ? 'Job Seeker' : 'Employer';

$sql = "
    SELECT 
        u.id, 
        u.first_name, 
        u.last_name,
        COUNT(m.id) AS unread_count
    FROM users u
    LEFT JOIN messages m 
        ON u.id = m.sender_id 
        AND m.receiver_id = ? 
        AND m.is_read = 0
    WHERE u.role = ?
    GROUP BY u.id
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $current_user_id, $opposite_role);
$stmt->execute();
$users = $stmt->get_result();

$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if ($selected_user_id) {
    $sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $selected_user_id, $current_user_id);
    $stmt->execute();

    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $current_user_id, $selected_user_id, $selected_user_id, $current_user_id);
    $stmt->execute();
    $messages = $stmt->get_result();
} else {
    $messages = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = intval($_POST['receiver_id']);

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $current_user_id, $receiver_id, $message);
    $stmt->execute();

    header("Location: chat.php?user_id=$receiver_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
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
    transition: background-color 0.3s;
}

header .menu ul li a:hover {
    background-color: #555;
}

h1 {
    text-align: center;
}

/* Main layout */
.main {
    display: flex;
    height: calc(100vh - 80px); /* Full height minus header */
}

.sidebar {
    width: 250px;
    background-color: #f4f4f4;
    border-right: 1px solid #ddd;
    padding-top: 10px;
    overflow-y: auto;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
    transition: background-color 0.3s;
}

.sidebar ul li:hover {
    background-color: #e0e0e0;
}

.sidebar ul li a {
    color: #333;
    text-decoration: none;
}

.sidebar ul li a .unread {
    color: red;
    font-weight: bold;
}

/* Main content area */
.main {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.header {
    padding: 10px;
    background-color: #2c2e30;
    color: #fff;
    font-size: 18px;
    font-weight: bold;
}

.messages {
    flex-grow: 1;
    padding: 10px;
    background-color: #ffffff;
    overflow-y: auto;
}

.message {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.message .sender {
    font-weight: bold;
    color: #007bff;
}

.message .content {
    background-color: #f1f1f1;
    padding: 8px;
    border-radius: 5px;
    margin-top: 5px;
    max-width: 70%;
    word-wrap: break-word;
}

.message .timestamp {
    font-size: 12px;
    color: #aaa;
    text-align: right;
    margin-top: 5px;
}

/* Unread message styles */
.message.unread .content {
    background-color: #fff4e5;
    border-left: 5px solid #ff7b00;
}

/* Message form */
.message-form {
    display: flex;
    border-top: 1px solid #ddd;
    padding: 10px;
    background-color: #f9f9f9;
}

.message-form input {
    flex-grow: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.message-form button {
    margin-left: 10px;
    padding: 10px 20px;
    border: none;
    background-color: #007bff;
    color: #fff;
    border-radius: 5px;
    cursor: pointer;
}

.message-form button:hover {
    background-color: #0056b3;
}

/* Active user in sidebar */
.sidebar ul li a.active {
    background-color: #007bff;
    color: white;
    font-weight: bold;
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
                    <li><a href="../View/chat_system.html">ChatBox</a></li>
                <?php elseif ($role === 'Job Seeker') : ?>
                    <li><a href="../View/view_jobs.php">Jobs</a></li>
                    <li><a href="../View/applied_jobs.php">Applications</a></li>
                    <li><a href="../View/feedback.php">Feedback</a></li>
                    <li><a href="../View/resume_builder.php">Build Resume</a></li>
                    <li><a href="../View/interview_page.php">Interview</a></li>
                    <li><a href="../View/chat_system.html">ChatBox</a></li>
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
    <div class="sidebar">
        <ul>
            <?php while ($user = $users->fetch_assoc()): ?>
                <li>
                    <a href="?user_id=<?php echo $user['id']; ?>" class="<?php echo $selected_user_id == $user['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        <?php if ($user['unread_count'] > 0): ?>
                            <span class="unread">(<?php echo $user['unread_count']; ?>)</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <div class="main">
        <div class="header">
            <?php if ($selected_user_id): ?>
                Chat with 
                <?php
                $sql = "SELECT first_name, last_name FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $selected_user_id);
                $stmt->execute();
                $selected_user = $stmt->get_result()->fetch_assoc();
                echo htmlspecialchars($selected_user['first_name'] . ' ' . $selected_user['last_name']);
                ?>
            <?php else: ?>
                Select a user to start chatting
            <?php endif; ?>
        </div>
        <div class="messages">
            <?php if ($messages): ?>
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="message">
                        <div class="sender">
                            <?php echo $message['sender_id'] == $current_user_id ? 'You' : htmlspecialchars($selected_user['first_name'] . ' ' . $selected_user['last_name']); ?>
                        </div>
                        <div class="content"><?php echo htmlspecialchars($message['message']); ?></div>
                        <div class="timestamp"><?php echo $message['timestamp']; ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages yet.</p>
            <?php endif; ?>
        </div>
        <?php if ($selected_user_id): ?>
            <form method="POST" class="message-form">
                <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                <input type="text" name="message" placeholder="Type a message..." required>
                <button type="submit">Send</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
