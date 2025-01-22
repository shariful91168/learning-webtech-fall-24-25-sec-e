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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employer') {
    die("Unauthorized access.");
}

if (isset($_GET['action']) && $_GET['action'] === 'get_applications') {
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $query = "
        SELECT a.id AS application_id, a.status, a.applied_at, 
               j.job_title, 
               u.first_name, u.last_name, u.email 
        FROM applications AS a
        JOIN jobs AS j ON a.job_id = j.id
        JOIN users AS u ON a.user_id = u.id
        WHERE CONCAT(u.first_name, ' ', u.last_name, u.email, j.job_title) LIKE ?
        ORDER BY a.applied_at DESC
    ";

    $stmt = $conn->prepare($query);
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }

    echo json_encode($applications);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Tracking System</title>

    <style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
    color: #333;
}
        h1 {
            text-align: center;
        }

        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .search-bar input {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .no-data {
            text-align: center;
            font-size: 16px;
            color: #888;
        }

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
    <h1>Applicant Tracking System</h1>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by applicant name, email, or job title...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Applicant Name</th>
                <th>Email</th>
                <th>Job Title</th>
                <th>Status</th>
                <th>Applied At</th>
            </tr>
        </thead>
        <tbody id="applicationsTable">
        </tbody>
    </table>

    <script>
        function fetchApplications(search = '') {
            fetch(`ats.php?action=get_applications&search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('applicationsTable');
                    tableBody.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(applicant => {
                            const row = `
                                <tr>
                                    <td>${applicant.first_name} ${applicant.last_name}</td>
                                    <td>${applicant.email}</td>
                                    <td>${applicant.job_title}</td>
                                    <td>${applicant.status}</td>
                                    <td>${applicant.applied_at}</td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="5" class="no-data">No applications found.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching applications:', error);
                });
        }

        document.getElementById('searchInput').addEventListener('input', function (e) {
            fetchApplications(e.target.value);
        });

        fetchApplications();
    </script>
</body>
</html>
