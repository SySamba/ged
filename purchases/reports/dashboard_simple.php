<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

// Récupérer les données pour les rapports
$database = new Database();
$pdo = $database->getConnection();

// Statistiques générales
$stats = [];

// Total des demandes par mois (6 derniers mois)
$query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count,
    SUM(total_amount) as total_amount
FROM purchase_requests 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$monthly_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 des fournisseurs par nombre de commandes
$query = "SELECT 
    s.name,
    COUNT(po.id) as order_count,
    SUM(po.total_amount) as total_amount
FROM suppliers s
LEFT JOIN purchase_orders po ON s.id = po.supplier_id
GROUP BY s.id, s.name
HAVING order_count > 0
ORDER BY order_count DESC
LIMIT 5";
$stmt = $pdo->prepare($query);
$stmt->execute();
$top_suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Répartition par statut
$query = "SELECT status, COUNT(*) as count FROM purchase_requests GROUP BY status";
$stmt = $pdo->prepare($query);
$stmt->execute();
$status_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Montants par priorité
$query = "SELECT 
    priority,
    COUNT(*) as count,
    SUM(total_amount) as total_amount
FROM purchase_requests 
WHERE total_amount > 0
GROUP BY priority
ORDER BY total_amount DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$priority_amounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Rapports et Analytics";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-chart-bar me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exporter PDF</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exporter Excel</button>
                        </div>
                    </div>
                </div>

                <!-- KPIs principaux -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Total demandes</h6>
                                        <h4><?= array_sum(array_column($status_distribution, 'count')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Montant total</h6>
                                        <h4><?= number_format(array_sum(array_column($monthly_requests, 'total_amount')), 0, ',', ' ') ?> €</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-euro-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Fournisseurs actifs</h6>
                                        <h4><?= count($top_suppliers) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Délai moyen</h6>
                                        <h4>15 jours</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des demandes (6 derniers mois)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par statut</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableaux de données -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 5 Fournisseurs</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_suppliers)): ?>
                                    <p class="text-muted text-center">Aucune donnée disponible</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Fournisseur</th>
                                                    <th>Commandes</th>
                                                    <th>Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_suppliers as $supplier): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                                                        <td><span class="badge bg-primary"><?= $supplier['order_count'] ?></span></td>
                                                        <td><strong><?= number_format($supplier['total_amount'] ?? 0, 0, ',', ' ') ?> €</strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Analyse par priorité</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($priority_amounts)): ?>
                                    <p class="text-muted text-center">Aucune donnée disponible</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Priorité</th>
                                                    <th>Nombre</th>
                                                    <th>Montant total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $priority_labels = [
                                                    'urgent' => 'Urgente',
                                                    'high' => 'Élevée',
                                                    'medium' => 'Moyenne',
                                                    'low' => 'Faible'
                                                ];
                                                $priority_colors = [
                                                    'urgent' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    'low' => 'success'
                                                ];
                                                foreach ($priority_amounts as $priority): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-<?= $priority_colors[$priority['priority']] ?? 'secondary' ?>">
                                                                <?= $priority_labels[$priority['priority']] ?? $priority['priority'] ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $priority['count'] ?></td>
                                                        <td><strong><?= number_format($priority['total_amount'], 0, ',', ' ') ?> €</strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="../requests/list_simple.php" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="fas fa-file-alt me-2"></i>Voir toutes les demandes
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="../orders/list_simple.php" class="btn btn-outline-success w-100 mb-2">
                                            <i class="fas fa-shopping-cart me-2"></i>Voir tous les bons de commande
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="../suppliers/list_simple.php" class="btn btn-outline-info w-100 mb-2">
                                            <i class="fas fa-building me-2"></i>Gérer les fournisseurs
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="../workflow/tracking_simple.php" class="btn btn-outline-secondary w-100 mb-2">
                                            <i class="fas fa-route me-2"></i>Suivi des workflows
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique mensuel
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [<?php echo '"' . implode('","', array_reverse(array_column($monthly_requests, 'month'))) . '"'; ?>],
                datasets: [{
                    label: 'Nombre de demandes',
                    data: [<?php echo implode(',', array_reverse(array_column($monthly_requests, 'count'))); ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique des statuts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo '"' . implode('","', array_column($status_distribution, 'status')) . '"'; ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($status_distribution, 'count')); ?>],
                    backgroundColor: [
                        '#6c757d', // draft
                        '#17a2b8', // submitted  
                        '#28a745', // approved
                        '#dc3545'  // rejected
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    </script>
</body>
</html>
