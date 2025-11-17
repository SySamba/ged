<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/PurchaseOrder.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = new User();
$user->read($_SESSION['user_id']);

// Récupérer les bons de commande
$purchaseOrder = new PurchaseOrder();
$orders = $purchaseOrder->getAll();

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
                        <a href="create.php" class="btn btn-primary">
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
                                        <h6 class="card-title">Total commandes</h6>
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
                                        <h6 class="card-title">En attente</h6>
                                        <h4><?= count(array_filter($orders, fn($o) => $o['status'] === 'sent')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
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
                                        <h6 class="card-title">Confirmées</h6>
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
                                        <h6 class="card-title">Montant total</h6>
                                        <h4><?= number_format(array_sum(array_column($orders, 'total_with_tax')), 0, ',', ' ') ?> €</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-euro-sign fa-2x"></i>
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
                                <h5 class="text-muted">Aucun bon de commande trouvé</h5>
                                <p class="text-muted">Créez votre premier bon de commande ou générez-le depuis une demande approuvée.</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Créer un bon de commande
                                    </a>
                                    <a href="../requests/list.php" class="btn btn-outline-primary">
                                        <i class="fas fa-file-alt me-2"></i>Voir les demandes
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Numéro</th>
                                            <th>Titre</th>
                                            <th>Fournisseur</th>
                                            <th>Acheteur</th>
                                            <th>Montant TTC</th>
                                            <th>Date commande</th>
                                            <th>Livraison prévue</th>
                                            <th>Statut</th>
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
                                                    <div class="fw-bold"><?= htmlspecialchars($order['title']) ?></div>
                                                    <?php if ($order['notes']): ?>
                                                        <small class="text-muted"><?= htmlspecialchars(substr($order['notes'], 0, 50)) ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($order['supplier_name']) ?></td>
                                                <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                                                <td>
                                                    <strong><?= number_format($order['total_with_tax'], 2, ',', ' ') ?> <?= $order['currency'] ?></strong>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($order['order_date'])) ?>
                                                </td>
                                                <td>
                                                    <?php if ($order['expected_delivery_date']): ?>
                                                        <?= date('d/m/Y', strtotime($order['expected_delivery_date'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non définie</span>
                                                    <?php endif; ?>
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
                                                    <span class="badge bg-<?= $status_colors[$order['status']] ?>">
                                                        <?= $status_labels[$order['status']] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($order['status'] === 'draft'): ?>
                                                            <a href="edit.php?id=<?= $order['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (in_array($order['status'], ['confirmed', 'partially_received'])): ?>
                                                            <a href="../receipts/create.php?order_id=<?= $order['id'] ?>" class="btn btn-outline-success" title="Créer réception">
                                                                <i class="fas fa-truck"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="print.php?id=<?= $order['id'] ?>" class="btn btn-outline-info" title="Imprimer" target="_blank">
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
