<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

// Récupérer les statistiques
$document = new Document();
$user = new User();
$category = new Category();

$docStats = $document->getStatistics();
$userStats = $user->getStatistics();
$categories = $category->getAll();

// Documents récents
$recentDocs = $document->search(['limit' => 5, 'order_by' => 'date_upload', 'order_dir' => 'DESC']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css?v=<?= time() ?>" rel="stylesheet">
    <style>
        /* Barre d'outils sticky */
        .sticky-toolbar {
            position: sticky;
            top: 76px; /* Hauteur de la navbar fixe */
            background: white;
            z-index: 1020;
            padding: 15px 0;
            margin: -15px 0 15px 0;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Ajustement pour mobile */
        @media (max-width: 768px) {
            .sticky-toolbar {
                top: 56px; /* Hauteur navbar mobile */
            }
        }
        
        /* Fix pour Chrome - Forcer l'affichage du bouton Bons de commande */
        .sidebar .nav-item a[href*="orders.php"] {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 999 !important;
        }
        
        /* Styles spécifiques pour Chrome */
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .sidebar .nav-item a[href*="orders.php"] {
                -webkit-appearance: none !important;
                display: block !important;
                visibility: visible !important;
            }
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
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Documents
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($docStats['total_documents'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Espace utilisé
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= formatFileSize($docStats['total_size'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hdd fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Documents récents
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $docStats['recent_documents'] ?? 0 ?>
                                        </div>
                                        <small class="text-muted">Cette semaine</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Utilisateurs actifs
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $userStats['total_users'] ?? 0 ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques et tableaux -->
                <div class="row">
                    <!-- Documents par catégorie -->
                    <div class="col-xl-6 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Documents par catégorie</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($docStats['by_category'])): ?>
                                    <?php foreach ($docStats['by_category'] as $cat): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm font-weight-bold" style="color: <?= $cat['couleur'] ?>">
                                                    <?= htmlspecialchars($cat['nom']) ?>
                                                </span>
                                                <span class="text-sm"><?= $cat['count'] ?> documents</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $docStats['total_documents'] > 0 ? ($cat['count'] / $docStats['total_documents']) * 100 : 0 ?>%; background-color: <?= $cat['couleur'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">Aucun document trouvé</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Documents récents -->
                    <div class="col-xl-6 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Documents récents</h6>
                                <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-primary">
                                    Voir tout
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recentDocs)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recentDocs as $doc): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-<?= $doc['categorie_icone'] ?? 'file' ?>" 
                                                           style="color: <?= $doc['categorie_couleur'] ?? '#6c757d' ?>"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?= htmlspecialchars($doc['nom_original']) ?></h6>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($doc['categorie_nom'] ?? 'Sans catégorie') ?> • 
                                                            <?= formatFileSize($doc['taille_fichier']) ?> • 
                                                            <?= date('d/m/Y H:i', strtotime($doc['date_upload'])) ?>
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">Aucun document récent</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="<?= APP_URL ?>/documents/upload.php" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-upload mb-2"></i><br>
                                            Uploader un document
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-info btn-lg w-100">
                                            <i class="fas fa-search mb-2"></i><br>
                                            Rechercher documents
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-file-contract mb-2"></i><br>
                                            Modèles Canva
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?= APP_URL ?>/jobs/list.php" class="btn btn-warning btn-lg w-100">
                                            <i class="fas fa-briefcase mb-2"></i><br>
                                            Offres d'emploi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
