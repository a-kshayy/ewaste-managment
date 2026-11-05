<?php
require_once 'config.php';

$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

if ($search != '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        'SELECT * FROM centers
         WHERE is_active = 1 AND (city LIKE ? OR pincode LIKE ? OR name LIKE ?)
         ORDER BY city, name'
    );
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $stmt = $conn->prepare('SELECT * FROM centers WHERE is_active = 1 ORDER BY city, name');
}
$stmt->execute();
$centers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Collection centers';
$active = 'centers.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <div class="section-head">
      <h1>Drop-off centers</h1>
      <p class="muted">Prefer to drop items yourself? These partners accept e-waste during working hours.</p>
    </div>

    <form method="get" action="centers.php" role="search" style="max-width:520px;margin-bottom:2rem">
      <div class="field">
        <label for="q">Search by city, PIN code or name</label>
        <input type="search" id="q" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="e.g. Kochi or 682030">
      </div>
      <button class="btn btn-solid" type="submit">Search</button>
      <?php if ($search != '') { ?>
        <a class="btn btn-ghost" href="centers.php">Clear</a>
      <?php } ?>
    </form>

    <p role="status" class="muted">
      <?php echo count($centers); ?> center(s) found
      <?php if ($search != '') { ?>
        for "<?php echo htmlspecialchars($search); ?>"
      <?php } ?>.
    </p>

    <?php if (count($centers) > 0) { ?>
      <div class="grid grid-3" style="margin-top:1rem">
        <?php foreach ($centers as $c) { ?>
          <article class="card">
            <h2 style="font-size:1.15rem"><?php echo htmlspecialchars($c['name']); ?></h2>
            <p class="muted" style="margin-bottom:.6rem">
              <?php echo htmlspecialchars($c['address']); ?><br>
              <?php echo htmlspecialchars($c['city']); ?> &ndash; <?php echo htmlspecialchars($c['pincode']); ?>
            </p>
            <p style="margin-bottom:.6rem"><strong>Open:</strong> <?php echo htmlspecialchars($c['timings']); ?></p>
            <p style="margin-bottom:.8rem"><strong>Accepts:</strong> <?php echo htmlspecialchars($c['accepts']); ?></p>
            <?php if ($c['phone']) { ?>
              <p style="margin:0"><a href="tel:<?php echo htmlspecialchars($c['phone']); ?>">Call <?php echo htmlspecialchars($c['phone']); ?></a></p>
            <?php } ?>
          </article>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="card"><p>No center matched that search. Try a nearby city, or <a href="request.php">book a doorstep pickup</a> instead.</p></div>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
