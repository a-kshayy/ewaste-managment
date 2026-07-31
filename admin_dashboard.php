<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin")
    {
        header("location: login.php");
        exit();
    }

    $stmt = $pdo->prepare("select pickup_requests.*, categories.name as category_name, users.name as user_name, users.email as user_email from pickup_requests 
     join categories on pickup_requests.category_id = categories.id 
     join users on pickup_requests.user_id = users.id
     order by pickup_requests.created_at desc");
    
     $stmt->execute();
     $requests = $stmt->fetchAll();
     
     require 'navbar.php';
     ?>
     <link rel="stylesheet" href="style.css">
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
                <td><?=  htmlspecialchars($r["user_name"]) ?></td>
                <td><?=  htmlspecialchars($r["user_email"]) ?></td>
                <td><?=  htmlspecialchars($r["category_name"]) ?></td>
                <td><?=  htmlspecialchars($r["device_description"]) ?></td>
                <td><?=  htmlspecialchars($r["preferred_date"]) ?></td>
                <td><span class="status-pill status-<?=  $r["status"] ?>"?><?=  htmlspecialchars($r["status"]) ?></span></td>
                <td>
                    <form method="post" action="update_status.php" class="update-form">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <select name="status">
                            <option value="Pending" <?= $r["status"] === "Pending" ? "selected" : "" ?>>Pending</option>
                            <option value="scheduled" <?= $r["status"] === "Scheduled" ? "selected" : "" ?>>Scheduled</option>
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
S