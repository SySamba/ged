<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Récupérer les paramètres de recherche
$search = sanitize($_GET['search'] ?? '');
$type_contrat = sanitize($_GET['type_contrat'] ?? '');
$lieu = sanitize($_GET['lieu'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Construire les filtres
$filters = [
    'limit' => $limit,
    'offset' => $offset
];

if ($search) $filters['search'] = $search;
if ($type_contrat) $filters['type_contrat'] = $type_contrat;
if ($lieu) $filters['lieu'] = $lieu;

// Récupérer les offres
$job = new Job();
$offres = $job->getOffres($filters);

// Compter le total pour la pagination
$totalFilters = $filters;
unset($totalFilters['limit'], $totalFilters['offset']);
$totalOffres = count($job->getOffres($totalFilters));
$totalPages = ceil($totalOffres / $limit);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres d'emploi - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-briefcase me-2"></i>
                        Offres d'emploi
                        <span class="badge bg-secondary"><?= $totalOffres ?></span>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if (hasPermission('offres', 'create')): ?>
                            <div class="btn-group me-2">
                                <a href="<?= APP_URL ?>/jobs/create.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Publier une offre
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtres de recherche -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Rechercher des offres
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label for="search" class="form-label">Mot-clé</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Titre, entreprise, compétences...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="type_contrat" class="form-label">Type de contrat</label>
                                <select class="form-select" id="type_contrat" name="type_contrat">
                                    <option value="">Tous les types</option>
                                    <option value="CDI" <?= $type_contrat === 'CDI' ? 'selected' : '' ?>>CDI</option>
                                    <option value="CDD" <?= $type_contrat === 'CDD' ? 'selected' : '' ?>>CDD</option>
                                    <option value="Stage" <?= $type_contrat === 'Stage' ? 'selected' : '' ?>>Stage</option>
                                    <option value="Freelance" <?= $type_contrat === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="lieu" class="form-label">Lieu</label>
                                <input type="text" class="form-control" id="lieu" name="lieu" 
                                       value="<?= htmlspecialchars($lieu) ?>" 
                                       placeholder="Ville, région...">
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($search || $type_contrat || $lieu): ?>
                            <div class="mt-3">
                                <a href="<?= APP_URL ?>/jobs/list.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Effacer les filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Liste des offres -->
                <?php if (empty($offres)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                        <h4>Aucune offre d'emploi trouvée</h4>
                        <p class="text-muted">
                            <?php if ($search || $type_contrat || $lieu): ?>
                                Aucune offre ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Il n'y a pas d'offres d'emploi disponibles pour le moment.
                            <?php endif; ?>
                        </p>
                        <?php if (hasPermission('offres', 'create')): ?>
                            <a href="<?= APP_URL ?>/jobs/create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Publier la première offre
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($offres as $offre): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?= htmlspecialchars($offre['entreprise']) ?></h6>
                                        <div>
                                            <span class="badge bg-<?= getContractTypeColor($offre['type_contrat']) ?>">
                                                <?= htmlspecialchars($offre['type_contrat']) ?>
                                            </span>
                                            <?php if ($offre['nb_candidatures'] > 0): ?>
                                                <span class="badge bg-info ms-1">
                                                    <?= $offre['nb_candidatures'] ?> candidature<?= $offre['nb_candidatures'] > 1 ? 's' : '' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($offre['titre']) ?></h5>
                                        
                                        <div class="mb-3">
                                            <?php if ($offre['lieu']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($offre['lieu']) ?>
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if ($offre['salaire']): ?>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    <?= htmlspecialchars($offre['salaire']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="card-text">
                                            <?= htmlspecialchars(substr($offre['description'], 0, 150)) ?>
                                            <?= strlen($offre['description']) > 150 ? '...' : '' ?>
                                        </p>
                                        
                                        <?php if ($offre['competences_requises']): ?>
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <strong>Compétences :</strong>
                                                    <?= htmlspecialchars(substr($offre['competences_requises'], 0, 100)) ?>
                                                    <?= strlen($offre['competences_requises']) > 100 ? '...' : '' ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Publié le <?= date('d/m/Y', strtotime($offre['date_creation'])) ?>
                                                <?php if ($offre['date_limite']): ?>
                                                    <br>Limite : <?= date('d/m/Y', strtotime($offre['date_limite'])) ?>
                                                <?php endif; ?>
                                            </small>
                                            <div class="btn-group">
                                                <a href="<?= APP_URL ?>/jobs/view.php?id=<?= $offre['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                                <a href="<?= APP_URL ?>/jobs/apply.php?id=<?= $offre['id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-paper-plane me-1"></i>Postuler
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted small">
                                        <i class="fas fa-user me-1"></i>
                                        Par <?= htmlspecialchars($offre['createur_prenom'] . ' ' . $offre['createur_nom']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Navigation des offres" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getContractTypeColor($type) {
    $colors = [
        'CDI' => 'success',
        'CDD' => 'primary',
        'Stage' => 'warning',
        'Freelance' => 'info'
    ];
    return $colors[$type] ?? 'secondary';
}
?>
