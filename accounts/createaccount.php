<?php
include "../connect.php";

$error = "";
$registrationSuccess = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password");
    $password2 = filter_input(INPUT_POST, "password2");
    $firstname = filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_SPECIAL_CHARS);
    $lastname = filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);

    // Basic validation
    if (!$username || !$password || !$password2 || !$firstname || !$lastname || !$email) {
        $error = "All fields are required";
    } elseif ($password !== $password2) {
        $error = "Passwords do not match";
    } else {
        // Check if username already exists
        $checkUserStmt = $dbh->prepare("SELECT username FROM hoopsdynastyusers WHERE username = ?");
        $checkUserStmt->execute([$username]);
        
        if ($checkUserStmt->rowCount() > 0) {
            $error = "Username already exists";
        } else {
            try {
                $cmd = "INSERT INTO `hoopsdynastyusers`(`username`, `password`, `firstname`, `lastname`, `accesslevel`, `email`) VALUES (?,?,?,?,0,?)";
                $stmt = $dbh->prepare($cmd);
                $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT), $firstname, $lastname, $email]);
                
                $registrationSuccess = true;
                
                // Redirect to signin page after successful registration
                header("Location: ./signin.php?registration=success");
                exit;
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NBA Basketball Simulator - Create Account</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/passwordvalidation.js" defer></script>
</head>

<body>
    <div class="wrapper">
        <div class="leftContainer">
            <img src="../images/ball.png" alt="Basketball">
            <div>
                <h1>Join Hoops Dynasty</h1>
            </div>
        </div>

        <div class="rightContainer">
            <div class="title">
                <h1>Create Account</h1>
                <p>Sign up to start building your basketball dynasty!</p>
                <?php if ($error): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            </div>

            <div class="loginForm createForm">
                <form method="post" action="createaccount.php" id="accountform">
                    <div class="inputBox">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <input type="password" id="password2" name="password2" placeholder="Confirm Password" required>
                        <input type="text" name="firstname" placeholder="First Name" required>
                        <input type="text" name="lastname" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>

                    <input type="submit" class="btn" value="Create Account">
                </form>
            </div>

            <div class="register">
                <p>Already have an account?</p>
                <a href="signin.php">Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>