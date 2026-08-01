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
    <?php  require "navbar.php";?>
    <section class="hero">
      <video class="hero-video" autoplay muted loop playsinline>
        <source src="assets/flowing.mp4" type="video/mp4">
      </video>
      <div class="hero-content">
        <h1>Give Your Old Electronics a Second Life</h1>
        <p>Schedule a free e-waste pickup and recycle responsibly.</p>
        <a href="request.php" class="btn">Schedule a Pickup</a>
      </div>
    </section>

  <h2 class="section-title">What We Accept</h2>

  <div class="card-grid">
    <?php if (!isset($categories)) { $categories = []; } ?>
    <?php foreach ($categories as $cat): ?>
      <div class="card">
        <h3><?= $cat['name'] ?></h3>
        <p><?= $cat['description'] ?></p>
        <span class="tag tag-<?= $cat['hazard_level'] ?>"><?= $cat['hazard_level'] ?> hazard</span>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>