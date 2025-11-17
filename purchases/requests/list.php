<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/PurchaseRequest.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = new User();
$user->read($_SESSION['user_id']);

// Récupérer les demandes d'achat
$purchaseRequest = new PurchaseRequest();
$requests = $purchaseRequest->getAll();

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
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle demande
                        </a>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="submitted" <?= ($_GET['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Soumise</option>
                                    <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                                    <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="priority" class="form-label">Priorité</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">Toutes les priorités</option>
                                    <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Faible</option>
                                    <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Moyenne</option>
                                    <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Élevée</option>
                                    <option value="urgent" <?= ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                                       placeholder="Numéro, titre, description...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                </div>
                            </div>
                        </form>
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
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune demande d'achat trouvée</h5>
                                <p class="text-muted">Commencez par créer votre première demande d'achat.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer une demande
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
                                            <th>Montant</th>
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
                                                    <div class="fw-bold"><?= htmlspecialchars($request['title']) ?></div>
                                                    <?php if ($request['description']): ?>
                                                        <small class="text-muted"><?= htmlspecialchars(substr($request['description'], 0, 50)) ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($request['requester_name']) ?></td>
                                                <td><?= htmlspecialchars($request['department']) ?></td>
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
                                                    <span class="badge bg-<?= $priority_colors[$request['priority']] ?>">
                                                        <?= $priority_labels[$request['priority']] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?= number_format($request['total_amount'], 2, ',', ' ') ?> <?= $request['currency'] ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'draft' => 'secondary',
                                                        'submitted' => 'info',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'cancelled' => 'dark'
                                                    ];
                                                    $status_labels = [
                                                        'draft' => 'Brouillon',
                                                        'submitted' => 'Soumise',
                                                        'approved' => 'Approuvée',
                                                        'rejected' => 'Rejetée',
                                                        'cancelled' => 'Annulée'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $status_colors[$request['status']] ?>">
                                                        <?= $status_labels[$request['status']] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y', strtotime($request['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?= $request['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($request['status'] === 'draft'): ?>
                                                            <a href="edit.php?id=<?= $request['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($request['status'] === 'approved'): ?>
                                                            <a href="../orders/create_from_request.php?request_id=<?= $request['id'] ?>" class="btn btn-outline-success" title="Créer bon de commande">
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
