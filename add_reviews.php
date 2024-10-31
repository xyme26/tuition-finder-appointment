<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in to submit a review.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $tuition_center_id = $_POST['tuition_center_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Insert the review into the database
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, tuition_center_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $tuition_center_id, $rating, $review_text);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
