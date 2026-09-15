<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <div class="nav-left">
        <a href="index.php" class="active" style="display:flex; align-items:center;">
            <img src="assets/logo.png" alt="EcoTrace" style="height:42px; width:auto;">
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="request.php">Request Pickup</a>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="dashboard.php">My Requests</a>
                <?php if ($_SESSION["role"] === "admin"): ?>
                    <a href="admin_dashboard.php">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="nav-right">
        <?php if (isset($_SESSION["user_id"])): ?>
            <span>Hi, <?= htmlspecialchars($_SESSION["name"]) ?></span>
            <a href="logout.php" class="nav-btn-outline">Log out</a>
        <?php else: ?>
            <a href="login.php" class="nav-btn-outline">Log in</a>
            <a href="register.php" class="nav-btn-filled">Sign up</a>
        <?php endif; ?>
    </div>
</nav>