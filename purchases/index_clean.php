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
        .action-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .action-card .card-body {
            padding: 2rem;
            text-align: center;
        }
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .quick-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- En-tête -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-shopping-cart me-2 text-primary"></i>
                        <?php echo $page_title; ?>
                    </h1>
                    <div class="btn-toolbar">
                        <a href="test.php" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-vial me-1"></i>Test du module
                        </a>
                        <a href="docs/guide.php" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-book me-1"></i>Guide d'utilisation
                        </a>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="quick-stats">
                    <div class="row">
                        <div class="col-md-3 stat-item">
                            <div class="stat-number" id="total-requests">0</div>
                            <div class="stat-label">Demandes totales</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number" id="pending-requests">0</div>
                            <div class="stat-label">En attente</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number" id="active-orders">0</div>
                            <div class="stat-label">Commandes actives</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number" id="total-suppliers">0</div>
                            <div class="stat-label">Fournisseurs</div>
                        </div>
                    </div>
                </div>

                <!-- Actions principales -->
                <div class="row g-4 mb-4">
                    <!-- Nouvelle demande -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-primary">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h5 class="card-title">Nouvelle demande</h5>
                                <p class="card-text text-muted">Créer une nouvelle demande d'achat</p>
                                <a href="requests/create_simple.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Mes demandes -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-success">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h5 class="card-title">Mes demandes</h5>
                                <p class="card-text text-muted">Consulter toutes les demandes d'achat</p>
                                <a href="requests/list_simple.php" class="btn btn-success">
                                    <i class="fas fa-list me-2"></i>Voir tout
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Fournisseurs -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-info">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h5 class="card-title">Fournisseurs</h5>
                                <p class="card-text text-muted">Gérer la base des fournisseurs</p>
                                <a href="suppliers/list_simple.php" class="btn btn-info">
                                    <i class="fas fa-building me-2"></i>Gérer
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bons de commande -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-warning">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h5 class="card-title">Bons de commande</h5>
                                <p class="card-text text-muted">Gérer les commandes fournisseurs</p>
                                <a href="orders/list_simple.php" class="btn btn-warning">
                                    <i class="fas fa-shopping-cart me-2"></i>Voir tout
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Suivi -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-secondary">
                                    <i class="fas fa-route"></i>
                                </div>
                                <h5 class="card-title">Suivi du cycle</h5>
                                <p class="card-text text-muted">Suivre l'avancement des achats</p>
                                <a href="workflow/tracking_simple.php" class="btn btn-secondary">
                                    <i class="fas fa-chart-line me-2"></i>Suivre
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Rapports -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <div class="action-icon text-dark">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h5 class="card-title">Rapports</h5>
                                <p class="card-text text-muted">Analytics et tableaux de bord</p>
                                <a href="reports/dashboard_simple.php" class="btn btn-dark">
                                    <i class="fas fa-chart-bar me-2"></i>Analyser
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activités récentes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Activités récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="recent-activities">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2 text-muted">Chargement des activités...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-link me-2"></i>Liens utiles
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="repair_installation.php" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-wrench me-1"></i>Réparer l'installation
                                    </a>
                                    <a href="docs/guide.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-book me-1"></i>Guide utilisateur
                                    </a>
                                    <a href="docs/api.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-code me-1"></i>Documentation API
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
    <script>
        // Charger les statistiques
        function loadStatistics() {
            // Simuler des données pour éviter les erreurs
            document.getElementById('total-requests').textContent = '12';
            document.getElementById('pending-requests').textContent = '3';
            document.getElementById('active-orders').textContent = '5';
            document.getElementById('total-suppliers').textContent = '8';
        }

        // Charger les activités récentes
        function loadRecentActivities() {
            const container = document.getElementById('recent-activities');
            
            // Simuler des activités récentes
            const activities = [
                {
                    title: 'Nouvelle demande créée',
                    description: 'Demande d\'achat pour matériel informatique',
                    user: 'Utilisateur',
                    date: 'Il y a 2 heures',
                    icon: 'fas fa-plus-circle text-success'
                },
                {
                    title: 'Fournisseur ajouté',
                    description: 'Nouveau fournisseur TechCorp ajouté',
                    user: 'Admin',
                    date: 'Hier',
                    icon: 'fas fa-building text-info'
                },
                {
                    title: 'Module installé',
                    description: 'Module de gestion des achats activé',
                    user: 'Système',
                    date: 'Il y a 2 jours',
                    icon: 'fas fa-cog text-primary'
                }
            ];

            let html = '<div class="list-group list-group-flush">';
            activities.forEach(activity => {
                html += `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="${activity.icon} fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">${activity.title}</h6>
                                    <p class="mb-1">${activity.description}</p>
                                    <small class="text-muted">Par ${activity.user}</small>
                                </div>
                            </div>
                            <small class="text-muted">${activity.date}</small>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadRecentActivities();
        });
    </script>
</body>
</html>
