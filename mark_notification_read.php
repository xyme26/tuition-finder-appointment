<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$notification_id = intval($_POST['notification_id']);

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
}

$stmt->close();
$conn->close();