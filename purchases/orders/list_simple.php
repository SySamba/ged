<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

// Récupérer les bons de commande
$database = new Database();
$pdo = $database->getConnection();

$query = "SELECT po.*, s.name as supplier_name, pr.request_number
          FROM purchase_orders po 
          LEFT JOIN suppliers s ON po.supplier_id = s.id 
          LEFT JOIN purchase_requests pr ON po.request_id = pr.id
          ORDER BY po.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Liste des bons de commande";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-file-invoice me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create_simple.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouveau bon de commande
                        </a>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Total</h6>
                                        <h4><?= count($orders) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-invoice fa-2x"></i>
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
                                        <h6>Envoyées</h6>
                                        <h4><?= count(array_filter($orders, fn($o) => $o['status'] === 'sent')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-paper-plane fa-2x"></i>
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
                                        <h6>Confirmées</h6>
                                        <h4><?= count(array_filter($orders, fn($o) => $o['status'] === 'confirmed')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check fa-2x"></i>
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
                                        <h6>Terminées</h6>
                                        <h4><?= count(array_filter($orders, fn($o) => $o['status'] === 'completed')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-flag-checkered fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des bons de commande -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bons de commande (<?= count($orders) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun bon de commande</h5>
                                <p class="text-muted">Créez votre premier bon de commande.</p>
                                <a href="create_simple.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Nouveau bon de commande
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Numéro</th>
                                            <th>Fournisseur</th>
                                            <th>Demande liée</th>
                                            <th>Montant total</th>
                                            <th>Statut</th>
                                            <th>Date commande</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($order['supplier_name'] ?? 'N/A') ?>
                                                </td>
                                                <td>
                                                    <?php if ($order['request_number']): ?>
                                                        <a href="../requests/view_simple.php?id=<?= $order['request_id'] ?>">
                                                            <?= htmlspecialchars($order['request_number']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucune</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= number_format($order['total_amount'] ?? 0, 2, ',', ' ') ?> €</strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'draft' => 'secondary',
                                                        'sent' => 'warning',
                                                        'confirmed' => 'success',
                                                        'partially_received' => 'info',
                                                        'completed' => 'primary',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $status_labels = [
                                                        'draft' => 'Brouillon',
                                                        'sent' => 'Envoyée',
                                                        'confirmed' => 'Confirmée',
                                                        'partially_received' => 'Partiellement reçue',
                                                        'completed' => 'Terminée',
                                                        'cancelled' => 'Annulée'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $status_colors[$order['status']] ?? 'secondary' ?>">
                                                        <?= $status_labels[$order['status']] ?? $order['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= $order['order_date'] ? date('d/m/Y', strtotime($order['order_date'])) : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view_simple.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_simple.php?id=<?= $order['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="print_simple.php?id=<?= $order['id'] ?>" class="btn btn-outline-success" title="Imprimer">
                                                            <i class="fas fa-print"></i>
                                                        </a>
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
