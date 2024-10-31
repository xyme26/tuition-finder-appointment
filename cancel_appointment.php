<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['appointmentId']) || !isset($_POST['reasons'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointmentId = intval($_POST['appointmentId']);
$reasons = implode(', ', $_POST['reasons']);

$stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $reasons, $appointmentId, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
}

$stmt->close();
$conn->close();
?>
