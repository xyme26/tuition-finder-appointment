<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'];
    $userId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if appointment exists and hasn't been completed yet
        $checkQuery = "SELECT id, status FROM appointments 
                      WHERE id = ? AND user_id = ? 
                      AND status NOT IN ('completed', 'cancelled')";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $appointmentId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Appointment not found, already completed, or unauthorized');
        }

        // Update appointment status to completed
        $query = "UPDATE appointments SET 
                 status = 'completed',
                 completed_at = NOW() 
                 WHERE id = ? AND user_id = ? 
                 AND status NOT IN ('completed', 'cancelled')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $appointmentId, $userId);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception('Failed to update appointment status');
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    // Close statements
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($stmt)) $stmt->close();
}

$conn->close();
?>

