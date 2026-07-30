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
  <h1>My Pickup Requests</h1>
  <p><a href="request.php">+ New Request</a></p>

  <?php if (empty($requests)): ?>
    <p>You haven't made any requests yet.</p>
  <?php else: ?>
    <table border="1" cellpadding="8">
      <tr>
        <th>Category</th>
        <th>Description</th>
        <th>Pickup Date</th>
        <th>Status</th>
      </tr>
      <?php foreach ($requests as $r): ?>
        <tr>
          <td><?= $r['category_name'] ?></td>
          <td><?= $r['device_description'] ?></td>
          <td><?= $r['preferred_date'] ?></td>
          <td><?= $r['status'] ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
</html>