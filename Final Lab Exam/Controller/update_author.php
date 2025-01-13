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

// Ensure that the 'id' parameter is passed in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Get the ID of the author to update

    // Fetch the current data for the author with the given ID
    $sql = "SELECT * FROM authors WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $author = $result->fetch_assoc();
        $name = $author['name'];
        $contactno = $author['contactno'];
        $username = $author['username'];
        // You can leave password empty if you're not updating it
        $password = ''; 
    } else {
        echo "Author not found!";
        exit();
    }
} else {
    echo "No author ID specified!";
    exit();
}

// Update the author's information if the form is submitted
if (isset($_POST['submit'])) {
    // Collect the form data
    $name = $conn->real_escape_string($_POST['name']);
    $contactno = $conn->real_escape_string($_POST['contactno']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';

    // If the password is not empty, hash it
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE authors SET name='$name', contactno='$contactno', username='$username', password='$hashed_password' WHERE id = $id";
    } else {
        $update_sql = "UPDATE authors SET name='$name', contactno='$contactno', username='$username', WHERE id = $id";
    }

    // Execute the update query
    if ($conn->query($update_sql) === TRUE) {
        echo "Author updated successfully!";
        header('Location: author_list.php'); // Redirect to author list
        exit();
    } else {
        echo "Error updating author: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Author</title>
</head>
<body>
    <h1>Update Author Information</h1>
    <form method="post" action="">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br>
        Contact No.: <input type="text" name="contactno" value="<?php echo htmlspecialchars($contactno); ?>" required><br>
        Username: <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>
        Password: <input type="password" name="password"><br> 
        <input type="submit" name="submit" value="Update Author">
    </form>

    <br><br>
    <a href="author_list.php">Back to Author List</a>
</body>
</html>
