<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointmentId = intval($_GET['id']);

$stmt = $conn->prepare("SELECT reschedule_reason FROM appointments WHERE id = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'reason' => $row['reschedule_reason']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
}

$stmt->close();
$conn->close();
?>
