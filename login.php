<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $conn->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($pass, $user['password_hash'])) {

        // Give the session a new ID so an old session can't be reused
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['flash']   = array('msg' => 'Welcome back, ' . $user['name'] . '.', 'type' => 'success');

        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;

    } else {
        // Same message either way, so nobody can tell which emails exist
        $error = 'Email or password is incorrect.';
    }
}

$page_title = 'Log in';
$active = 'login.php';
include 'includes/header.php';
?>

<section>
  <div class="wrap">
    <div class="form-card">
      <h1>Log in</h1>

      <?php if ($error != '') { ?>
        <div class="error-summary" role="alert" tabindex="-1" autofocus>
          <h2>We couldn't log you in</h2>
          <p><?php echo htmlspecialchars($error); ?></p>
        </div>
      <?php } ?>

      <form method="post" action="login.php">
        <div class="field">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" autocomplete="email" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-solid" type="submit">Log in</button>
        <p class="muted" style="margin-top:1rem">New here? <a href="register.php">Create an account</a>.</p>
      </form>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
