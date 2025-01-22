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
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
 
    $profile_picture = $_FILES['profile_picture']['name'] ? time() . '_' . $_FILES['profile_picture']['name'] : null;
    $cv = $_FILES['cv']['name'] ? time() . '_' . $_FILES['cv']['name'] : null;
 
    if ($profile_picture) {
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], "uploads/$profile_picture");
    }
 
    if ($cv) {
        move_uploaded_file($_FILES['cv']['tmp_name'], "uploads/$cv");
    }
 
    $sql = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, profile_picture = COALESCE(?, profile_picture), cv = COALESCE(?, cv) WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $phone_number, $address, $profile_picture, $cv, $user_id);
 
    if ($stmt->execute()) {
        header("Location: ../View/profile.php");
        exit;
    } else {
        echo "Error: Could not update profile.";
    }
 
    $stmt->close();
}
 
$conn->close();
?>
 
 