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
    header("Location: ../View/signin.html");
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
$sql = "SELECT first_name, last_name, email, phone_number, address, profile_picture, cv FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
 
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="edit_profile.css">
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f7f7f7;
    color: #333;
}

h2 {
    text-align: center;
    margin-top: 20px;
    color: #333;
}

form {
    max-width: 600px;
    margin: 30px auto;
    padding-right: 10px;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    text-align: center;
}

input, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    align: center;
}

button:hover {
    background-color: #0056b3;
}

p {
    font-size: 14px;
    color: #666;
}

img {
    border-radius: 50%;
    margin-top: 10px;
}

input[type="file"] {
    padding: 5px;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

    </style>
</head>
<body>
    <h2>Edit Profile</h2>
    <form method="POST" action="../Controller/update_profile.php" enctype="multipart/form-data">
        <label for="first_name">First Name:</label><br>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required><br><br>
 
        <label for="last_name">Last Name:</label><br>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required><br><br>
 
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly><br><br>
 
        <label for="phone_number">Phone Number:</label><br>
        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"><br><br>
 
        <label for="address">Address:</label><br>
        <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea><br><br>
 
        <label for="profile_picture">Profile Picture:</label><br>
        <input type="file" id="profile_picture" name="profile_picture"><br>
        <p>
            Current:
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="../Controller/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="100">
            <?php else: ?>
                No file chosen
            <?php endif; ?>
        </p><br><br>
 
        <label for="cv">CV:</label><br>
        <input type="file" id="cv" name="cv"><br>
        <p>
            Current:
            <?php if (!empty($user['cv'])): ?>
                <a href="../Controller/uploads/<?php echo htmlspecialchars($user['cv']); ?>" target="_blank">View CV</a>
            <?php else: ?>
                No file chosen
            <?php endif; ?>
        </p><br><br>
 
        <button type="submit" name="save_changes">Save Changes</button>
    </form>
</body>
</html>
 
 