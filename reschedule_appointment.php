<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['appointmentId']) || !isset($_POST['newDate']) || !isset($_POST['newTime']) || !isset($_POST['rescheduleReason'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointmentId = intval($_POST['appointmentId']);
$newDate = $_POST['newDate'];
$newTime = $_POST['newTime'];
$rescheduleReason = $_POST['rescheduleReason'];

// Combine date and time
$newDateTime = $newDate . ' ' . $newTime;

// Update the appointment
$stmt = $conn->prepare("UPDATE appointments SET appointment_datetime = ?, reschedule_reason = ?, status = 'rescheduled' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssii", $newDateTime, $rescheduleReason, $appointmentId, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reschedule appointment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
