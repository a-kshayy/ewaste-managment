<?php
require_once 'config.php';

// Get the numbers shown on the home page
$result = $conn->query(
    "SELECT COUNT(*) AS reqs,
            COALESCE(SUM(CASE WHEN status='Recycled' THEN est_weight END),0) AS kg,
            (SELECT COUNT(*) FROM users WHERE role='user') AS members
     FROM requests"
);
$totals = $result->fetch_assoc();

// Handle the contact form
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $body  = trim($_POST['body']);

    if ($name == '' || $body == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please fill in your name, a valid email and a message.';
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (name, email, body) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $body);
        $stmt->execute();
        $success = 'Thanks — your message reached us. We usually reply within a day.';
    }
}

$page_title = 'Responsible e-waste pickup';
$active = 'index.php';
include 'includes/header.php';
?>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <p class="eyebrow">Doorstep collection &middot; Certified recycling</p>
      <h1>Old devices deserve a <span style="color:var(--copper-txt)">better ending</span>.</h1>
      <p class="lead">
        Book a pickup for your dead phones, laptops and cables. We hand them to
        authorised recyclers, and you can follow every step with a tracking code —
        no guessing where your data or your metal ended up.
      </p>
      <div class="hero-actions">
        <a class="btn btn-solid" href="request.php">Request a pickup</a>
        <a class="btn btn-ghost" href="track.php">Track a request</a>
      </div>
    </div>

    <div class="hero-card">
      <h2 style="font-size:1.15rem">Our impact so far</h2>
      <div class="stat-row">
        <div>
          <span class="stat-num"><?php echo (int)$totals['reqs']; ?></span>
          <span class="stat-label">pickups booked</span>
        </div>
        <div>
          <span class="stat-num"><?php echo number_format($totals['kg'], 1); ?></span>
          <span class="stat-label">kg recycled</span>
        </div>
        <div>
          <span class="stat-num"><?php echo (int)$totals['members']; ?></span>
          <span class="stat-label">members</span>
        </div>
      </div>
      <p class="muted" style="margin:1rem 0 0;font-size:.9rem">
        Numbers update live from our records.
      </p>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head">
      <h2>How it works</h2>
      <p class="muted">Four steps, no paperwork. Review the category charge and flat pickup charge before submitting. No online payment is collected.</p>
    </div>
    <div class="grid grid-4">
      <article class="card">
        <p class="step-num">STEP 01</p>
        <h3>Tell us what you have</h3>
        <p class="muted">Pick the device type, rough weight and your address.</p>
      </article>
      <article class="card">
        <p class="step-num">STEP 02</p>
        <h3>Review the charges</h3>
        <p class="muted">Category and pickup charges are shown with your request. No online payment is needed.</p>
      </article>
      <article class="card">
        <p class="step-num">STEP 03</p>
        <h3>Doorstep collection</h3>
        <p class="muted">We confirm a date, then our partner collects and logs the weight.</p>
      </article>
      <article class="card">
        <p class="step-num">STEP 04</p>
        <h3>Recycled</h3>
        <p class="muted">Status turns Recycled once your device reaches a certified recycler.</p>
      </article>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head">
      <h2>Charges</h2>
      <p class="muted">The category charge is per item. Add one flat pickup charge per request. These amounts are displayed for information only.</p>
    </div>
    <div class="table-scroll">
      <table>
        <caption>EcoTrace project charges</caption>
        <thead><tr><th scope="col">Device category</th><th scope="col">Charge per item</th></tr></thead>
        <tbody>
          <?php foreach ($category_fees as $device_name => $fee_amount) { ?>
            <tr><td><?php echo htmlspecialchars($device_name); ?></td><td>&#8377;<?php echo number_format($fee_amount, 2); ?></td></tr>
          <?php } ?>
          <tr><th scope="row">Pickup charge (per request)</th><td>&#8377;<?php echo number_format($pickup_charge, 2); ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section style="background:var(--mint)">
  <div class="wrap">
    <div class="section-head">
      <h2>What we accept</h2>
      <p class="muted">If it has a plug, a battery or a circuit board, it probably belongs here.</p>
    </div>
    <div class="grid grid-3">
      <article class="card">
        <div class="card-icon" aria-hidden="true">&#128241;</div>
        <h3>Phones &amp; tablets</h3>
        <p class="muted">Dead or working. We wipe storage before dismantling.</p>
      </article>
      <article class="card">
        <div class="card-icon" aria-hidden="true">&#128187;</div>
        <h3>Laptops &amp; PCs</h3>
        <p class="muted">Monitors, keyboards, CPUs, printers and their cables.</p>
      </article>
      <article class="card">
        <div class="card-icon" aria-hidden="true">&#128267;</div>
        <h3>Batteries &amp; chargers</h3>
        <p class="muted">Lithium cells and power banks — never put these in normal bins.</p>
      </article>
    </div>
  </div>
</section>

<section>
  <div class="wrap grid grid-2">
    <div>
      <h2>Why it matters</h2>
      <p>
        A single smartphone holds lead, mercury and cadmium. Dumped in a landfill
        those metals reach groundwater; burned in the open, they reach lungs.
        Recycled properly, the same phone gives back gold, copper and rare earths
        that would otherwise be mined again.
      </p>
      <p><a class="btn btn-copper" href="awareness.php">Read the full guide</a></p>
    </div>
    <div class="panel">
      <h2 style="font-size:1.3rem">Track every request</h2>
      <p class="muted">
        Create a free account to see every pickup you've booked, its live status,
        and the tracking code for each one, all in one dashboard.
      </p>
      <p><a class="btn btn-ghost" href="register.php">Create a free account</a></p>
    </div>
  </div>
</section>

<section id="contact">
  <div class="wrap">
    <div class="form-card">
      <h2>Have a question?</h2>
      <p class="muted">Bulk pickup for an office or hostel? Tell us the details.</p>

      <?php if ($error != '') { ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
      <?php } ?>

      <?php if ($success != '') { ?>
        <p class="flash flash-success"><?php echo htmlspecialchars($success); ?></p>
      <?php } ?>

      <form method="post" action="index.php#contact">
        <input type="hidden" name="contact" value="1">
        <div class="two-col">
          <div class="field">
            <label for="c_name">Your name</label>
            <input type="text" id="c_name" name="name" required autocomplete="name">
          </div>
          <div class="field">
            <label for="c_email">Email address</label>
            <input type="email" id="c_email" name="email" required autocomplete="email">
          </div>
        </div>
        <div class="field">
          <label for="c_body">Message</label>
          <textarea id="c_body" name="body" required></textarea>
        </div>
        <button class="btn btn-solid" type="submit">Send message</button>
      </form>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
