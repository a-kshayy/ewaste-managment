<?php
require_once 'config.php';

// Empty out and destroy the session, then start a fresh one
session_unset();
session_destroy();
session_start();

$_SESSION['flash'] = array('msg' => 'You have been logged out.', 'type' => 'info');

header('Location: index.php');
exit;
