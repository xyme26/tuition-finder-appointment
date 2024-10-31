<?php
// fetch_chart.php
require_once 'connection.php';

header('Content-Type: application/json');

// Handle different data types for Google Charts
$type = $_GET['type'] ?? '';

$response = [];

if ($type == 'tuition_centers') {
    // Get top 5 tuition centers by average rating only
    $query = "SELECT 
                t.name AS center,
                ROUND(AVG(r.rating), 1) as avg_rating
              FROM tuition_centers t
              LEFT JOIN reviews r ON t.id = r.tuition_center_id
              GROUP BY t.id, t.name
              HAVING avg_rating > 0
              ORDER BY avg_rating DESC
              LIMIT 5";

    try {
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'center' => $row['center'],
                'avg_rating' => (float)$row['avg_rating']
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching tuition centers: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Update the appointments chart to include review data
if ($type == 'appointments') {
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $month = isset($_GET['month']) && $_GET['month'] !== 'all' ? intval($_GET['month']) : null;

    $query = "SELECT 
                " . ($month ? "DAY(COALESCE(a.appointment_datetime, r.created_at)) as day" 
                          : "MONTH(COALESCE(a.appointment_datetime, r.created_at)) as month") . ",
                COUNT(DISTINCT a.id) as appointments,
                COUNT(DISTINCT r.id) as reviews
              FROM (
                SELECT id, appointment_datetime FROM appointments
                UNION ALL
                SELECT id, created_at FROM reviews
              ) combined_dates
              LEFT JOIN appointments a ON a.id = combined_dates.id
              LEFT JOIN reviews r ON r.id = combined_dates.id
              WHERE YEAR(COALESCE(a.appointment_datetime, r.created_at)) = ?";
    
    $params = [$year];
    $types = "i";

    if ($month !== null) {
        $query .= " AND MONTH(COALESCE(a.appointment_datetime, r.created_at)) = ?";
        $params[] = $month;
        $types .= "i";
    }

    $query .= " GROUP BY " . ($month ? "day" : "month");
    $query .= " ORDER BY " . ($month ? "day" : "month");

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $period = $month ? $row['day'] : $row['month'];
            $response[] = [
                ($month ? 'day' : 'month') => (int)$period,
                'appointments' => (int)$row['appointments'],
                'reviews' => (int)$row['reviews']
            ];
        }
        error_log("Combined data: " . json_encode($response)); // Add logging
    } catch (Exception $e) {
        error_log("Error fetching combined data: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Update user accounts chart to include review activity
if ($type == 'user_accounts') {
    // Get monthly user registrations for current year
    $year = date('Y');
    
    $query = "SELECT 
                MONTH(created_at) as month,
                COUNT(*) as registrations
              FROM users
              WHERE YEAR(created_at) = ?
              GROUP BY MONTH(created_at)
              ORDER BY month";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize all months with 0
        $monthlyData = array_fill(1, 12, 0);

        while ($row = $result->fetch_assoc()) {
            $monthlyData[$row['month']] = (int)$row['registrations'];
        }

        // Convert to response format
        foreach ($monthlyData as $month => $count) {
            $response[] = [
                'month' => $month,
                'registrations' => $count
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching user accounts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode($response);
?>
