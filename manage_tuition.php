<?php
// Purpose: Manages tuition center information
// This file allows administrators to view, add, edit, and delete tuition centers

session_start();
$current_page = 'manage_tuition';
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connection.php';

// Initialize success and error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

// Clear the session variables after retrieving them
unset($_SESSION['success']);
unset($_SESSION['error']);

// Main Functionality:
// 1. Displays list of all tuition centers
// 2. Allows deletion of tuition centers
// 3. Provides links to edit tuition center details
// 4. Provides links to manage tuition center availability
// 5. Shows success/error messages for admin actions

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $center_id = intval($_GET['delete']);

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM tuition_centers WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $center_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success = "Tuition center deleted successfully.";
        } else {
            $error = "Failed to delete tuition center or center not found.";
        }

        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}

// Fetch all tuition centers to display
$query = "SELECT id, name, address AS location, description, contact, price_range, created_at FROM tuition_centers";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tuition Centers - Admin</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br><br><br>
    <div class="container-fluid admin-dashboard-container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Manage Tuition Centers</h2>

                <!-- Display success or error messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Add New Tuition Center Button -->
                <a href="add_tuition.php" class="btn btn-primary mb-3">Add New Tuition Center</a>

                <!-- Tuition Centers Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Contact</th>
                                <th>Price Range</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($center = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($center['id']); ?></td>
                                        <td><?php echo htmlspecialchars($center['name']); ?></td>
                                        <td><?php echo htmlspecialchars($center['location']); ?></td>
                                        <td><?php echo htmlspecialchars($center['description']); ?></td>
                                        <td><?php echo htmlspecialchars($center['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($center['price_range']); ?></td>
                                        <td><?php echo htmlspecialchars($center['created_at']); ?></td>
                                        <td>
                                            <a href="edit_tuition.php?id=<?php echo $center['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="manage_tuition.php?delete=<?php echo $center['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tuition center?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No tuition centers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>

    <script>
        // Set the current year
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>
</html>
