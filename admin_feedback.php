<?php
session_start();
include 'connection.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit();
}

$query = "SELECT f.id, f.rating, f.comment, f.created_at, u.username 
          FROM feedback f 
          LEFT JOIN users u ON f.user_id = u.id 
          ORDER BY f.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Feedback Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Feedback Results</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?></td>
                    <td>
                        <?php
                        switch ($row['rating']) {
                            case 'good':
                                echo '😃 Good';
                                break;
                            case 'neutral':
                                echo '😐 Neutral';
                                break;
                            case 'bad':
                                echo '😞 Bad';
                                break;
                            default:
                                echo 'Unknown';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
