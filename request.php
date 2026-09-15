<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require "config/db.php";

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = $_POST["category_id"];
    $description = $_POST["description"];
    $address = $_POST["address"];
    $date = $_POST["preferred_date"];

    $stmt = $pdo->prepare("INSERT INTO pickup_requests (user_id, category_id, device_description, pickup_address, preferred_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION["user_id"], $category_id, $description, $address, $date]);

    $success = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Pickup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require "navbar.php"; ?>
    <body class="app-bg">
    <div class="page-header">
        <h1>Schedule a Pickup</h1>
        <p>Tell us what you'd like collected and when.</p>
    </div>

    <?php if ($success): ?>
        <p class="success-msg">Request submitted!</p>
    <?php endif; ?>

    <form method="post">
        <label>Category:</label>
        <select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Describe your item(s):</label>
        <textarea name="description" required></textarea>

        <label>Pickup address:</label>
        <input type="text" name="address" required>

        <label>Preferred date:</label>
        <input type="date" name="preferred_date" required>

        <button type="submit">Submit Request</button>
    </form>
</body>
</html>