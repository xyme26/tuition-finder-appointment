<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$center_id = $_POST['center_id'];

$stmt = $conn->prepare("INSERT INTO favorites (user_id, tuition_center_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $center_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to add favorite']);
}

$stmt->close();
$conn->close();
