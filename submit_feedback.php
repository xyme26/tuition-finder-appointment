<?php
session_start();
include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'] ?? null;
$rating = $data['rating'];
$comment = $data['comment'];

$stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();