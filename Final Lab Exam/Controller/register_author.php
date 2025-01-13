<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../View/login.html');
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize the form data
    $name = trim($_POST['name']);
    $contactno = trim($_POST['contactno']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs (simple example)
    if (empty($name) || empty($contactno) || empty($username) || empty($password)) {
        $error_message = "All fields are required!";
    } else {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Database connection details
        $host = 'localhost';      // Replace with your database host
        $dbname = 'blog_system'; // Replace with your database name
        $dbuser = 'root';          // Replace with your database username
        $dbpass = '';              // Replace with your database password

        // Create a connection to the database
        $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Escape inputs to prevent SQL injection
        $name = $conn->real_escape_string($name);
        $contactno = $conn->real_escape_string($contactno);
        $username = $conn->real_escape_string($username);

        // Insert the new author into the database
        $sql = "INSERT INTO authors (name, contactno, username, password) 
                VALUES ('$name', '$contactno', '$username', '$hashedPassword')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to the author list or dashboard after successful registration
            header('Location: author_list.php');
            exit();
        } else {
            $error_message = "Error: " . $conn->error;
        }

        // Close the database connection
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Author</title>
</head>
<body>
    <h1>Register New Author</h1>

    <?php
    // Display any error message if there's one
    if (isset($error_message)) {
        echo "<p style='color: red;'>$error_message</p>";
    }
    ?>

    <form method="post" action="register_author.php">
        Name: <input type="text" name="name" required><br>
        Contact No.: <input type="text" name="contactno" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>

        <input type="submit" value="Register Author">
    </form>

    <br><br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
