<?php
// Include your database connection file
include('connection.php');

$error_message = $success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $new_password = $_POST['new_password'];
    
    // Check if email exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $hashed_password, $email);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $success_message = "Your password has been successfully reset. You can now log in with your new password.";
        } else {
            $error_message = "An error occurred while resetting your password. Please try again.";
        }
    } else {
        $error_message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="login-box">
            <h2 class="text-center mb-4" style="color: #1a2238;">Reset Password</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php else: ?>
                <form id="resetForm" action="" method="POST">
                    <div class="input-box">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-box">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="button">
                        <input type="button" value="Reset Password" onclick="confirmReset()">
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="login-options">
                <a href="login.php" style="color: #1a2238;">Back to Login</a>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Password Reset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reset your password?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" onclick="submitReset()">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function confirmReset() {
            var email = document.getElementById('email').value;
            var newPassword = document.getElementById('new_password').value;

            if (email.trim() === '' || newPassword.trim() === '') {
                alert('Please fill in both email and new password fields.');
                return;
            }

            var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        }

        function submitReset() {
            document.getElementById('resetForm').submit();
        }
    </script>
</body>
</html>
