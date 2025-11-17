<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$page_title = "Gestion des Achats";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .purchase-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .purchase-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card-info {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i><?php echo $page_title; ?></h1>
                </div>

                <!-- Message d'installation -->
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Module d'achat en cours d'installation</h4>
                    <p>Le module de gestion des achats est en cours de configuration. Veuillez d'abord :</p>
                    <ol>
                        <li><a href="repair_installation.php" class="alert-link">Exécuter le script de réparation</a></li>
                        <li><a href="test.php" class="alert-link">Tester la configuration</a></li>
                    </ol>
                    <hr>
                    <p class="mb-0">Une fois l'installation terminée, vous aurez accès à toutes les fonctionnalités du cycle d'achat.</p>
                </div>

                <!-- Menu principal -->
                <div class="row">
                    <!-- Demandes d'achat -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-plus-circle fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Demandes d'achat</h5>
                                <p class="card-text">Créer et gérer les demandes d'achat de votre département.</p>
                                <div class="d-grid gap-2">
                                    <a href="requests/create_simple.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Nouvelle demande
                                    </a>
                                    <a href="requests/list_simple.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-2"></i>Voir toutes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bons de commande -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file-invoice fa-3x text-success"></i>
                                </div>
                                <h5 class="card-title">Bons de commande</h5>
                                <p class="card-text">Gérer les bons de commande et suivre les livraisons.</p>
                                <div class="d-grid gap-2">
                                    <a href="orders/create_simple.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Nouveau bon
                                    </a>
                                    <a href="orders/list_simple.php" class="btn btn-outline-success">
                                        <i class="fas fa-list me-2"></i>Voir tous
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fournisseurs -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-building fa-3x text-info"></i>
                                </div>
                                <h5 class="card-title">Fournisseurs</h5>
                                <p class="card-text">Gérer la base de données des fournisseurs et partenaires.</p>
                                <div class="d-grid gap-2">
                                    <a href="suppliers/create_simple.php" class="btn btn-info">
                                        <i class="fas fa-plus me-2"></i>Nouveau fournisseur
                                    </a>
                                    <a href="suppliers/list_simple.php" class="btn btn-outline-info">
                                        <i class="fas fa-list me-2"></i>Voir tous
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Installation et configuration -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-cogs fa-3x text-warning"></i>
                                </div>
                                <h5 class="card-title">Installation</h5>
                                <p class="card-text">Configurer et installer le module d'achat.</p>
                                <div class="d-grid gap-2">
                                    <a href="repair_installation.php" class="btn btn-warning">
                                        <i class="fas fa-wrench me-2"></i>Réparer installation
                                    </a>
                                    <a href="test.php" class="btn btn-outline-warning">
                                        <i class="fas fa-vial me-2"></i>Tester le module
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suivi du workflow -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-route fa-3x text-secondary"></i>
                                </div>
                                <h5 class="card-title">Suivi du cycle</h5>
                                <p class="card-text">Suivre le workflow complet du cycle d'achat.</p>
                                <div class="d-grid gap-2">
                                    <a href="workflow/tracking_simple.php" class="btn btn-secondary">
                                        <i class="fas fa-chart-line me-2"></i>Voir le suivi
                                    </a>
                                    <a href="reports/dashboard_simple.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-chart-bar me-2"></i>Rapports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentation -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-book fa-3x text-dark"></i>
                                </div>
                                <h5 class="card-title">Documentation</h5>
                                <p class="card-text">Guide d'utilisation et documentation du module.</p>
                                <div class="d-grid gap-2">
                                    <a href="docs/guide.php" class="btn btn-dark">
                                        <i class="fas fa-book-open me-2"></i>Guide utilisateur
                                    </a>
                                    <a href="docs/api.php" class="btn btn-outline-dark">
                                        <i class="fas fa-code me-2"></i>Documentation API
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
