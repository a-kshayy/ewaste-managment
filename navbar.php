<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
  <a href="index.php" style="display:flex; align-items:center;">
    <img src="assets/logo.png" alt="EcoTrace" align="right" style="height: 50px; width:auto;">
  
</a>
  <a href="index.php">Home</a>
  <a href="request.php">Request Pickup</a>

  <?php if (isset($_SESSION["user_id"])): ?>
    <a href="dashboard.php">My Requests</a>
    <span>Hi, <?= $_SESSION["name"] ?></span>
    <a href="logout.php">Log out</a>
  <?php else: ?>
    <a href="login.php">Log in</a>
    <a href="register.php">Sign up</a>
  <?php endif; ?>
</nav>