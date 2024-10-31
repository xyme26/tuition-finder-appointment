<?php
// logout.php
session_start();
session_unset();
session_destroy();

// Clear "Remember Me" cookie if exists
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/"); // Expire cookie
}

// Redirect to login page
header("Location: login.php");
exit();
?>
