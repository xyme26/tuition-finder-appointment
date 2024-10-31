<?php
session_start();
include 'connection.php';
$current_page = 'profile';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT tc.* FROM tuition_centers tc
          JOIN favorites f ON tc.id = f.tuition_center_id
          WHERE f.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorite_centers = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Tuition Centers</title>
    
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
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <br><br><br><br>

    <!-- Main Content -->
     <!-- Profile Container -->
    <div class="profile-container">
        <!-- Left navigation with custom design -->
        <div class="left-nav">
            <ul>
                <li><a href="profile.php">Personal Details</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_history.php">Review History</a></li>
                <li><a href="favorite_history.php" class="active">Favorite History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="profile-details">
        <h2>Your Favorite Tuition Centers</h2>
        <div class="row">
            <?php foreach ($favorite_centers as $center): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($center['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($center['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($center['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($center['description']); ?></p>
                            <a href="tuition_details.php?id=<?php echo $center['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    </div>
    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>
