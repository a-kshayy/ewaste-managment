<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") 
    {
        header("Location: login.php");
        exit();
    }

if ($_SERVER["REQUEST_METHOD"] === "POST")
{

    $id = $_POST["id"];
    $status = $_POST["status"];

    $stmt = $pdo->prepare("UPDATE pickup_requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: admin_dashboard.php");
    exit();
}