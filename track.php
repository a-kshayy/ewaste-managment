<?php
require_once 'config.php';

$request = null;
$history = array();
$notfound = false;
$code = '';

if (isset($_GET['code'])) {
    $code = trim($_GET['code']);
}

if ($code != '') {

    $stmt = $conn->prepare(
        'SELECT requests.*, users.name AS owner, centers.name AS center_name
         FROM requests
         JOIN users ON users.id = requests.user_id
         LEFT JOIN centers ON centers.id = requests.center_id
         WHERE requests.tracking_code = ?'
    );
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if ($request) {
        $stmt = $conn->prepare('SELECT status, note, changed_by, changed_at FROM status_history WHERE request_id = ? ORDER BY changed_at DESC, id DESC');
        $stmt->bind_param('i', $request['id']);
        $stmt->execute();
        $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $notfound = true;
    }
}

// Pick the CSS class for this status pill
$pill_class = 'pill-pending';
if ($request) {
    if ($request['status'] == 'Pending') {
        $pill_class = 'pill-pending';
    } elseif ($request['status'] == 'Scheduled') {
        $pill_class = 'pill-scheduled';
    } elseif ($request['status'] == 'Picked Up') {
        $pill_class = 'pill-picked';
    } elseif ($request['status'] == 'Recycled') {
        $pill_class = 'pill-recycled';
    } else {
        $pill_class = 'pill-cancelled';
    }
}

$page_title = 'Track a request';
$active = 'track.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <div class="form-card form-wide">
      <h1>Track your pickup</h1>
      <p class="muted">Enter the code you received when you booked, for example ECO-4F2A9C.</p>

      <form method="get" action="track.php">
        <div class="field">
          <label for="code">Tracking code</label>
          <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($code); ?>" placeholder="ECO-XXXXXX" required>
        </div>
        <button class="btn btn-solid" type="submit">Track</button>
      </form>

      <?php if ($notfound) { ?>
        <p class="error" role="alert">
          No request found with that code. Check for typos, or <a href="dashboard.php">open your dashboard</a> to see your codes.
        </p>
      <?php } ?>

      <?php if ($request) { ?>
        <hr style="border:none;border-top:1px solid var(--line);margin:2rem 0">

        <h2>Request <span class="code"><?php echo htmlspecialchars($request['tracking_code']); ?></span></h2>
        <p>
          <span class="pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($request['status']); ?></span>
          <span class="muted">&nbsp;last updated <?php echo date('d M Y, g:i a', strtotime($request['updated_at'])); ?></span>
        </p>

        <div class="grid grid-2" style="margin-top:1.2rem">
          <div class="card">
            <h3>Item details</h3>
            <p class="muted" style="margin:0">
              <?php echo htmlspecialchars($request['device_type']); ?> &times; <?php echo (int)$request['device_count']; ?><br>
              Approx. <?php echo number_format($request['est_weight'], 1); ?> kg<br>
              Booked by <?php echo htmlspecialchars($request['owner']); ?> on <?php echo date('d M Y', strtotime($request['created_at'])); ?><br>
            </p>
          </div>
          <div class="card">
            <h3>Displayed charges</h3>
            <p class="muted" style="margin:0">
              Category charge: &#8377;<?php echo number_format($category_fees[$request['device_type']] * (int)$request['device_count'], 2); ?><br>
              Pickup charge: &#8377;<?php echo number_format($pickup_charge, 2); ?><br>
              Total: &#8377;<?php echo number_format(($category_fees[$request['device_type']] * (int)$request['device_count']) + $pickup_charge, 2); ?>
            </p>
          </div>
          <div class="card">
            <h3>Collection</h3>
            <p class="muted" style="margin:0">
              <?php echo date('d M Y', strtotime($request['preferred_date'])); ?>, <?php echo htmlspecialchars($request['slot']); ?> slot<br>
              <?php echo htmlspecialchars($request['city']); ?> &ndash; <?php echo htmlspecialchars($request['pincode']); ?><br>
              <?php if ($request['center_name']) { ?>
                Routed to <?php echo htmlspecialchars($request['center_name']); ?>
              <?php } else { ?>
                Recycler not assigned yet
              <?php } ?>
            </p>
          </div>
        </div>

        <?php if ($request['admin_note']) { ?>
          <p class="flash flash-info" style="margin-top:1.2rem"><?php echo htmlspecialchars($request['admin_note']); ?></p>
        <?php } ?>

        <h3 style="margin-top:2rem">Progress</h3>
        <ol class="timeline">
          <?php foreach ($history as $h) { ?>
            <li>
              <strong><?php echo htmlspecialchars($h['status']); ?></strong><br>
              <?php if ($h['note']) { ?>
                <span class="muted"><?php echo htmlspecialchars($h['note']); ?></span><br>
              <?php } ?>
              <time datetime="<?php echo htmlspecialchars($h['changed_at']); ?>">
                <?php echo date('d M Y, g:i a', strtotime($h['changed_at'])); ?> &middot; <?php echo htmlspecialchars($h['changed_by']); ?>
              </time>
            </li>
          <?php } ?>
        </ol>
      <?php } ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
