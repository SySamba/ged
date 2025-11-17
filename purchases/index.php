<?php
session_start();
require_once '../config/database.php';
require_once '../classes/User.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user = new User();
$user->read($_SESSION['user_id']);

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

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card purchase-card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Demandes en attente</div>
                                        <div class="h5 mb-0 font-weight-bold" id="pending-requests">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card purchase-card stat-card-success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Commandes actives</div>
                                        <div class="h5 mb-0 font-weight-bold" id="active-orders">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-invoice fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card purchase-card stat-card-warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Factures à traiter</div>
                                        <div class="h5 mb-0 font-weight-bold" id="pending-invoices">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-receipt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card purchase-card stat-card-info">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Budget mensuel</div>
                                        <div class="h5 mb-0 font-weight-bold" id="monthly-budget">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-euro-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    <a href="requests/create.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Nouvelle demande
                                    </a>
                                    <a href="requests/list.php" class="btn btn-outline-primary">
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
                                    <a href="orders/create.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Nouveau bon
                                    </a>
                                    <a href="orders/list.php" class="btn btn-outline-success">
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
                                    <a href="suppliers/create.php" class="btn btn-info">
                                        <i class="fas fa-plus me-2"></i>Nouveau fournisseur
                                    </a>
                                    <a href="suppliers/list.php" class="btn btn-outline-info">
                                        <i class="fas fa-list me-2"></i>Voir tous
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réceptions -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-truck fa-3x text-warning"></i>
                                </div>
                                <h5 class="card-title">Réceptions</h5>
                                <p class="card-text">Enregistrer les réceptions de marchandises et contrôler la qualité.</p>
                                <div class="d-grid gap-2">
                                    <a href="receipts/create.php" class="btn btn-warning">
                                        <i class="fas fa-plus me-2"></i>Nouvelle réception
                                    </a>
                                    <a href="receipts/list.php" class="btn btn-outline-warning">
                                        <i class="fas fa-list me-2"></i>Voir toutes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Factures -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-receipt fa-3x text-danger"></i>
                                </div>
                                <h5 class="card-title">Factures</h5>
                                <p class="card-text">Traiter les factures fournisseurs et valider les paiements.</p>
                                <div class="d-grid gap-2">
                                    <a href="invoices/create.php" class="btn btn-danger">
                                        <i class="fas fa-plus me-2"></i>Nouvelle facture
                                    </a>
                                    <a href="invoices/list.php" class="btn btn-outline-danger">
                                        <i class="fas fa-list me-2"></i>Voir toutes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comptabilité -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card purchase-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calculator fa-3x text-secondary"></i>
                                </div>
                                <h5 class="card-title">Comptabilité</h5>
                                <p class="card-text">Générer les écritures comptables et suivre les budgets.</p>
                                <div class="d-grid gap-2">
                                    <a href="accounting/entries.php" class="btn btn-secondary">
                                        <i class="fas fa-book me-2"></i>Écritures
                                    </a>
                                    <a href="accounting/reports.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-chart-bar me-2"></i>Rapports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activités récentes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activités récentes</h5>
                            </div>
                            <div class="card-body">
                                <div id="recent-activities">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Charger les statistiques
        function loadStatistics() {
            fetch('api/statistics.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pending-requests').textContent = data.pending_requests || '0';
                    document.getElementById('active-orders').textContent = data.active_orders || '0';
                    document.getElementById('pending-invoices').textContent = data.pending_invoices || '0';
                    document.getElementById('monthly-budget').textContent = (data.monthly_budget || '0') + ' €';
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des statistiques:', error);
                });
        }

        // Charger les activités récentes
        function loadRecentActivities() {
            fetch('api/recent_activities.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recent-activities');
                    if (data.length === 0) {
                        container.innerHTML = '<p class="text-muted text-center">Aucune activité récente</p>';
                        return;
                    }

                    let html = '<div class="list-group list-group-flush">';
                    data.forEach(activity => {
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${activity.title}</h6>
                                    <small>${activity.date}</small>
                                </div>
                                <p class="mb-1">${activity.description}</p>
                                <small class="text-muted">${activity.user}</small>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des activités:', error);
                    document.getElementById('recent-activities').innerHTML = 
                        '<p class="text-danger text-center">Erreur lors du chargement des activités</p>';
                });
        }

        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadRecentActivities();
        });
    </script>
</body>
</html>
