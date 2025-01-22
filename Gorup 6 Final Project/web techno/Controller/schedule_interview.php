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

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['applicantId'], $data['interviewDate'])) {
    $applicantId = $data['applicantId'];
    $interviewDate = $data['interviewDate'];

    // Schedule the interview
    $stmt = $conn->prepare("INSERT INTO interview_schedule (applicant_id, interview_date) VALUES (?, ?)");
    $stmt->bind_param("is", $applicantId, $interviewDate);

    if ($stmt->execute()) {
        // Add a notification for the applicant
        $notification_message = "You have been shortlisted for an interview on " . $interviewDate;
        $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $notification_stmt->bind_param("is", $applicantId, $notification_message);

        if ($notification_stmt->execute()) {
            echo json_encode(["message" => "Interview scheduled and notification sent successfully!"]);
        } else {
            echo json_encode(["message" => "Interview scheduled, but notification failed."]);
        }
        $notification_stmt->close();
    } else {
        echo json_encode(["message" => "Error scheduling interview."]);
    }
    $stmt->close();
} else {
    echo json_encode(["message" => "Invalid data."]);
}

$conn->close();
?>
