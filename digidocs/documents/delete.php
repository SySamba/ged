<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

$document = new Document();
$result = $document->delete($id);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . APP_URL . '/documents/list.php');
exit;
