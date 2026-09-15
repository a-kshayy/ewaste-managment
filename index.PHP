<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ewaste-portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/logo.png">
</head>
<body>
    <?php
    require "config/db.php";
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
    ?>
    <?php require "navbar.php"; ?>

    <div class="hero-new">
        <div class="hero-new-text">
            <h1>Give Your Old Electronics a Second Life</h1>
            <p>Schedule a free e-waste pickup and recycle responsibly — simple, trusted, and good for the planet.</p>
            <div class="hero-btn-row">
                <a href="request.php" class="btn-filled">Schedule a Pickup</a>
                <a href="#categories" class="btn-outline">What We Accept</a>
            </div>
        </div>
    </div>

    <h2 class="section-title" id="categories" style="text-align:center; margin-top:50px;">What We Accept</h2>

    <div class="card-grid">
        <?php if (!isset($categories)) { $categories = []; } ?>
        <?php foreach ($categories as $cat): ?>
            <div class="card">
                <h3><?= htmlspecialchars($cat['name']) ?></h3>
                <p><?= htmlspecialchars($cat['description']) ?></p>
                <span class="tag tag-<?= htmlspecialchars($cat['hazard_level']) ?>"><?= htmlspecialchars($cat['hazard_level']) ?> hazard</span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>