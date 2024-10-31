<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

if (!isset($_POST['appointmentId']) || !isset($_POST['cancelReason'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$appointmentId = intval($_POST['appointmentId']);
$cancelReason = $_POST['cancelReason'];
$newDate = $_POST['newDate'] ?? null;
$newTime = $_POST['newTime'] ?? null;

$conn->begin_transaction();

try {
    // Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $cancelReason, $appointmentId);
    $stmt->execute();

    // Get user_id for the appointment
    $stmt = $conn->prepare("SELECT user_id FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $userId = $appointment['user_id'];

    // Create notification for the user
    $notificationMessage = "Your appointment has been cancelled by the admin. Reason: " . $cancelReason;
    if ($newDate && $newTime) {
        $newDateTime = $newDate . ' ' . $newTime;
        $stmt = $conn->prepare("UPDATE appointments SET appointment_datetime = ? WHERE id = ?");
        $stmt->bind_param("si", $newDateTime, $appointmentId);
        $stmt->execute();
        $notificationMessage .= " A new date/time has been suggested: " . $newDate . " at " . $newTime;
    }
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $notificationMessage);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
