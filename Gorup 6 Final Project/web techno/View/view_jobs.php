<?php
session_start();

$confirm_delete_id = null;

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
$role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (isset($_GET['action']) && $_GET['action'] === 'search_jobs') {
    $search = $_GET['search'] ?? '';

    if ($role === 'Employer') {
        $sql = "SELECT * FROM jobs WHERE user_id = ? AND (job_title LIKE ? OR company_name LIKE ? OR location LIKE ?) ORDER BY posted_at DESC";
        $stmt = $conn->prepare($sql);
        $like_search = '%' . $search . '%';
        $stmt->bind_param("isss", $user_id, $like_search, $like_search, $like_search);
    } else {
        $sql = "SELECT * FROM jobs WHERE job_title LIKE ? OR company_name LIKE ? OR location LIKE ? ORDER BY posted_at DESC";
        $stmt = $conn->prepare($sql);
        $like_search = '%' . $search . '%';
        $stmt->bind_param("sss", $like_search, $like_search, $like_search);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $jobs = [];
    while ($job = $result->fetch_assoc()) {
        $jobs[] = $job;
    }

    echo json_encode($jobs);
    exit;
}

if ($role === 'Employer') {
    $sql = "SELECT * FROM jobs WHERE user_id = ? ORDER BY posted_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql = "SELECT * FROM jobs ORDER BY posted_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($role === 'Employer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_job_id'])) {
        $confirm_delete_id = intval($_POST['delete_job_id']);
    } elseif (isset($_POST['confirm_delete_id'], $_POST['confirm_delete'])) {
        $confirm_delete_id = intval($_POST['confirm_delete_id']);

        if ($_POST['confirm_delete'] === 'yes') {
            $delete_sql = "DELETE FROM jobs WHERE id = ? AND user_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $confirm_delete_id, $user_id);

            if ($delete_stmt->execute()) {
                echo "<p>Job successfully deleted.</p>";
                header("Location: view_jobs.php"); 
                exit;
            } else {
                echo "<p>Error deleting job: " . $delete_stmt->error . "</p>";
            }
            $delete_stmt->close();
        }
        $confirm_delete_id = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role === 'Employer' ? 'Jobs You Have Posted' : 'Available Jobs'; ?></title>

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



h1 {
    text-align: center;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background-color: #fff;
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.button {
    padding: 5px 10px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    text-align: center;
    border: none;
}

.delete {
    background-color: #ff4d4d;
}

.apply {
    background-color: #4CAF50;
}

.post-job-link {
    display: inline-block;
    margin: 20px 0;
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.post-job-link:hover {
    background-color: #0056b3;
}

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    <main>
        <div class="container">
            <h1><?php echo $role === 'Employer' ? 'Jobs You Have Posted' : 'Available Jobs'; ?></h1>

            <!-- Search Bar -->
            <input type="text" id="search" placeholder="Search jobs..." oninput="searchJobs()">

            <div id="jobs-container">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Company Name</th>
                                <th>Location</th>
                                <th>Salary</th>
                                <th>Job Type</th>
                                <th>Experience Level</th>
                                <th>Description</th>
                                <th>Posted At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="jobs-table-body">
                            <?php while ($job = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($job['location']); ?></td>
                                    <td><?php echo htmlspecialchars($job['salary']); ?></td>
                                    <td><?php echo htmlspecialchars($job['job_type']); ?></td>
                                    <td><?php echo htmlspecialchars($job['experience_level']); ?></td>
                                    <td><?php echo htmlspecialchars($job['description']); ?></td>
                                    <td><?php echo htmlspecialchars($job['posted_at']); ?></td>
                                    <td>
                                    <?php if ($role === 'Employer'): ?>
                                        <?php if ($confirm_delete_id === $job['id']): ?>
                                            <!-- Confirmation Form -->
                                            <form method="POST">
                                                <p>Are you sure?</p>
                                                <input type="hidden" name="confirm_delete_id" value="<?php echo $job['id']; ?>">
                                                <button type="submit" name="confirm_delete" value="yes">Yes</button>
                                                <button type="submit" name="confirm_delete" value="no">No</button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Delete Button -->
                                            <form method="POST">
                                                <input type="hidden" name="delete_job_id" value="<?php echo $job['id']; ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <form action="../View/application.php" method="GET" style="display:inline;">
                                                <button type="submit" name="job_id" value="<?php echo $job['id']; ?>">Apply</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No jobs available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Elev8. All rights reserved.</p>
    </footer>

    <script>
        function searchJobs() {
            const searchQuery = document.getElementById('search').value;

            $.ajax({
                url: 'view_jobs.php',
                type: 'GET',
                data: { action: 'search_jobs', search: searchQuery },
                dataType: 'json',
                success: function (jobs) {
                    const tbody = $('#jobs-table-body');
                    tbody.empty();

                    if (jobs.length > 0) {
                        jobs.forEach(job => {
                            tbody.append(`
                                <tr>
                                    <td>${job.job_title}</td>
                                    <td>${job.company_name}</td>
                                    <td>${job.location}</td>
                                    <td>${job.salary}</td>
                                    <td>${job.job_type}</td>
                                    <td>${job.experience_level}</td>
                                    <td>${job.description}</td>
                                    <td>${job.posted_at}</td>
                                    <td>
                                        <?php if ($role === 'Employer'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="delete_job_id" value="${job.id}">
                                                <button type="submit">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="../View/application.php" method="GET">
                                                <button type="submit" name="job_id" value="${job.id}">Apply</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="9">No jobs found.</td></tr>');
                    }
                }
            });
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
