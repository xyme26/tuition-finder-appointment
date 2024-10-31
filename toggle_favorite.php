<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$center_id = $_POST['center_id'];

// Check if the favorite already exists
$check_stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
$check_stmt->bind_param("ii", $user_id, $center_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Favorite exists, so remove it
    $delete_stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $center_id);
    $success = $delete_stmt->execute();
    $action = 'removed';
} else {
    // Favorite doesn't exist, so add it
    $insert_stmt = $conn->prepare("INSERT INTO favorites (user_id, tuition_center_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $center_id);
    $success = $insert_stmt->execute();
    $action = 'added';
}

if ($success) {
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();