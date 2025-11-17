<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/Supplier.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = new User();
$user->read($_SESSION['user_id']);

// Récupérer les fournisseurs
$supplier = new Supplier();
$suppliers = $supplier->getAll();

$page_title = "Liste des fournisseurs";
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
                    <h1 class="h2"><i class="fas fa-building me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouveau fournisseur
                        </a>
                    </div>
                </div>

                <!-- Barre de recherche -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Rechercher un fournisseur</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                                       placeholder="Nom, contact, email...">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des fournisseurs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Fournisseurs (<?= count($suppliers) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($suppliers)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun fournisseur trouvé</h5>
                                <p class="text-muted">Commencez par ajouter vos premiers fournisseurs.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Ajouter un fournisseur
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Ville</th>
                                            <th>Délai paiement</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($supplier['name']) ?></div>
                                                    <?php if ($supplier['tax_number']): ?>
                                                        <small class="text-muted">N° TVA: <?= htmlspecialchars($supplier['tax_number']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($supplier['contact_person']): ?>
                                                        <?= htmlspecialchars($supplier['contact_person']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non renseigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($supplier['email']): ?>
                                                        <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>">
                                                            <?= htmlspecialchars($supplier['email']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non renseigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($supplier['phone']): ?>
                                                        <a href="tel:<?= htmlspecialchars($supplier['phone']) ?>">
                                                            <?= htmlspecialchars($supplier['phone']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non renseigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($supplier['city']): ?>
                                                        <?= htmlspecialchars($supplier['city']) ?>
                                                        <?php if ($supplier['country'] && $supplier['country'] !== 'France'): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($supplier['country']) ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non renseigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $supplier['payment_terms'] ?> jours</span>
                                                </td>
                                                <td>
                                                    <?php if ($supplier['status'] === 'active'): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?= $supplier['id'] ?>" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?= $supplier['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../orders/create.php?supplier_id=<?= $supplier['id'] ?>" class="btn btn-outline-success" title="Nouvelle commande">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </a>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="history.php?id=<?= $supplier['id'] ?>">
                                                                    <i class="fas fa-history me-2"></i>Historique
                                                                </a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="delete.php?id=<?= $supplier['id'] ?>" 
                                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')">
                                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                                </a></li>
                                                            </ul>
                                                        </div>
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

                <!-- Statistiques des fournisseurs -->
                <?php if (!empty($suppliers)): ?>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total fournisseurs</h6>
                                        <h4><?= count($suppliers) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Fournisseurs actifs</h6>
                                        <h4><?= count(array_filter($suppliers, fn($s) => $s['status'] === 'active')) ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Délai moyen</h6>
                                        <h4><?= round(array_sum(array_column($suppliers, 'payment_terms')) / count($suppliers)) ?> jours</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
