<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointmentId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT cancellation_reason FROM appointments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $appointmentId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $appointment = $result->fetch_assoc();
    echo json_encode(['success' => true, 'reason' => $appointment['cancellation_reason']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Appointment not found or unauthorized access']);
}

$stmt->close();
$conn->close();
?>
