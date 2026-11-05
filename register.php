<?php
require_once 'config.php';

// If already logged in, no need to register again
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = array();
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass  = $_POST['password'];
    $pass2 = $_POST['confirm'];

    if ($name == '' || strlen($name) < 3) {
        $errors['name'] = 'Enter your full name (at least 3 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($phone != '' && !preg_match('/^[0-9+\- ]{8,15}$/', $phone)) {
        $errors['phone'] = 'Phone can only contain digits, spaces, + and -.';
    }
    if (strlen($pass) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($pass != $pass2) {
        $errors['confirm'] = 'Both passwords must match.';
    }

    // Only check the database if the form itself is valid so far
    if (count($errors) == 0) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors['email'] = 'An account already exists with this email.';
        }
    }

    if (count($errors) == 0) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $phone, $hash);
        $stmt->execute();

        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['name']    = $name;
        $_SESSION['role']    = 'user';
        $_SESSION['flash']   = array('msg' => 'Welcome to EcoTrace, ' . $name . '.', 'type' => 'success');

        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Create an account';
$active = 'register.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <div class="form-card">
      <h1>Create your account</h1>
      <p class="muted">You need an account to book pickups and track their status.</p>

      <?php if (count($errors) > 0) { ?>
        <div class="error-summary" role="alert" tabindex="-1" autofocus>
          <h2>Please fix the following:</h2>
          <ul>
            <?php foreach ($errors as $field => $message) { ?>
              <li><a href="#<?php echo $field; ?>"><?php echo htmlspecialchars($message); ?></a></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <form method="post" action="register.php">

        <div class="field">
          <label for="name">Full name</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" autocomplete="name" required>
          <?php if (isset($errors['name'])) { ?>
            <span class="field-error"><?php echo htmlspecialchars($errors['name']); ?></span>
          <?php } ?>
        </div>

        <div class="two-col">
          <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" autocomplete="email" required>
            <?php if (isset($errors['email'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['email']); ?></span>
            <?php } ?>
          </div>
          <div class="field">
            <label for="phone">Phone (optional)</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" autocomplete="tel">
            <?php if (isset($errors['phone'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['phone']); ?></span>
            <?php } ?>
          </div>
        </div>

        <div class="two-col">
          <div class="field">
            <label for="password">Password</label>
            <p class="hint">At least 8 characters.</p>
            <input type="password" id="password" name="password" autocomplete="new-password" required>
            <?php if (isset($errors['password'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['password']); ?></span>
            <?php } ?>
          </div>
          <div class="field">
            <label for="confirm">Confirm password</label>
            <p class="hint">&nbsp;</p>
            <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
            <?php if (isset($errors['confirm'])) { ?>
              <span class="field-error"><?php echo htmlspecialchars($errors['confirm']); ?></span>
            <?php } ?>
          </div>
        </div>

        <button class="btn btn-solid" type="submit">Create account</button>
        <p class="muted" style="margin-top:1rem">Already registered? <a href="login.php">Log in instead</a>.</p>
      </form>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
