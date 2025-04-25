<!--
    Author: Grady Rueffer
    Student Number: 400579449
    Date: 15-03-2025
    Description: This file is a simple redirect based on whether a user is logged
    in or not. This file will redirect to signin.php when the user is not logged in,
    or menu.php when the user is logged in.
    Links to: accounts/signin.php, menu/index.php
--> 
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