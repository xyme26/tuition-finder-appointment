<?php
session_start();
include 'connections.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in only to favorite tuition centers.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $tuition_center_id = $_POST['tuition_center_id'];

    // Check if the user already favorited this center
    $checkFavorite = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
    $checkFavorite->bind_param("ii", $user_id, $tuition_center_id);
    $checkFavorite->execute();
    $result = $checkFavorite->get_result();

    if ($result->num_rows > 0) {
        echo "You have already favorited this tuition center.";
    } else {
        // Add to favorites
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, tuition_center_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $tuition_center_id);

        if ($stmt->execute()) {
            echo "Successfully added to favorites!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $checkFavorite->close();
    $stmt->close();
}
?>
