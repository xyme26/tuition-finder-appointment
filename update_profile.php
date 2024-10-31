<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];

    $fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'username'];
    $updates = [];
    $types = '';
    $values = [];

    $fieldMap = [
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'phone' => 'phone_number',
        'address' => 'address',
        'username' => 'username'
    ];

    // Debug: Print received POST data
    error_log("Received POST data: " . print_r($_POST, true));

    foreach ($fields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $dbField = $fieldMap[$field];
            $updates[] = "$dbField = ?";
            $types .= 's';
            $values[] = $_POST[$field];
        }
    }

    if (!empty($updates)) {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= 'i';
        $values[] = $userId;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            // Update session if username was changed
            if (isset($_POST['username'])) {
                $_SESSION['username'] = $_POST['username'];
            }
            // Update session if name was changed
            if (isset($_POST['first_name']) || isset($_POST['last_name'])) {
                $_SESSION['name'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
            }
            echo json_encode(['success' => true]);
        } else {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
