<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Cancel a request (only your own, only while it is still Pending)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_id'])) {

    $request_id = (int)$_POST['cancel_id'];

    $stmt = $conn->prepare("UPDATE requests SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $stmt->bind_param('ii', $request_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {

        $status_text = 'Cancelled';
        $note_text   = 'Cancelled by user';
        $by_text = $_SESSION['name'];

        $history = $conn->prepare('INSERT INTO status_history (request_id, status, note, changed_by) VALUES (?, ?, ?, ?)');
        $history->bind_param('isss', $request_id, $status_text, $note_text, $by_text);
        $history->execute();

        $_SESSION['flash'] = array('msg' => 'Request cancelled.', 'type' => 'info');
    } else {
        $_SESSION['flash'] = array('msg' => 'That request can no longer be cancelled.', 'type' => 'error');
    }

    header('Location: dashboard.php');
    exit;
}

// Summary numbers for this user
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total,
            SUM(status IN ('Pending','Scheduled','Picked Up')) AS active,
            SUM(status = 'Recycled') AS done,
            COALESCE(SUM(CASE WHEN status = 'Recycled' THEN est_weight END), 0) AS kg
     FROM requests WHERE user_id = ?"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare('SELECT email, phone FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

// All of this user's requests.
$stmt = $conn->prepare(
    "SELECT requests.*, centers.name AS center_name
     FROM requests
     LEFT JOIN centers ON centers.id = requests.center_id
     WHERE requests.user_id = ?
     ORDER BY requests.created_at DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$my_requests = $stmt->get_result();

$page_title = 'My dashboard';
$active = 'dashboard.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <h1>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
    <p class="muted">
      <?php echo htmlspecialchars($me['email']); ?>
      <?php if ($me['phone']) { ?>
        &middot; <?php echo htmlspecialchars($me['phone']); ?>
      <?php } ?>
    </p>

    <div class="grid grid-3" style="margin:1.5rem 0 2.5rem">
      <div class="stat-card"><span class="num"><?php echo (int)$summary['total']; ?></span><span class="lbl">Total requests</span></div>
      <div class="stat-card"><span class="num"><?php echo (int)$summary['active']; ?></span><span class="lbl">In progress</span></div>
      <div class="stat-card"><span class="num"><?php echo number_format($summary['kg'], 1); ?></span><span class="lbl">kg recycled</span></div>
    </div>

    <div style="display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:1.5rem">
      <a class="btn btn-solid" href="request.php">New pickup request</a>
      <a class="btn btn-ghost" href="centers.php">Find a drop-off center</a>
    </div>

    <h2>Your requests</h2>

    <?php if ($my_requests->num_rows == 0) { ?>
      <div class="card">
        <p>You haven't booked a pickup yet. <a href="request.php">Book your first one</a> — it takes a minute.</p>
      </div>
    <?php } else { ?>
      <div class="table-scroll">
        <table>
          <caption>All pickup requests on your account, newest first.</caption>
          <thead>
            <tr>
              <th scope="col">Tracking code</th>
              <th scope="col">Item</th>
              <th scope="col">Weight</th>
              <th scope="col">Displayed charges</th>
              <th scope="col">Pickup date</th>
              <th scope="col">Status</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = $my_requests->fetch_assoc()) { ?>

            <?php
            // Pick the CSS class for the status pill
            if ($row['status'] == 'Pending') {
                $pill_class = 'pill-pending';
            } elseif ($row['status'] == 'Scheduled') {
                $pill_class = 'pill-scheduled';
            } elseif ($row['status'] == 'Picked Up') {
                $pill_class = 'pill-picked';
            } elseif ($row['status'] == 'Recycled') {
                $pill_class = 'pill-recycled';
            } else {
                $pill_class = 'pill-cancelled';
            }

            ?>

            <tr>
              <td><span class="code"><?php echo htmlspecialchars($row['tracking_code']); ?></span></td>
              <td><?php echo htmlspecialchars($row['device_type']); ?> &times; <?php echo (int)$row['device_count']; ?></td>
              <td><?php echo number_format($row['est_weight'], 1); ?> kg</td>
              <td>
                &#8377;<?php echo number_format($category_fees[$row['device_type']] * (int)$row['device_count'], 2); ?> category<br>
                <span class="muted">+ &#8377;<?php echo number_format($pickup_charge, 2); ?> pickup</span>
              </td>
              <td>
                <?php echo date('d M Y', strtotime($row['preferred_date'])); ?><br>
                <span class="muted" style="font-size:.85rem"><?php echo htmlspecialchars($row['slot']); ?></span>
              </td>
              <td><span class="pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
              <td>
                <a class="btn btn-ghost btn-sm" href="track.php?code=<?php echo urlencode($row['tracking_code']); ?>">Track</a>

                <?php if ($row['status'] == 'Pending') { ?>
                  <form method="post" action="dashboard.php" style="display:inline">
                    <input type="hidden" name="cancel_id" value="<?php echo (int)$row['id']; ?>">
                    <button class="btn btn-ghost btn-sm" type="submit">Cancel</button>
                  </form>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
