<?php
session_start();
require "config/db.php";

$error= "";

if($_SERVER["REQUEST_METHOD"]=="POST")
    {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $stmt =$pdo-> prepare("SELECT * FROM users WHERE email =?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user["password_hash"]))
            {
                $_SESSION["user_id"]= $user["id"];
                $_SESSION["name"]= $user["name"];
                $_SESSION["role"]= $user["role"];

                header("Location: index.php");
                exit;
            }
            else{
                $error = "Incorrect email or password.";
                }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <video autoplay muted loop playsinline class="auth-video">
        <source src="assets/login_back.mp4" type="video/mp4">
    </video>
    <div class="auth-overlay"></div>
    <div class="auth-content">
        <?php require "navbar.php"; ?>
        <div class="auth-card">
            <h1>Login</h1>
            <?php if($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required><br><br>

                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required><br><br>

                <input type="submit" value="Login">
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>