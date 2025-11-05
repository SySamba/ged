<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Vérifier les permissions admin
if (!hasPermission('users', 'create')) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/admin/users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => sanitize($_POST['nom'] ?? ''),
        'prenom' => sanitize($_POST['prenom'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'role' => sanitize($_POST['role'] ?? 'employe')
    ];
    
    // Validation
    if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password'])) {
        $_SESSION['error'] = 'Tous les champs sont obligatoires';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Adresse email invalide';
    } elseif (strlen($data['password']) < 6) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $user = new User();
        $result = $user->create($data);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Utilisateur créé avec succès';
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}

header('Location: ' . APP_URL . '/admin/users.php');
exit;
