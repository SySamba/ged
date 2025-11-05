<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$user = new User();
$currentUser = $user->getById($_SESSION['user_id']);

if (!$currentUser) {
    $_SESSION['error'] = 'Utilisateur introuvable';
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = 'Tous les champs sont obligatoires';
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Les nouveaux mots de passe ne correspondent pas';
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['error'] = 'Le nouveau mot de passe doit contenir au moins 6 caractères';
    } else {
        // Récupérer l'utilisateur avec le mot de passe pour vérification
        $userWithPassword = $user->getByIdWithPassword($_SESSION['user_id']);
        if (!$userWithPassword || !password_verify($currentPassword, $userWithPassword['password'])) {
            $_SESSION['error'] = 'Mot de passe actuel incorrect';
        } else {
            $result = $user->changePassword($_SESSION['user_id'], $newPassword);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Mot de passe modifié avec succès';
            } else {
                $_SESSION['error'] = $result['message'];
            }
        }
    }
}

// Traitement des préférences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preferences'])) {
    $preferences = [
        'notifications_email' => isset($_POST['notifications_email']) ? 1 : 0,
        'theme' => sanitize($_POST['theme'] ?? 'light'),
        'langue' => sanitize($_POST['langue'] ?? 'fr'),
        'documents_per_page' => (int)($_POST['documents_per_page'] ?? 10)
    ];
    
    $result = $user->updatePreferences($_SESSION['user_id'], $preferences);
    
    if ($result['success']) {
        $_SESSION['success'] = 'Préférences sauvegardées avec succès';
    } else {
        $_SESSION['error'] = $result['message'];
    }
}

// Récupérer les préférences actuelles
$preferences = $user->getPreferences($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .settings-section {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .settings-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 0.5rem;
            padding: 1.5rem;
            background: #fff5f5;
        }
        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-cog me-2"></i>
                        Paramètres
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/profile.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-user me-1"></i>
                            Retour au profil
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Changement de mot de passe -->
                <div class="settings-section">
                    <div class="settings-header">
                        <h4>
                            <i class="fas fa-lock me-2"></i>
                            Sécurité
                        </h4>
                        <p class="text-muted mb-0">Modifiez votre mot de passe pour sécuriser votre compte</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="6" required>
                                <div class="form-text">Au moins 6 caractères</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="6" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Changer le mot de passe
                        </button>
                    </form>
                </div>

                <!-- Préférences -->
                <div class="settings-section">
                    <div class="settings-header">
                        <h4>
                            <i class="fas fa-sliders-h me-2"></i>
                            Préférences
                        </h4>
                        <p class="text-muted mb-0">Personnalisez votre expérience utilisateur</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="save_preferences" value="1">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="theme" class="form-label">Thème</label>
                                <select class="form-select" id="theme" name="theme">
                                    <option value="light" <?= ($preferences['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Clair</option>
                                    <option value="dark" <?= ($preferences['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Sombre</option>
                                    <option value="auto" <?= ($preferences['theme'] ?? 'light') === 'auto' ? 'selected' : '' ?>>Automatique</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="langue" class="form-label">Langue</label>
                                <select class="form-select" id="langue" name="langue">
                                    <option value="fr" <?= ($preferences['langue'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                                    <option value="en" <?= ($preferences['langue'] ?? 'fr') === 'en' ? 'selected' : '' ?>>English</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="documents_per_page" class="form-label">Documents par page</label>
                            <select class="form-select" id="documents_per_page" name="documents_per_page">
                                <option value="10" <?= ($preferences['documents_per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= ($preferences['documents_per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($preferences['documents_per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($preferences['documents_per_page'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notifications_email" name="notifications_email" 
                                   <?= ($preferences['notifications_email'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="notifications_email">
                                Recevoir les notifications par email
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>
                            Sauvegarder les préférences
                        </button>
                    </form>
                </div>

                <!-- Informations du compte -->
                <div class="settings-section">
                    <div class="settings-header">
                        <h4>
                            <i class="fas fa-info-circle me-2"></i>
                            Informations du compte
                        </h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></p>
                            <p><strong>Email :</strong> <?= htmlspecialchars($currentUser['email']) ?></p>
                            <p><strong>Rôle :</strong> <?= ucfirst($currentUser['role']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Membre depuis :</strong> <?= date('d/m/Y', strtotime($currentUser['date_creation'])) ?></p>
                            <p><strong>Dernière connexion :</strong> <?= $currentUser['derniere_connexion'] ? date('d/m/Y à H:i', strtotime($currentUser['derniere_connexion'])) : 'Jamais' ?></p>
                            <p><strong>Statut :</strong> 
                                <span class="badge bg-<?= $currentUser['statut'] === 'actif' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($currentUser['statut']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Zone de danger -->
                <?php if ($currentUser['role'] !== 'admin'): ?>
                <div class="danger-zone">
                    <h5 class="text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Zone de danger
                    </h5>
                    <p class="text-muted">
                        Les actions suivantes sont irréversibles. Procédez avec prudence.
                    </p>
                    <button class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                        <i class="fas fa-trash me-2"></i>
                        Supprimer mon compte
                    </button>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDeleteAccount() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ?\n\nCette action est irréversible et supprimera tous vos documents.')) {
                if (confirm('Dernière confirmation : voulez-vous vraiment supprimer définitivement votre compte ?')) {
                    window.location.href = '<?= APP_URL ?>/auth/delete_account.php';
                }
            }
        }

        // Validation du mot de passe en temps réel
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
