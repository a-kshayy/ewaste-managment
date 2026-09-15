<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require "config/db.php";

$stmt = $pdo->prepare("
    SELECT pickup_requests.*, categories.name AS category_name
    FROM pickup_requests
    JOIN categories ON pickup_requests.category_id = categories.id
    WHERE pickup_requests.user_id = ?
    ORDER BY pickup_requests.created_at DESC
");
$stmt->execute([$_SESSION["user_id"]]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Requests</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require "navbar.php"; ?>
    <body class="app-bg">

    <div class="page-header">
        <h1>My Pickup Requests</h1>
    </div>
    <div class="page-header-links">
        <a href="request.php">+ New Request</a>
    </div>

    <?php if (empty($requests)): ?>
        <p class="empty-state">You haven't made any requests yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Pickup Date</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['category_name']) ?></td>
                        <td><?= htmlspecialchars($r['device_description']) ?></td>
                        <td><?= htmlspecialchars($r['preferred_date']) ?></td>
                        <td><span class="status-pill status-<?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>