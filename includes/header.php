<?php
// This file is included at the top of every page's HTML.
// The page itself should have already set $page_title and $active
// before including this file (see index.php for an example).

if (!isset($page_title)) {
    $page_title = 'EcoTrace';
}
if (!isset($active)) {
    $active = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="EcoTrace — book an e-waste pickup and track your device to a certified recycler.">
<title><?php echo htmlspecialchars($page_title); ?> - EcoTrace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>

<a class="skip-link" href="#main">Skip to main content</a>

<header class="site-header">
  <div class="wrap header-inner">

    <a class="brand" href="index.php">
      <svg class="brand-mark" viewBox="0 0 48 48" width="40" height="40" aria-hidden="true" focusable="false">
        <circle cx="24" cy="24" r="21" fill="none" stroke="#1B3A2F" stroke-width="3"
                stroke-linecap="round" stroke-dasharray="88 44" transform="rotate(-40 24 24)"/>
        <path d="M34 8 l6 4 -6 5 z" fill="#B87333"/>
        <path d="M24 33c-7 0-11-5-11-11 8 0 12 4 12 11 0-8 4-12 11-12 0 7-4 12-12 12z" fill="#4C7A5D"/>
      </svg>
      <span class="brand-text"><span class="brand-eco">Eco</span><span class="brand-trace">Trace</span></span>
    </a>

    <input type="checkbox" id="navToggle" class="nav-toggle-input">
    <label for="navToggle" class="nav-toggle">
      <span class="bars" aria-hidden="true"></span> Menu
    </label>

    <nav id="mainNav" class="main-nav" aria-label="Main navigation">
      <ul>
        <li>
          <?php if ($active == 'index.php') { ?>
            <a href="index.php" aria-current="page">Home</a>
          <?php } else { ?>
            <a href="index.php">Home</a>
          <?php } ?>
        </li>
        <li>
          <?php if ($active == 'request.php') { ?>
            <a href="request.php" aria-current="page">Request Pickup</a>
          <?php } else { ?>
            <a href="request.php">Request Pickup</a>
          <?php } ?>
        </li>
        <li>
          <?php if ($active == 'track.php') { ?>
            <a href="track.php" aria-current="page">Track</a>
          <?php } else { ?>
            <a href="track.php">Track</a>
          <?php } ?>
        </li>
        <li>
          <?php if ($active == 'centers.php') { ?>
            <a href="centers.php" aria-current="page">Centers</a>
          <?php } else { ?>
            <a href="centers.php">Centers</a>
          <?php } ?>
        </li>
        <li>
          <?php if ($active == 'awareness.php') { ?>
            <a href="awareness.php" aria-current="page">Learn</a>
          <?php } else { ?>
            <a href="awareness.php">Learn</a>
          <?php } ?>
        </li>

        <?php if (isset($_SESSION['user_id'])) { ?>

          <?php if ($_SESSION['role'] == 'admin') { ?>
            <li>
              <?php if ($active == 'admin_dashboard.php') { ?>
                <a href="admin_dashboard.php" aria-current="page">Admin</a>
              <?php } else { ?>
                <a href="admin_dashboard.php">Admin</a>
              <?php } ?>
            </li>
          <?php } ?>

          <li>
            <?php if ($active == 'dashboard.php') { ?>
              <a href="dashboard.php" aria-current="page">My Dashboard</a>
            <?php } else { ?>
              <a href="dashboard.php">My Dashboard</a>
            <?php } ?>
          </li>
          <li><a class="btn btn-ghost" href="logout.php">Log out</a></li>

        <?php } else { ?>

          <li>
            <?php if ($active == 'login.php') { ?>
              <a href="login.php" aria-current="page">Log in</a>
            <?php } else { ?>
              <a href="login.php">Log in</a>
            <?php } ?>
          </li>
          <li><a class="btn btn-solid" href="register.php">Sign up</a></li>

        <?php } ?>
      </ul>
    </nav>

  </div>
</header>

<?php if (isset($_SESSION['flash'])) { ?>
  <div class="wrap">
    <p class="flash flash-<?php echo htmlspecialchars($_SESSION['flash']['type']); ?>" role="alert">
      <?php echo htmlspecialchars($_SESSION['flash']['msg']); ?>
    </p>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php } ?>

<main id="main">
