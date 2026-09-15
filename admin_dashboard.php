<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT pickup_requests.*, categories.name AS category_name, users.name AS user_name, users.email AS user_email
    FROM pickup_requests
    JOIN categories ON pickup_requests.category_id = categories.id
    JOIN users ON pickup_requests.user_id = users.id
    ORDER BY pickup_requests.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'navbar.php'; ?>
    <body class="app-bg">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="admin-table-wrap">
        <table>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Category</th>
                <th>Description</th>
                <th>Pickup Date</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r["user_name"]) ?></td>
                    <td><?= htmlspecialchars($r["user_email"]) ?></td>
                    <td><?= htmlspecialchars($r["category_name"]) ?></td>
                    <td><?= htmlspecialchars($r["device_description"]) ?></td>
                    <td><?= htmlspecialchars($r["preferred_date"]) ?></td>
                    <td><span class="status-pill status-<?= htmlspecialchars($r["status"]) ?>"><?= htmlspecialchars($r["status"]) ?></span></td>
                    <td>
                        <form method="post" action="update_status.php" class="update-form">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <select name="status">
                                <option value="pending" <?= $r["status"] === "pending" ? "selected" : "" ?>>Pending</option>
                                <option value="scheduled" <?= $r["status"] === "scheduled" ? "selected" : "" ?>>Scheduled</option>
                                <option value="collected" <?= $r["status"] === "collected" ? "selected" : "" ?>>Collected</option>
                                <option value="processing" <?= $r["status"] === "processing" ? "selected" : "" ?>>Processing</option>
                                <option value="recycled" <?= $r["status"] === "recycled" ? "selected" : "" ?>>Recycled</option>
                                <option value="cancelled" <?= $r["status"] === "cancelled" ? "selected" : "" ?>>Cancelled</option>
                            </select>
                            <input type="submit" value="Update">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>