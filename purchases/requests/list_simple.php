<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

// Récupérer les demandes d'achat
$database = new Database();
$pdo = $database->getConnection();

$query = "SELECT pr.*, u.username as requester_name 
          FROM purchase_requests pr 
          LEFT JOIN users u ON pr.requester_id = u.id 
          ORDER BY pr.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Liste des demandes d'achat";
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
                    <h1 class="h2"><i class="fas fa-file-alt me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create_simple.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle demande
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
                                        <h4><?= count($requests) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x"></i>
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
                                        <h6>En attente</h6>
                                        <h4><?= count(array_filter($requests, fn($r) => $r['status'] === 'submitted')) ?></h4>
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
                                        <h6>Approuvées</h6>
                                        <h4><?= count(array_filter($requests, fn($r) => $r['status'] === 'approved')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Brouillons</h6>
                                        <h4><?= count(array_filter($requests, fn($r) => $r['status'] === 'draft')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-edit fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des demandes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Demandes d'achat (<?= count($requests) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($requests)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune demande d'achat</h5>
                                <p class="text-muted">Créez votre première demande d'achat.</p>
                                <a href="create_simple.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Nouvelle demande
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Numéro</th>
                                            <th>Titre</th>
                                            <th>Demandeur</th>
                                            <th>Département</th>
                                            <th>Priorité</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($request['request_number']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($request['title']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($request['requester_name'] ?? 'N/A') ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($request['department']) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priority_colors = [
                                                        'low' => 'success',
                                                        'medium' => 'warning',
                                                        'high' => 'danger',
                                                        'urgent' => 'dark'
                                                    ];
                                                    $priority_labels = [
                                                        'low' => 'Faible',
                                                        'medium' => 'Moyenne',
                                                        'high' => 'Élevée',
                                                        'urgent' => 'Urgente'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $priority_colors[$request['priority']] ?? 'secondary' ?>">
                                                        <?= $priority_labels[$request['priority']] ?? $request['priority'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'draft' => 'secondary',
                                                        'submitted' => 'info',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger'
                                                    ];
                                                    $status_labels = [
                                                        'draft' => 'Brouillon',
                                                        'submitted' => 'Soumise',
                                                        'approved' => 'Approuvée',
                                                        'rejected' => 'Rejetée'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $status_colors[$request['status']] ?? 'secondary' ?>">
                                                        <?= $status_labels[$request['status']] ?? $request['status'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($request['created_at'])) ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view_simple.php?id=<?= $request['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_simple.php?id=<?= $request['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
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
