<?php
/**
    Author: Grady Rueffer
    Student Number: 400579449
    Date: 15-03-2025
    Description: This file contains baseline reusable code accross the web app
    to destroy the session and redirect to the welcome page
    Links to: HoopsDynasty/index.php (Redirects user to welcome page)
 */

// Start the session to access session data
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to welcome page
header("Location: ../index.php");
exit;
?>