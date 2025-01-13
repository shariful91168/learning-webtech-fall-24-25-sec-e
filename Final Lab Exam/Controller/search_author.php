<?php
// Database connection details
$host = 'localhost';
$dbname = 'blog_system';
$dbuser = 'root';
$dbpass = '';

// Create a connection to the database
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the search query is set
if (isset($_GET['query'])) {
    $searchTerm = $conn->real_escape_string($_GET['query']); // Escape the search term to prevent SQL injection

    // SQL query to search authors by name, username, or contactno
    $sql = "SELECT * FROM authors WHERE name LIKE '%$searchTerm%' OR username LIKE '%$searchTerm%' OR contactno LIKE '%$searchTerm%'";
    $result = $conn->query($sql);

    // Check if any authors match the search query
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
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
}

$conn->close();
?>
