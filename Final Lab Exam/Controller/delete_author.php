<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../View/login.html');
    exit();
}

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

// Check if the ID is passed in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the author from the database
    $sql = "DELETE FROM authors WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "Author deleted successfully!";
        header('Location: author_list.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid request!";
}

// Close the database connection
$conn->close();
?>
