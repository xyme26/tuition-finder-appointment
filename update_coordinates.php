<?php
include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['latitude']) && isset($data['longitude'])) {
    $sql = "UPDATE tuition_centers 
            SET latitude = ?, longitude = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", 
        $data['latitude'], 
        $data['longitude'], 
        $data['id']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
}
?>
