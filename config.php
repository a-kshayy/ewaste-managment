<?php
// config.php — every page includes this file first.
// It starts the session and connects to the database.
// There are no custom functions here — every page writes
// its own htmlspecialchars(), header() and login checks.

session_start();

$conn = new mysqli('localhost', 'root', '', 'ecotrace');

if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

$conn->set_charset('utf8mb4');

// Example category charge per item, in rupees. Change these amounts as needed.
$category_fees = array(
    'Mobile phone' => 20,
    'Laptop / PC' => 80,
    'Monitor / TV' => 100,
    'Printer / Scanner' => 70,
    'Batteries / Power bank' => 15,
    'Cables & chargers' => 10,
    'Kitchen appliance' => 120,
    'Other' => 50
);

// One flat pickup charge is added to each request.
$pickup_charge = 50;
