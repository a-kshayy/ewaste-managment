<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <h1>Create your account</h1>

            <?php
                require "config/db.php";
                $success = false;

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $name = $_POST["name"];
                    $email = $_POST["email"];
                    $password = $_POST["password"];

                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $email, $hashed]);

                    $success = true;
                }
            ?>

            <?php if ($success): ?>
                <p>Account created! <a href="login.php">Log in here</a></p>
            <?php else: ?>
                <form method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <input type="submit" value="Register">
                </form>
                <p>Already have an account? <a href="login.php">Log in</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>