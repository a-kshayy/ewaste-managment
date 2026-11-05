<?php
// Handles the admin's "Save" button on admin_dashboard.php.
// It never prints HTML — it always redirects back to a page.
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: admin_dashboard.php');
    exit;
}

$request_id = (int)$_POST['request_id'];
$new_status = $_POST['status'];
$note       = trim($_POST['admin_note']);
$back_page  = $_POST['back'];

$center_id = null;
if ($_POST['center_id'] != '') {
    $center_id = (int)$_POST['center_id'];
}

// Only allow redirecting back to our own admin page (nothing outside this site)
if ($back_page != 'admin_dashboard.php') {
    $back_page = 'admin_dashboard.php';
}

$valid_statuses = array('Pending', 'Scheduled', 'Picked Up', 'Recycled', 'Cancelled');

if ($request_id <= 0 || !in_array($new_status, $valid_statuses)) {
    $_SESSION['flash'] = array('msg' => 'Invalid update request.', 'type' => 'error');
    header('Location: admin_dashboard.php');
    exit;
}

// Load the current row so we know if the status actually changed
$stmt = $conn->prepare('SELECT status, tracking_code FROM requests WHERE id = ?');
$stmt->bind_param('i', $request_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if (!$existing) {
    $_SESSION['flash'] = array('msg' => 'That request no longer exists.', 'type' => 'error');
    header('Location: admin_dashboard.php');
    exit;
}

// Update the request
$stmt = $conn->prepare('UPDATE requests SET status = ?, center_id = ?, admin_note = ? WHERE id = ?');
$stmt->bind_param('sisi', $new_status, $center_id, $note, $request_id);
$stmt->execute();

// Record the change in the timeline, only when something actually changed
if ($existing['status'] != $new_status || $note != '') {
    if ($note != '') {
        $line = $note;
    } else {
        $line = 'Status changed to ' . $new_status . '.';
    }
    $by = $_SESSION['name'] . ' (admin)';

    $history = $conn->prepare('INSERT INTO status_history (request_id, status, note, changed_by) VALUES (?, ?, ?, ?)');
    $history->bind_param('isss', $request_id, $new_status, $line, $by);
    $history->execute();
}

$_SESSION['flash'] = array('msg' => $existing['tracking_code'] . ' updated to ' . $new_status . '.', 'type' => 'success');
header('Location: ' . $back_page);
exit;
