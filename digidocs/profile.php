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

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => sanitize($_POST['nom'] ?? ''),
        'prenom' => sanitize($_POST['prenom'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'telephone' => sanitize($_POST['telephone'] ?? ''),
        'adresse' => sanitize($_POST['adresse'] ?? '')
    ];
    
    // Validation
    if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
        $_SESSION['error'] = 'Les champs nom, prénom et email sont obligatoires';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Adresse email invalide';
    } else {
        $result = $user->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Profil mis à jour avec succès';
            $currentUser = $user->getById($_SESSION['user_id']); // Recharger les données
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}

// Statistiques de l'utilisateur
$document = new Document();
$stats = [
    'documents_total' => $document->countByUser($_SESSION['user_id']),
    'documents_ce_mois' => $document->countByUserThisMonth($_SESSION['user_id']),
    'espace_utilise' => $document->getTotalSizeByUser($_SESSION['user_id'])
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }
        .stat-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .form-section {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- En-tête du profil -->
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h2>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        <?= htmlspecialchars($currentUser['email']) ?>
                    </p>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user-tag me-1"></i>
                            <?= ucfirst($currentUser['role']) ?>
                        </span>
                    </p>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h4><?= $stats['documents_total'] ?></h4>
                            <p class="text-muted mb-0">Documents total</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4><?= $stats['documents_ce_mois'] ?></h4>
                            <p class="text-muted mb-0">Ce mois-ci</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-hdd"></i>
                            </div>
                            <h4><?= formatFileSize($stats['espace_utilise']) ?></h4>
                            <p class="text-muted mb-0">Espace utilisé</p>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification du profil -->
                <div class="form-section">
                    <h4 class="mb-4">
                        <i class="fas fa-edit me-2"></i>
                        Modifier mon profil
                    </h4>

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

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?= htmlspecialchars($currentUser['prenom']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?= htmlspecialchars($currentUser['nom']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?= htmlspecialchars($currentUser['telephone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <input type="text" class="form-control" value="<?= ucfirst($currentUser['role']) ?>" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($currentUser['adresse'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= APP_URL ?>/settings.php" class="btn btn-outline-secondary">
                                <i class="fas fa-cog me-2"></i>
                                Paramètres
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
