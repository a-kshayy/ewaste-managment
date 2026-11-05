<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Read the filter values from the page URL.
// To keep this simple, you can filter by status OR search, not both at once.
// If a search term is typed, it takes priority over the status dropdown.
$status_filter = '';
if (isset($_GET['status'])) {
    $status_filter = $_GET['status'];
}
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

$valid_statuses = array('Pending', 'Scheduled', 'Picked Up', 'Recycled', 'Cancelled');

// Decide which filter (if any) is active, and build one WHERE clause for it.
// filter_mode is one of: 'none', 'status', 'search'
$filter_mode = 'none';
$where_sql = '';

if ($search != '') {
    $filter_mode = 'search';
    $where_sql = 'WHERE (requests.tracking_code LIKE ? OR users.name LIKE ? OR users.email LIKE ? OR requests.city LIKE ? OR requests.pincode LIKE ?)';
} elseif (in_array($status_filter, $valid_statuses)) {
    $filter_mode = 'status';
    $where_sql = 'WHERE requests.status = ?';
}

$base_sql = 'FROM requests
             JOIN users ON users.id = requests.user_id
             LEFT JOIN centers ON centers.id = requests.center_id
             ' . $where_sql;

// Export to CSV — this has to run before any HTML is printed
if (isset($_GET['export']) && $_GET['export'] == 'csv') {

    $sql = 'SELECT requests.tracking_code, users.name, users.email, requests.device_type, requests.device_count,
                   requests.est_weight, requests.city, requests.pincode, requests.preferred_date, requests.slot,
                   requests.status, centers.name AS center, requests.created_at ' . $base_sql . ' ORDER BY requests.created_at DESC';

    $stmt = $conn->prepare($sql);
    if ($filter_mode == 'search') {
        $like = '%' . $search . '%';
        $stmt->bind_param('sssss', $like, $like, $like, $like, $like);
    } elseif ($filter_mode == 'status') {
        $stmt->bind_param('s', $status_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ecotrace_requests.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, array('Tracking', 'User', 'Email', 'Device', 'Qty', 'Weight (kg)', 'City', 'PIN',
                         'Preferred date', 'Slot', 'Status', 'Center', 'Created'));
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

// Overall stats
$stats = $conn->query(
    "SELECT COUNT(*) AS total,
            SUM(status = 'Pending') AS pending,
            SUM(status = 'Scheduled') AS scheduled,
            SUM(status = 'Picked Up') AS picked,
            SUM(status = 'Recycled') AS recycled,
            COALESCE(SUM(CASE WHEN status = 'Recycled' THEN est_weight END), 0) AS kg
     FROM requests"
)->fetch_assoc();

$user_count_row = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'user'")->fetch_assoc();
$user_count = $user_count_row['c'];

$unread_row = $conn->query('SELECT COUNT(*) AS c FROM messages WHERE is_read = 0')->fetch_assoc();
$unread = $unread_row['c'];


// Pagination
$per_page = 15;
$page = 1;
if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
}
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $per_page;

$count_sql = 'SELECT COUNT(*) AS c ' . $base_sql;
$stmt = $conn->prepare($count_sql);
if ($filter_mode == 'search') {
    $like = '%' . $search . '%';
    $stmt->bind_param('sssss', $like, $like, $like, $like, $like);
} elseif ($filter_mode == 'status') {
    $stmt->bind_param('s', $status_filter);
}
$stmt->execute();
$total_rows_row = $stmt->get_result()->fetch_assoc();
$total_rows = $total_rows_row['c'];
$total_pages = (int)ceil($total_rows / $per_page);
if ($total_pages < 1) {
    $total_pages = 1;
}

// The list of requests for this page
$list_sql = 'SELECT requests.*, users.name AS user_name, users.email AS user_email, centers.name AS center_name '
          . $base_sql . ' ORDER BY requests.created_at DESC LIMIT ? OFFSET ?';

$stmt = $conn->prepare($list_sql);
if ($filter_mode == 'search') {
    $like = '%' . $search . '%';
    $stmt->bind_param('sssssii', $like, $like, $like, $like, $like, $per_page, $offset);
} elseif ($filter_mode == 'status') {
    $stmt->bind_param('sii', $status_filter, $per_page, $offset);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$rows = $stmt->get_result();

$centers = $conn->query('SELECT id, name FROM centers WHERE is_active = 1 ORDER BY name')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Admin dashboard';
$active = 'admin_dashboard.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <h1>Admin dashboard</h1>
    <p class="muted">Every request across the platform, with live status control.</p>

    <div class="grid grid-4" style="margin:1.5rem 0 2rem">
      <div class="stat-card"><span class="num"><?php echo (int)$stats['total']; ?></span><span class="lbl">Total requests</span></div>
      <div class="stat-card" style="border-left-color:#C77700"><span class="num"><?php echo (int)$stats['pending']; ?></span><span class="lbl">Awaiting action</span></div>
      <div class="stat-card"><span class="num"><?php echo number_format($stats['kg'], 1); ?></span><span class="lbl">kg recycled</span></div>
      <div class="stat-card" style="border-left-color:var(--copper)"><span class="num"><?php echo (int)$user_count; ?></span><span class="lbl">Registered users</span></div>
    </div>

    <?php if ($unread > 0) { ?>
      <p class="flash flash-info"><?php echo (int)$unread; ?> unread enquiry message(s) in the messages table.</p>
    <?php } ?>

    <form method="get" action="admin_dashboard.php" style="margin:1.5rem 0">
      <div class="two-col">
        <div class="field">
          <label for="q">Search</label>
          <input type="search" id="q" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Code, name, email, city or PIN">
        </div>
        <div class="field">
          <label for="status">Filter by status</label>
          <select id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach ($valid_statuses as $one_status) { ?>
              <?php if ($status_filter == $one_status) { ?>
                <option value="<?php echo $one_status; ?>" selected><?php echo $one_status; ?></option>
              <?php } else { ?>
                <option value="<?php echo $one_status; ?>"><?php echo $one_status; ?></option>
              <?php } ?>
            <?php } ?>
          </select>
        </div>
      </div>
      <button class="btn btn-solid" type="submit">Apply</button>
      <a class="btn btn-ghost" href="admin_dashboard.php">Reset</a>
      <a class="btn btn-copper" href="admin_dashboard.php?export=csv&status=<?php echo urlencode($status_filter); ?>&q=<?php echo urlencode($search); ?>">Export CSV</a>
    </form>

    <div class="table-scroll">
      <table>
        <caption><?php echo $total_rows; ?> request(s) &middot; page <?php echo $page; ?> of <?php echo $total_pages; ?></caption>
        <thead>
          <tr>
            <th scope="col">Code</th>
            <th scope="col">User</th>
            <th scope="col">Item</th>
            <th scope="col">Pickup</th>
            <th scope="col">Status</th>
            <th scope="col">Update</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $rows->fetch_assoc()) { ?>
          <?php $rid = (int)$row['id']; ?>

          <?php
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
            <td>
              <span class="code"><?php echo htmlspecialchars($row['tracking_code']); ?></span><br>
              <a href="track.php?code=<?php echo urlencode($row['tracking_code']); ?>" style="font-size:.85rem">timeline</a>
            </td>
            <td>
              <?php echo htmlspecialchars($row['user_name']); ?><br>
              <span class="muted" style="font-size:.85rem"><?php echo htmlspecialchars($row['user_email']); ?></span>
            </td>
            <td>
              <?php echo htmlspecialchars($row['device_type']); ?> &times; <?php echo (int)$row['device_count']; ?><br>
              <span class="muted" style="font-size:.85rem"><?php echo number_format($row['est_weight'], 1); ?> kg</span>
            </td>
            <td>
              <?php echo date('d M Y', strtotime($row['preferred_date'])); ?><br>
              <span class="muted" style="font-size:.85rem"><?php echo htmlspecialchars($row['city']); ?> &ndash; <?php echo htmlspecialchars($row['pincode']); ?></span>
            </td>
            <td><span class="pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
            <td>
              <form method="post" action="update_status.php">
                <input type="hidden" name="request_id" value="<?php echo $rid; ?>">
                <input type="hidden" name="back" value="admin_dashboard.php">

                <label class="visually-hidden" for="st<?php echo $rid; ?>">New status</label>
                <select id="st<?php echo $rid; ?>" name="status" style="min-width:140px">
                  <?php foreach ($valid_statuses as $one_status) { ?>
                    <?php if ($row['status'] == $one_status) { ?>
                      <option value="<?php echo $one_status; ?>" selected><?php echo $one_status; ?></option>
                    <?php } else { ?>
                      <option value="<?php echo $one_status; ?>"><?php echo $one_status; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>

                <label class="visually-hidden" for="ct<?php echo $rid; ?>">Assign recycler</label>
                <select id="ct<?php echo $rid; ?>" name="center_id" style="min-width:140px;margin-top:.35rem">
                  <option value="">No recycler</option>
                  <?php foreach ($centers as $c) { ?>
                    <?php if ($row['center_id'] == $c['id']) { ?>
                      <option value="<?php echo (int)$c['id']; ?>" selected><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php } else { ?>
                      <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>

                <label class="visually-hidden" for="nt<?php echo $rid; ?>">Note for user</label>
                <input type="text" id="nt<?php echo $rid; ?>" name="admin_note" maxlength="255"
                       value="<?php echo htmlspecialchars($row['admin_note']); ?>" placeholder="Note to the user"
                       style="margin-top:.35rem">

                <button class="btn btn-solid btn-sm" type="submit" style="margin-top:.45rem">Save</button>
              </form>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1) { ?>
      <nav aria-label="Pagination" style="margin-top:1.5rem;display:flex;gap:.5rem;flex-wrap:wrap">
        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
          <?php if ($i == $page) { ?>
            <a class="btn btn-solid btn-sm" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&q=<?php echo urlencode($search); ?>" aria-current="page"><?php echo $i; ?></a>
          <?php } else { ?>
            <a class="btn btn-ghost btn-sm" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&q=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
          <?php } ?>
        <?php } ?>
      </nav>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
