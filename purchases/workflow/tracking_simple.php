<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

// Initialiser les données par défaut
$requests_by_status = [];
$orders_by_status = [];
$workflow_items = [];

try {
    // Récupérer les données pour le suivi du workflow
    $database = new Database();
    $pdo = $database->getConnection();

    // Vérifier si les tables existent
    $tables_exist = true;
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'purchase_requests'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $tables_exist = false;
        }
    } catch (Exception $e) {
        $tables_exist = false;
    }

    if ($tables_exist) {
        // Demandes d'achat par statut
        $query = "SELECT status, COUNT(*) as count FROM purchase_requests GROUP BY status";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $requests_by_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Bons de commande par statut
        $query = "SELECT status, COUNT(*) as count FROM purchase_orders GROUP BY status";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $orders_by_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} catch (Exception $e) {
    // En cas d'erreur, utiliser des données par défaut
    $requests_by_status = [];
    $orders_by_status = [];
}

// Workflow complet - Demandes avec leur progression
if ($tables_exist) {
    try {
        $query = "SELECT 
            pr.id as request_id,
            pr.request_number,
            pr.title,
            pr.total_amount,
            pr.status as request_status,
            pr.created_at as request_date,
            u.username as requester_name,
            
            po.id as order_id,
            po.order_number,
            po.status as order_status,
            po.order_date,
            s.name as supplier_name
            
        FROM purchase_requests pr
        LEFT JOIN users u ON pr.requester_id = u.id
        LEFT JOIN purchase_orders po ON pr.id = po.request_id
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        ORDER BY pr.created_at DESC
        LIMIT 50";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $workflow_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $workflow_items = [];
    }
}

$page_title = "Suivi du cycle d'achat";
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
        .progress-timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0;
            position: relative;
        }
        .progress-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        .timeline-step {
            background: white;
            border: 3px solid #dee2e6;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            flex-direction: column;
        }
        .timeline-step.completed {
            border-color: #28a745;
            background-color: #28a745;
            color: white;
        }
        .timeline-step.in-progress {
            border-color: #ffc107;
            background-color: #ffc107;
            color: white;
        }
        .timeline-step.pending {
            border-color: #6c757d;
            background-color: #f8f9fa;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-route me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                        </a>
                    </div>
                </div>

                <!-- Vue d'ensemble du processus -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Vue d'ensemble du processus d'achat</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress-timeline">
                            <div class="text-center">
                                <div class="timeline-step completed">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <small class="mt-2 d-block">Demande</small>
                                <strong><?= array_sum($requests_by_status) ?></strong>
                            </div>
                            
                            <div class="text-center">
                                <div class="timeline-step <?= !empty($requests_by_status['approved']) ? 'completed' : 'pending' ?>">
                                    <i class="fas fa-check"></i>
                                </div>
                                <small class="mt-2 d-block">Approbation</small>
                                <strong><?= $requests_by_status['approved'] ?? 0 ?></strong>
                            </div>
                            
                            <div class="text-center">
                                <div class="timeline-step <?= !empty($orders_by_status) ? 'completed' : 'pending' ?>">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <small class="mt-2 d-block">Commande</small>
                                <strong><?= array_sum($orders_by_status ?: []) ?></strong>
                            </div>
                            
                            <div class="text-center">
                                <div class="timeline-step <?= !empty($orders_by_status['completed']) ? 'completed' : 'pending' ?>">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <small class="mt-2 d-block">Réception</small>
                                <strong><?= $orders_by_status['completed'] ?? 0 ?></strong>
                            </div>
                            
                            <div class="text-center">
                                <div class="timeline-step pending">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <small class="mt-2 d-block">Facturation</small>
                                <strong>0</strong>
                            </div>
                            
                            <div class="text-center">
                                <div class="timeline-step pending">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <small class="mt-2 d-block">Comptabilité</small>
                                <strong>0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques par étape -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Demandes d'achat</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach (['draft' => 'Brouillons', 'submitted' => 'Soumises', 'approved' => 'Approuvées', 'rejected' => 'Rejetées'] as $status => $label): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?= $label ?></span>
                                        <strong><?= $requests_by_status[$status] ?? 0 ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Bons de commande</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach (['draft' => 'Brouillons', 'sent' => 'Envoyées', 'confirmed' => 'Confirmées', 'completed' => 'Terminées'] as $status => $label): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?= $label ?></span>
                                        <strong><?= $orders_by_status[$status] ?? 0 ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suivi détaillé des workflows -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Suivi détaillé des workflows</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($workflow_items)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun workflow en cours</h5>
                                <p class="text-muted">Créez votre première demande d'achat pour commencer.</p>
                                <a href="../requests/create_simple.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer une demande
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Demande</th>
                                            <th>Titre</th>
                                            <th>Demandeur</th>
                                            <th>Montant</th>
                                            <th>Progression</th>
                                            <th>Statut actuel</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($workflow_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['request_number']) ?></strong>
                                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($item['request_date'])) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($item['title']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($item['requester_name'] ?? 'N/A') ?>
                                                </td>
                                                <td>
                                                    <strong><?= number_format($item['total_amount'] ?? 0, 2, ',', ' ') ?> €</strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $progress = 0;
                                                    $current_step = 'Demande créée';
                                                    
                                                    if ($item['request_status'] === 'approved') {
                                                        $progress = 25;
                                                        $current_step = 'Demande approuvée';
                                                    }
                                                    if ($item['order_id']) {
                                                        $progress = 50;
                                                        $current_step = 'Bon de commande créé';
                                                    }
                                                    if ($item['order_status'] === 'completed') {
                                                        $progress = 75;
                                                        $current_step = 'Marchandises reçues';
                                                    }
                                                    ?>
                                                    <div class="progress mb-1" style="height: 8px;">
                                                        <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= $progress ?>% - <?= $current_step ?></small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'draft' => 'secondary',
                                                        'submitted' => 'info',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'sent' => 'warning',
                                                        'confirmed' => 'success',
                                                        'completed' => 'primary'
                                                    ];
                                                    
                                                    if ($item['order_status']) {
                                                        echo '<span class="badge bg-' . ($status_colors[$item['order_status']] ?? 'secondary') . '">Commande ' . $item['order_status'] . '</span>';
                                                    } else {
                                                        echo '<span class="badge bg-' . ($status_colors[$item['request_status']] ?? 'secondary') . '">Demande ' . $item['request_status'] . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../requests/view_simple.php?id=<?= $item['request_id'] ?>" class="btn btn-outline-primary" title="Voir demande">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($item['order_id']): ?>
                                                            <a href="../orders/view_simple.php?id=<?= $item['order_id'] ?>" class="btn btn-outline-success" title="Voir commande">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
