<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../View/login.html');
    exit();
}

// Database connection details
$host = 'localhost';      // Replace with your database host
$dbname = 'blog_system';  // Replace with your database name
$dbuser = 'root';         // Replace with your database username
$dbpass = '';             // Replace with your database password

// Create a connection to the database
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all authors from the database (initial load)
$sql = "SELECT * FROM authors";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Author List</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h1>Author List</h1>

    <input type="text" id="search" placeholder="Search authors by name, username, or contact..." />
    <button id="searchBtn">Search</button>

    <br><br>

    <div id="authorsTable">
        <!-- Table to display authors -->
        <?php
        if ($result->num_rows > 0) {
            echo "<table border='1' id='authorsTableContent'>";
            echo "<tr><th>Name</th><th>Contact No.</th><th>Username</th><th>Actions</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['contactno'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>
                        <a href='update_author.php?id=" . $row['id'] . "'>Update</a> | 
                        <a href='delete_author.php?id=" . $row['id'] . "'>Delete</a>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No authors found!";
        }
        ?>
    </div>

    <br><br>
    <a href="dashboard.php">Back to Dashboard</a>

    <script>
        // jQuery function to handle search button click
        $(document).ready(function() {
            // When the search button is clicked
            $("#searchBtn").click(function() {
                var searchTerm = $("#search").val(); // Get the search term

                // AJAX request to search authors in the database
                $.ajax({
                    url: 'search_author.php',  // PHP file that will handle the search
                    method: 'GET',
                    data: { query: searchTerm }, // Send the search query
                    success: function(response) {
                        // Replace the table content with the new results
                        $('#authorsTable').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
