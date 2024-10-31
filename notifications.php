<?php
session_start();
$current_page = 'profile';
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details and notification preferences
$query = "SELECT fav_update, upcoming_appointment, appointment_confirmation FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fav_update = $row['fav_update'];
    $upcoming_appointment = $row['upcoming_appointment'];
    $appointment_confirmation = $row['appointment_confirmation'];
} else {
    echo "Error retrieving user details.";
    exit();
}

// Update notification preferences on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fav_update = isset($_POST['fav_update']) ? 1 : 0;
    $upcoming_appointment = isset($_POST['upcoming_appointment']) ? 1 : 0;
    $appointment_confirmation = isset($_POST['appointment_confirmation']) ? 1 : 0;

    $update_query = "UPDATE users SET fav_update = ?, upcoming_appointment = ?, appointment_confirmation = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("iiii", $fav_update, $upcoming_appointment, $appointment_confirmation, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Notification preferences updated successfully.";
    } else {
        $error_message = "Error updating notification preferences.";
    }
}

// Rest of your HTML code remains the same
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Details</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    
    <link rel="stylesheet" href="style.css">

  <!-- Inline styling (for quick customizations) -->
<style>
    /* Container for the whole profile section */
    .profile-container {
        display:flex;
        padding: 20px;
        width:70%;
    }

    /* Profile details design */
    .profile-details {
        background-color: #f4db7d;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        margin-left: 20px;
        color: #1a2238;
    }

    .profile-details h2 {
        color: #1a2238;
    }

    .profile-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #1a2238;
    }

    .profile-item span {
        width: 40%;
    }

    .profile-item a {
        color: #ff6a3d;
        text-decoration: none;
        font-weight: bold;
    }

    .profile-item a:hover {
        color: #1a2238;
    }

    /* Left navigation design */
    .left-nav {
        background-color: #1a2238;
        padding: 20px;
        border-radius: 10px;
        color: white;
        width: 250px;
    }

    .left-nav ul {
        list-style-type: none;
        padding-left: 0;
    }

    .left-nav ul li {
        margin: 15px 0;
    }

    .left-nav ul li a {
        color: #f4db7d;
        text-decoration: none;
        font-weight: bold;
    }

    .left-nav ul li a:hover {
        color: #ff6a3d;
    }

    .left-nav ul li a.active{
        color: #ff6a3d;
        border-radius: 4px;
        border-color: #ffffff;
        border-style: solid;
    }

    button {
        background-color: #ff6a3d;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #1a2238;
    }

.form-check {
    display: flex; /* Use flexbox for better alignment */
    align-items: center; /* Center items vertically */
    margin-bottom: 15px; /* Space between form checks */
}

.form-check-input {
    display: none; /* Hide the default checkbox */
}

.form-check-label {
    position: relative;
    padding-left: 35px; /* Space for toggle */
    cursor: pointer;
    user-select: none;
    margin-right: 10px; /* Space between label and toggle */
}

.form-check-label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 20px;
    width: 35px;
    background-color: #ccc;
    border-radius: 25px;
    transition: background-color 0.3s;
}

.form-check-input:checked + .form-check-label::before {
    background-color: #007bff; /* Change this to your preferred color */
}

.form-check-label::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 5px;
    height: 10px;
    width: 10px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.form-check-input:checked + .form-check-label::after {
    transform: translateX(20px);
}

</style>
<body>
    <?php include 'header.php'; ?>
    <br><br><br><br>
    
    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Left navigation with custom design -->
        <div class="left-nav">
            <ul>
                <li><a href="profile.php">Personal Details</a></li>
                <li><a href="notifications.php" class="active">Notifications</a></li>
                <li><a href="review_history.php">Review History</a></li>
                <li><a href="favorite_history.php">Favorite History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Your existing HTML form for notifications -->
        <div class="profile-details">
    <h2>Notification Settings</h2>
    <?php
    if (isset($success_message)) {
        echo "<p class='alert alert-success'>$success_message</p>";
    }
    if (isset($error_message)) {
        echo "<p class='alert alert-danger'>$error_message</p>";
    }
    ?>
    <form method="POST" action="notifications.php">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fav_update" id="fav_update" value="1" 
            <?php echo $fav_update ? 'checked' : ''; ?>>
            <label class="form-check-label" for="fav_update">Favorite Updates</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="upcoming_appointment" id="upcoming_appointment" value="1" 
            <?php echo $upcoming_appointment ? 'checked' : ''; ?>>
            <label class="form-check-label" for="upcoming_appointment">Upcoming Appointments</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="appointment_confirmation" id="appointment_confirmation" value="1" 
            <?php echo $appointment_confirmation ? 'checked' : ''; ?>>
            <label class="form-check-label" for="appointment_confirmation">Appointment Confirmation</label>
        </div>
        <button type="submit" class="btn btn-primary">Update Notifications</button>
    </form>
</div>

    </div>
    <?php include 'footer.php'; ?>

    <script>

document.getElementById('currentYear').textContent = new Date().getFullYear();
function toggleNotification(checkbox, name) {
    const allCheckbox = document.getElementById('subscribe_all');
    if (checkbox.checked) {
        allCheckbox.checked = false; // Uncheck "Allow All Notifications" if individual is checked
    }
}

function toggleAllNotifications(checkbox) {
    const favUpdate = document.getElementById('fav_update');
    const upcomingAppointment = document.getElementById('upcoming_appointment');
    const appointmentConfirmation = document.getElementById('appointment_confirmation');

    // Check/uncheck all notifications based on "Allow All Notifications"
    favUpdate.checked = checkbox.checked;
    upcomingAppointment.checked = checkbox.checked;
    appointmentConfirmation.checked = checkbox.checked;

    // Disable/enable other checkboxes based on "Allow All Notifications"
    favUpdate.disabled = checkbox.checked;
    upcomingAppointment.disabled = checkbox.checked;
    appointmentConfirmation.disabled = checkbox.checked;
}
</script>

</body>
</head>
</html>
