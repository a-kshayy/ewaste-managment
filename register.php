<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
            <h1>Create an account</h1>
            <?php 
                require "config/db.php";
                $error="";

                if($_SERVER["REQUEST_METHOD"]== "POST")
                    {
                        $name = $_POST["name"];
                        $email = $_POST["email"];
                        $password = $_POST["password"];

                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $email, $hashed]);
                        
                        echo "<p> ACCOUNT CREATED! <a href='login.php'>Login here</a></p>";
                    }
            ?>
            <form method="POST">
                <label for="name">Name:</label><br>
                <input type="text" id="name" name="name" required><br><br>

                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required><br><br>

                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required><br><br>

                <input type="submit" value="Register">
            </form>
        </div>
    </div>
</body>
</html>