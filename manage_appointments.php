<?php
// admin/manage_appointments.php
session_start();
$current_page = 'manage_appointments';
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

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $appointment_id = intval($_GET['delete']);

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Appointment deleted successfully.";
    } else {
        $error = "Failed to delete appointment.";
    }

    $stmt->close();
}

// Handle approval if requested
if (isset($_GET['approve'])) {
    $appointment_id = intval($_GET['approve']);

    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Appointment approved successfully.";
        
        // Create notification for the user
        $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) SELECT user_id, CONCAT('Your appointment for ', DATE_FORMAT(appointment_datetime, '%Y-%m-%d'), ' at ', TIME_FORMAT(appointment_datetime, '%H:%i'), ' has been approved.') FROM appointments WHERE id = ?");
        $notification_stmt->bind_param("i", $appointment_id);
        $notification_stmt->execute();
        $notification_stmt->close();
    } else {
        $error = "Failed to approve appointment.";
    }

    $stmt->close();
}

// Fetch all appointments
$sql = "SELECT a.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, tc.name AS tuition_center, a.appointment_datetime, a.status, a.created_at 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN tuition_centers tc ON a.tuition_center_id = tc.id 
        ORDER BY a.appointment_datetime DESC";
$result = $conn->query($sql);
?>

<?php
// Function to send notification
function sendNotification($appointment_id) {
    global $conn;
    
    // Fetch appointment details
    $stmt = $conn->prepare("SELECT user_id, appointment_datetime FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    
    if ($appointment) {
        $user_id = $appointment['user_id'];
        $appointment_datetime = $appointment['appointment_datetime'];
        
        // Insert notification into notifications table
        $notification_text = "Your appointment for " . $appointment_datetime . " has been approved.";
        $insert_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $insert_stmt->bind_param("is", $user_id, $notification_text);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br>
    <div class="container-fluid admin-dashboard-container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Manage Appointments</h2>

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

                <!-- Appointments Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Tuition Center</th>
                                <th>Appointment Date/Time</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($appointment = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['tuition_center']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_datetime']); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            switch($appointment['status']) {
                                                case 'completed':
                                                    $statusClass = 'text-success fw-bold';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'text-danger';
                                                    break;
                                                case 'approved':
                                                    $statusClass = 'text-primary';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'text-warning';
                                                    break;
                                                default:
                                                    $statusClass = 'text-secondary';
                                            }
                                            ?>
                                            <span class="<?php echo $statusClass; ?>">
                                                <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                            </span>
                                            <?php 
                                            if ($appointment['status'] === 'cancelled') {
                                                echo ' <a href="#" onclick="showCancellationReason(' . $appointment['id'] . ')">Reason</a>';
                                            } elseif ($appointment['status'] === 'rescheduled') {
                                                echo ' <a href="#" onclick="showRescheduleReason(' . $appointment['id'] . ')">Reason</a>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['created_at']); ?></td>
                                        <td>
                                            <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                                <button type="button" class="btn btn-success btn-sm" onclick="completeAppointment(<?php echo $appointment['id']; ?>)">Complete</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                                                <button class="btn btn-warning btn-sm" onclick="showCancelModal(<?php echo $appointment['id']; ?>)">Cancel</button>
                                            <?php endif; ?>
                                            
                                            <a href="manage_appointments.php?delete=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No appointments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>

    <!-- Add this modal at the bottom of your file -->
    <div class="modal fade" id="cancellationReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancellation Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cancellationReasonBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Add this modal at the bottom of your file -->
    <div class="modal fade" id="rescheduleReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="rescheduleReasonBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Add this modal at the bottom of your file -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelForm">
                        <input type="hidden" id="cancelAppointmentId" name="appointmentId">
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Reason for Cancellation</label>
                            <textarea class="form-control" id="cancelReason" name="cancelReason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="newDate" class="form-label">Suggest New Date (optional)</label>
                            <input type="date" class="form-control" id="newDate" name="newDate">
                        </div>
                        <div class="mb-3">
                            <label for="newTime" class="form-label">Suggest New Time (optional)</label>
                            <input type="time" class="form-control" id="newTime" name="newTime">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="cancelAppointment()">Confirm Cancellation</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Set the current year
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    function showCancellationReason(appointmentId) {
        fetch('get_cancellation_reason.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cancellationReasonBody').innerHTML = data.reason;
                    var modal = new bootstrap.Modal(document.getElementById('cancellationReasonModal'));
                    modal.show();
                } else {
                    alert('Failed to fetch cancellation reason: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the cancellation reason');
            });
    }

    function showRescheduleReason(appointmentId) {
        fetch('get_reschedule_reason.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('rescheduleReasonBody').innerHTML = data.reason;
                    var modal = new bootstrap.Modal(document.getElementById('rescheduleReasonModal'));
                    modal.show();
                } else {
                    alert('Failed to fetch reschedule reason: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the reschedule reason');
            });
    }

    function showCancelModal(appointmentId) {
        document.getElementById('cancelAppointmentId').value = appointmentId;
        var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
        cancelModal.show();
    }

    function cancelAppointment() {
        var form = document.getElementById('cancelForm');
        var formData = new FormData(form);

        fetch('cancel_appointment_admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment cancelled successfully');
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to cancel appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the appointment');
        });
    }

    function completeAppointment(appointmentId) {
        fetch('complete_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `appointment_id=${appointmentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment marked as complete.');
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to complete appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the appointment');
        });
    }
    </script>

</body>
</html>
