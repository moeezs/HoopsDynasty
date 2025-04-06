<?php
// Start the session to check if user is logged in
session_start();

// If user is not logged in, redirect to signin page
if (!isset($_SESSION["userid"])) {
    header("Location: ./accounts/signin.php");
} else {
    // If user is logged in, redirect to the main menu page
    header("Location: ./menu/index.php");
}
exit;
?>