<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ewaste-portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    require "config/db.php";
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
    ?>
    <?php  require "navbar.php";?>
    <section class="hero">
    <h1>Give Your Old Electronics a Second Life</h1>
    <p>Schedule a free e-waste pickup and recycle responsibly.</p>
    <a href="request.php" class="btn">Schedule a Pickup</a>
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