<?php
// save_appointment.php

include 'connection.php';

// Start the session
session_start();

// Get data from the AJAX request
$selectedDate = $_POST['date'];
$availableTime = $_POST['time'];
$userId = $_SESSION['user_id'];
$tuitionCenterId = $_POST['tuition_center_id'];

$appointmentDateTime = $selectedDate . ' ' . $availableTime;
$sql = "INSERT INTO appointments (appointment_datetime, status, user_id, tuition_center_id) VALUES (?, 'pending', ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $appointmentDateTime, $userId, $tuitionCenterId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
