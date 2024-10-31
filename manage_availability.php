<?php
/*
session_start();
require_once 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$tuition_center_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch tuition center details
$stmt = $conn->prepare("SELECT name FROM tuition_centers WHERE id = ?");
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$result = $stmt->get_result();
$tuition_center = $result->fetch_assoc();

if (!$tuition_center) {
    die("Tuition center not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete existing availability
    $stmt = $conn->prepare("DELETE FROM tuition_availability WHERE tuition_center_id = ?");
    $stmt->bind_param("i", $tuition_center_id);
    $stmt->execute();

    // Insert new availability
    $stmt = $conn->prepare("INSERT INTO tuition_availability (tuition_center_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, ?)");
    
    for ($day = 0; $day < 7; $day++) {
        if (isset($_POST["day_$day"]) && $_POST["day_$day"] === 'on') {
            $start_time = $_POST["start_time_$day"];
            $end_time = $_POST["end_time_$day"];
            $slot_duration = intval($_POST["slot_duration_$day"]);
            
            $stmt->bind_param("iissi", $tuition_center_id, $day, $start_time, $end_time, $slot_duration);
            $stmt->execute();
        }
    }

    // Handle unavailable dates
    $unavailable_dates = explode(',', $_POST['unavailable_dates']);
    $stmt = $conn->prepare("INSERT INTO tuition_unavailable_dates (tuition_center_id, unavailable_date) VALUES (?, ?)");
    foreach ($unavailable_dates as $date) {
        $date = trim($date);
        if (!empty($date)) {
            $stmt->bind_param("is", $tuition_center_id, $date);
            $stmt->execute();
        }
    }

    $_SESSION['success'] = "Availability updated successfully.";
    header("Location: manage_tuition.php");
    exit();
}

// Fetch existing availability
$stmt = $conn->prepare("SELECT * FROM tuition_availability WHERE tuition_center_id = ?");
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$availability_result = $stmt->get_result();
$availability = [];
while ($row = $availability_result->fetch_assoc()) {
    $availability[$row['day_of_week']] = $row;
}

// Fetch unavailable dates
$stmt = $conn->prepare("SELECT unavailable_date FROM tuition_unavailable_dates WHERE tuition_center_id = ?");
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$unavailable_dates_result = $stmt->get_result();
$unavailable_dates = [];
while ($row = $unavailable_dates_result->fetch_assoc()) {
    $unavailable_dates[] = $row['unavailable_date'];
}
*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Availability - <?php echo htmlspecialchars($tuition_center['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        .time-slots {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-5">
        <h2>Manage Availability for <?php echo htmlspecialchars($tuition_center['name']); ?></h2>
        <form method="post">
            <?php
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($days as $day_num => $day_name):
            ?>
                <div class="mb-3">
                    <label class="form-check-label">
                        <input type="checkbox" name="day_<?php echo $day_num; ?>" class="form-check-input day-checkbox" data-day="<?php echo $day_num; ?>" <?php echo isset($availability[$day_num]) ? 'checked' : ''; ?>>
                        <?php echo $day_name; ?>
                    </label>
                    <div class="time-slots" id="time-slots-<?php echo $day_num; ?>">
                        <div class="row">
                            <div class="col">
                                <label>Start Time</label>
                                <input type="time" name="start_time_<?php echo $day_num; ?>" class="form-control" value="<?php echo isset($availability[$day_num]) ? $availability[$day_num]['start_time'] : ''; ?>">
                            </div>
                            <div class="col">
                                <label>End Time</label>
                                <input type="time" name="end_time_<?php echo $day_num; ?>" class="form-control" value="<?php echo isset($availability[$day_num]) ? $availability[$day_num]['end_time'] : ''; ?>">
                            </div>
                            <div class="col">
                                <label>Slot Duration (minutes)</label>
                                <input type="number" name="slot_duration_<?php echo $day_num; ?>" class="form-control" value="<?php echo isset($availability[$day_num]) ? $availability[$day_num]['slot_duration'] : '60'; ?>" min="15" step="15">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="mb-3">
                <label for="unavailable_dates" class="form-label">Unavailable Dates</label>
                <input type="text" id="unavailable_dates" name="unavailable_dates" class="form-control" value="<?php echo implode(', ', $unavailable_dates); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Save Availability</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#unavailable_dates", {
            mode: "multiple",
            dateFormat: "Y-m-d",
            conjunction: ", "
        });

        document.querySelectorAll('.day-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var dayNum = this.getAttribute('data-day');
                var timeSlots = document.getElementById('time-slots-' + dayNum);
                timeSlots.style.display = this.checked ? 'block' : 'none';
            });

            // Set initial state
            var dayNum = checkbox.getAttribute('data-day');
            var timeSlots = document.getElementById('time-slots-' + dayNum);
            timeSlots.style.display = checkbox.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>
