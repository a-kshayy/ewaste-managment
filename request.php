<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = array('msg' => 'Please log in to continue.', 'type' => 'error');
    header('Location: login.php');
    exit;
}

$errors = array();

$device_type    = '';
$device_count   = 1;
$est_weight     = '';
$condition_note = '';
$pickup_address = '';
$city           = '';
$pincode        = '';
$preferred_date = '';
$slot           = 'Morning';

$device_types = array('Mobile phone', 'Laptop / PC', 'Monitor / TV', 'Printer / Scanner',
                       'Batteries / Power bank', 'Cables & chargers', 'Kitchen appliance', 'Other');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $device_type    = trim($_POST['device_type']);
    $device_count   = trim($_POST['device_count']);
    $est_weight     = trim($_POST['est_weight']);
    $condition_note = trim($_POST['condition_note']);
    $pickup_address = trim($_POST['pickup_address']);
    $city           = trim($_POST['city']);
    $pincode        = trim($_POST['pincode']);
    $preferred_date = trim($_POST['preferred_date']);
    $slot           = trim($_POST['slot']);

    if (!in_array($device_type, $device_types)) {
        $errors['device_type'] = 'Choose the kind of device.';
    }
    if (!ctype_digit($device_count) || $device_count < 1 || $device_count > 100) {
        $errors['device_count'] = 'Enter how many items (1-100).';
    }
    if (!is_numeric($est_weight) || $est_weight <= 0 || $est_weight > 500) {
        $errors['est_weight'] = 'Enter an approximate weight in kg (0.1 - 500).';
    }
    if (strlen($pickup_address) < 8) {
        $errors['pickup_address'] = 'Enter the full pickup address.';
    }
    if ($city == '') {
        $errors['city'] = 'Enter your city or town.';
    }
    if (!preg_match('/^[0-9]{6}$/', $pincode)) {
        $errors['pincode'] = 'Enter a 6-digit PIN code.';
    }
    if ($preferred_date == '' || $preferred_date < date('Y-m-d')) {
        $errors['preferred_date'] = 'Pick today or a future date.';
    }
    if ($slot != 'Morning' && $slot != 'Afternoon' && $slot != 'Evening') {
        $errors['slot'] = 'Choose a time slot.';
    }

    // Handle the optional photo upload
    $photo_name = null;
    if ($_FILES['photo']['name'] != '') {

        $upload_error = $_FILES['photo']['error'];
        $upload_size  = $_FILES['photo']['size'];
        $upload_tmp   = $_FILES['photo']['tmp_name'];

        if ($upload_error != UPLOAD_ERR_OK) {
            $errors['photo'] = 'The photo did not upload. Try a smaller file.';
        } elseif ($upload_size > 2 * 1024 * 1024) {
            $errors['photo'] = 'Photo must be under 2 MB.';
        } else {
            $file_info = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $file_info->file($upload_tmp);

            if ($mime_type == 'image/jpeg') {
                $photo_name = 'ewaste_' . time() . '.jpg';
            } elseif ($mime_type == 'image/png') {
                $photo_name = 'ewaste_' . time() . '.png';
            } elseif ($mime_type == 'image/webp') {
                $photo_name = 'ewaste_' . time() . '.webp';
            } else {
                $errors['photo'] = 'Only JPG, PNG or WebP images are allowed.';
            }
        }
    }

    if (count($errors) == 0) {

        if ($photo_name != null) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0775, true);
            }
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_name);
        }

        // Build a tracking code like ECO-4F2A9C and make sure it's not already used
        do {
            $tracking_code = 'ECO-' . strtoupper(substr(md5(uniqid()), 0, 6));
            $check = $conn->prepare('SELECT id FROM requests WHERE tracking_code = ?');
            $check->bind_param('s', $tracking_code);
            $check->execute();
            $already_used = $check->get_result()->num_rows > 0;
        } while ($already_used);

        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare(
            'INSERT INTO requests
             (tracking_code, user_id, device_type, device_count, est_weight, condition_note,
              pickup_address, city, pincode, preferred_date, slot, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sisidsssssss',
            $tracking_code, $user_id, $device_type, $device_count, $est_weight, $condition_note,
            $pickup_address, $city, $pincode, $preferred_date, $slot, $photo_name
        );
        $stmt->execute();
        $new_request_id = $stmt->insert_id;

        // Write the first line of the status history for this request
        $status_text = 'Pending';
        $note_text   = 'Request submitted by user';
        $by_text     = $_SESSION['name'];
        $history = $conn->prepare('INSERT INTO status_history (request_id, status, note, changed_by) VALUES (?, ?, ?, ?)');
        $history->bind_param('isss', $new_request_id, $status_text, $note_text, $by_text);
        $history->execute();

        $category_total = $category_fees[$device_type] * (int)$device_count;
        $total_charge = $category_total + $pickup_charge;
        $_SESSION['flash'] = array('msg' => 'Request saved. Your tracking code is ' . $tracking_code . '. Displayed charges: Rs. ' . number_format($category_total, 2) . ' category charge + Rs. ' . number_format($pickup_charge, 2) . ' pickup charge = Rs. ' . number_format($total_charge, 2) . '. No online payment is required.', 'type' => 'success');
        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Request a pickup';
$active = 'request.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <div class="form-card form-wide">
      <h1>Request a pickup</h1>
      <p class="muted">Fill this in once. You'll get a tracking code as soon as it's saved.</p>
      <p class="muted">The category charge is per item. One flat pickup charge is added per request. No online payment is required.</p>
      <div class="table-scroll">
        <table>
          <caption>Charges in rupees</caption>
          <thead><tr><th scope="col">Device category</th><th scope="col">Charge per item</th></tr></thead>
          <tbody>
            <?php foreach ($category_fees as $device_name => $fee_amount) { ?>
              <tr><td><?php echo htmlspecialchars($device_name); ?></td><td>&#8377;<?php echo number_format($fee_amount, 2); ?></td></tr>
            <?php } ?>
            <tr><th scope="row">Pickup charge (per request)</th><td>&#8377;<?php echo number_format($pickup_charge, 2); ?></td></tr>
          </tbody>
        </table>
      </div>

      <?php if (count($errors) > 0) { ?>
        <div class="error-summary" role="alert" tabindex="-1" autofocus>
          <h2>Please check the following:</h2>
          <ul>
            <?php foreach ($errors as $field => $message) { ?>
              <li><a href="#<?php echo $field; ?>"><?php echo htmlspecialchars($message); ?></a></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <form method="post" action="request.php" enctype="multipart/form-data">

        <fieldset>
          <legend>What are we collecting?</legend>

          <div class="two-col">
            <div class="field">
              <label for="device_type">Device type</label>
              <select id="device_type" name="device_type" required>
                <option value="">-- Select --</option>
                <?php foreach ($device_types as $one_type) { ?>
                  <?php if ($device_type == $one_type) { ?>
                    <option value="<?php echo htmlspecialchars($one_type); ?>" selected><?php echo htmlspecialchars($one_type); ?></option>
                  <?php } else { ?>
                    <option value="<?php echo htmlspecialchars($one_type); ?>"><?php echo htmlspecialchars($one_type); ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
              <?php if (isset($errors['device_type'])) { ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['device_type']); ?></span>
              <?php } ?>
            </div>

            <div class="field">
              <label for="device_count">How many items?</label>
              <input type="number" id="device_count" name="device_count" min="1" max="100"
                     value="<?php echo htmlspecialchars($device_count); ?>" required>
              <?php if (isset($errors['device_count'])) { ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['device_count']); ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="field">
            <label for="est_weight">Approximate total weight (kg)</label>
            <p class="hint">A rough guess is fine — a laptop is about 2 kg.</p>
            <input type="number" step="0.1" min="0.1" max="500" id="est_weight" name="est_weight"
                   value="<?php echo htmlspecialchars($est_weight); ?>" required>
            <?php if (isset($errors['est_weight'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['est_weight']); ?></span>
            <?php } ?>
          </div>

          <div class="field">
            <label for="condition_note">Condition / notes (optional)</label>
            <textarea id="condition_note" name="condition_note"><?php echo htmlspecialchars($condition_note); ?></textarea>
          </div>

          <div class="field">
            <label for="photo">Photo of the items (optional)</label>
            <p class="hint">JPG, PNG or WebP, up to 2 MB. Helps us send the right vehicle.</p>
            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp">
            <?php if (isset($errors['photo'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['photo']); ?></span>
            <?php } ?>
          </div>
        </fieldset>

        <fieldset>
          <legend>Where and when?</legend>

          <div class="field">
            <label for="pickup_address">Pickup address</label>
            <textarea id="pickup_address" name="pickup_address" autocomplete="street-address" required><?php echo htmlspecialchars($pickup_address); ?></textarea>
            <?php if (isset($errors['pickup_address'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['pickup_address']); ?></span>
            <?php } ?>
          </div>

          <div class="two-col">
            <div class="field">
              <label for="city">City / town</label>
              <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" autocomplete="address-level2" required>
              <?php if (isset($errors['city'])) { ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['city']); ?></span>
              <?php } ?>
            </div>
            <div class="field">
              <label for="pincode">PIN code</label>
              <input type="text" id="pincode" name="pincode" inputmode="numeric" maxlength="6"
                     value="<?php echo htmlspecialchars($pincode); ?>" autocomplete="postal-code" required>
              <?php if (isset($errors['pincode'])) { ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['pincode']); ?></span>
              <?php } ?>
            </div>
          </div>

          <div class="two-col">
            <div class="field">
              <label for="preferred_date">Preferred date</label>
              <input type="date" id="preferred_date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>"
                     value="<?php echo htmlspecialchars($preferred_date); ?>" required>
              <?php if (isset($errors['preferred_date'])) { ?>
                <span class="field-error"><?php echo htmlspecialchars($errors['preferred_date']); ?></span>
              <?php } ?>
            </div>
            <div class="field">
              <label for="slot">Time slot</label>
              <select id="slot" name="slot" required>
                <?php foreach (array('Morning', 'Afternoon', 'Evening') as $one_slot) { ?>
                  <?php if ($slot == $one_slot) { ?>
                    <option value="<?php echo $one_slot; ?>" selected><?php echo $one_slot; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $one_slot; ?>"><?php echo $one_slot; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
        </fieldset>

        <button class="btn btn-solid" type="submit">Submit request</button>
      </form>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
