<?php
session_start();
include "../connect.php";

// If user is already logged in, redirect to menu
if (isset($_SESSION["userid"])) {
    header("Location: ../menu");
    exit;
}

// Check for login error from a previous attempt
$loginError = false;
if (isset($_GET['error']) && $_GET['error'] == 'invalid') {
    $loginError = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoops Dynasty Basketball Simulator - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <div class="leftContainer">
            <img src="../images/ball.png" alt="Basketball">
            <div>
                <h1>Hoops Dynasty</h1>
            </div>
        </div>

        <div class="rightContainer">
            <div class="title">
                <h1>Login</h1>
                <p>Welcome Back! Please login to your account.</p>
                <?php if ($loginError): ?>
                <p class="error-message">Invalid username or password</p>
                <?php endif; ?>
            </div>

            <div class="loginForm">
                <form method="post" action="../menu/index.php">
                    <div class="inputBox">
                        <input type="text" name="userid" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>

                    <div class="rememberForgot">
                        <label><input type="checkbox"> Remember Me</label>
                    </div>

                    <input type="submit" class="btn" value="Login">
                </form>
            </div>

            <div class="register">
                <p>New user?</p>
                <a href="createaccount.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>