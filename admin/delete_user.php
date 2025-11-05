<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Vérifier les permissions admin
if (!hasPermission('users', 'delete')) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/admin/users.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    $_SESSION['error'] = 'ID utilisateur manquant';
    header('Location: ' . APP_URL . '/admin/users.php');
    exit;
}

$user = new User();
$result = $user->delete($id);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . APP_URL . '/admin/users.php');
exit;
