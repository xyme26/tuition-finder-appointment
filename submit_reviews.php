<?php
session_start(); // Start the session to access session variables
include 'connection.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Add error logging
error_log('Received review data: ' . print_r($data, true));

// Check if the data exists and is valid
if (isset($data['tuition_center_id'], $data['rating'], $data['comment'])) {
    $user_id = $_SESSION['user_id'];
    $tuition_center_id = $data['tuition_center_id'];
    $rating = intval($data['rating']);
    $comment = $data['comment'];

    try {
        // Check if user has already reviewed
        $check_sql = "SELECT id FROM reviews WHERE user_id = ? AND tuition_center_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $tuition_center_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'You have already submitted a review for this tuition center'
            ]);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Insert new review
        $insert_sql = "INSERT INTO reviews (user_id, tuition_center_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiis", $user_id, $tuition_center_id, $rating, $comment);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Review submitted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to submit review'
            ]);
        }
        $stmt->close();

    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required data'
    ]);
}

$conn->close();
?>
