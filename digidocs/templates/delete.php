<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . APP_URL . '/templates/list.php');
    exit;
}

$template = new Template();
$result = $template->deleteGeneratedDocument($id);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . APP_URL . '/templates/list.php');
exit;
