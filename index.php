<?php
require_once __DIR__ . '/config/config.php';

// Rediriger vers le dashboard si connecté
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

// Rediriger vers la page de connexion
header('Location: ' . APP_URL . '/auth/login.php');
exit;
