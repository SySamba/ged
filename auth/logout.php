<?php
require_once __DIR__ . '/../config/config.php';

$user = new User();
$result = $user->logout();

header('Location: ' . APP_URL . '/auth/login.php');
exit;
