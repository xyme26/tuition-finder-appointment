<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update appointment status to completed
        $query = "UPDATE appointments SET 
                 status = 'completed',
                 completed_at = NOW()
                 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception('Failed to update appointment status');
        }

        // Get user_id for notification
        $userQuery = "SELECT user_id, appointment_datetime 
                     FROM appointments 
                     WHERE id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("i", $appointmentId);
        $userStmt->execute();
        $result = $userStmt->get_result();
        $appointment = $result->fetch_assoc();

        // Create notification for user
        if ($appointment) {
            $notificationMsg = "Your appointment on " . date('Y-m-d H:i', strtotime($appointment['appointment_datetime'])) . " has been marked as completed.";
            $notifyQuery = "INSERT INTO notifications (user_id, message, created_at) 
                           VALUES (?, ?, NOW())";
            $notifyStmt = $conn->prepare($notifyQuery);
            $notifyStmt->bind_param("is", $appointment['user_id'], $notificationMsg);
            $notifyStmt->execute();
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update appointment status: ' . $e->getMessage()]);
    }

    // Close statements
    if (isset($stmt)) $stmt->close();
    if (isset($userStmt)) $userStmt->close();
    if (isset($notifyStmt)) $notifyStmt->close();
}

$conn->close();
?>
