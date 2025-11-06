<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Récupérer les paramètres de recherche
$search = sanitize($_GET['search'] ?? '');
$categorie_id = !empty($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : null;
$date_debut = sanitize($_GET['date_debut'] ?? '');
$date_fin = sanitize($_GET['date_fin'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Construire les filtres
$filters = [
    'statut' => 'archive',
    'limit' => $limit,
    'offset' => $offset,
    'order_by' => 'date_archivage',
    'order_dir' => 'DESC'
];

if ($search) $filters['search'] = $search;
if ($categorie_id) $filters['categorie_id'] = $categorie_id;
if ($date_debut) $filters['date_debut'] = $date_debut;
if ($date_fin) $filters['date_fin'] = $date_fin;

// Si pas admin, ne voir que ses propres documents
if ($_SESSION['user_role'] !== 'admin') {
    $filters['utilisateur_id'] = $_SESSION['user_id'];
}

// Récupérer les documents et catégories
$document = new Document();
$category = new Category();

$documents = $document->getByStatus('archive', $filters);
$categories = $category->getAll();

// Compter le total pour la pagination
$totalFilters = $filters;
unset($totalFilters['limit'], $totalFilters['offset']);
$totalDocuments = count($document->getByStatus('archive', $totalFilters));
$totalPages = ceil($totalDocuments / $limit);

// Statistiques d'archivage
$stats = $document->getArchivingStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents archivés - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .archived-header {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .document-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            opacity: 0.8;
            border-left: 4px solid #ffc107;
        }
        
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
            opacity: 1;
        }
        
        .archived-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffc107;
            color: #000;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .archive-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .btn-restore {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
        }
        
        .btn-restore:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        .stats-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-archive me-2"></i>
                        Documents archivés
                        <span class="badge bg-warning text-dark"><?= $totalDocuments ?></span>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-folder me-1"></i>
                                Documents actifs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- En-tête des archives -->
                <div class="archived-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div style="width: 80px; height: 80px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; background: rgba(255, 255, 255, 0.2);">
                                <i class="fas fa-archive"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-2">Archives des documents</h3>
                            <p class="mb-0 opacity-75">
                                Consultez et gérez vos documents archivés. Ces documents ne sont plus visibles dans la liste principale mais restent accessibles ici.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="h4 text-warning mb-1"><?= $totalDocuments ?></div>
                            <small class="text-muted">Documents archivés</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="h4 text-info mb-1"><?= $stats['recent_archived'] ?? 0 ?></div>
                            <small class="text-muted">Archivés cette semaine</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="h4 text-success mb-1"><?= formatFileSize($stats['archived_size'] ?? 0) ?></div>
                            <small class="text-muted">Espace archivé</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="h4 text-danger mb-1"><?= $stats['pending_deletion'] ?? 0 ?></div>
                            <small class="text-muted">À supprimer bientôt</small>
                        </div>
                    </div>
                </div>

                <!-- Filtres de recherche -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filtrer les archives
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Nom, mots-clés, description...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="categorie_id" class="form-label">Catégorie</label>
                                <select class="form-select" id="categorie_id" name="categorie_id">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $categorie_id == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_debut" class="form-label">Archivé du</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_fin" class="form-label">Au</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($search || $categorie_id || $date_debut || $date_fin): ?>
                            <div class="mt-3">
                                <a href="<?= APP_URL ?>/documents/archived.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Effacer les filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Liste des documents archivés -->
                <?php if (empty($documents)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-archive fa-4x text-muted mb-3"></i>
                        <h4>Aucun document archivé</h4>
                        <p class="text-muted">
                            <?php if ($search || $categorie_id || $date_debut || $date_fin): ?>
                                Aucun document archivé ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Vous n'avez pas encore de documents archivés.
                            <?php endif; ?>
                        </p>
                        <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-primary">
                            <i class="fas fa-folder me-2"></i>
                            Voir les documents actifs
                        </a>
                    </div>
                <?php else: ?>
                    <div class="document-grid">
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-card">
                                <div class="archived-badge">
                                    <i class="fas fa-archive me-1"></i>
                                    Archivé
                                </div>
                                
                                <!-- En-tête du document -->
                                <div class="d-flex align-items-start mb-3">
                                    <div class="file-icon <?= strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION)) ?> me-3">
                                        <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($doc['nom_original']) ?></h6>
                                        <small class="text-muted"><?= formatFileSize($doc['taille_fichier']) ?></small>
                                    </div>
                                </div>
                                
                                <!-- Informations d'archivage -->
                                <div class="archive-info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <small class="fw-bold text-warning">
                                                <i class="fas fa-calendar me-1"></i>
                                                Archivé le <?= date('d/m/Y', strtotime($doc['date_archivage'])) ?>
                                            </small>
                                            <?php if ($doc['archive_par_nom']): ?>
                                                <br><small class="text-muted">
                                                    Par <?= htmlspecialchars($doc['archive_par_prenom'] . ' ' . $doc['archive_par_nom']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($doc['raison_archivage']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>Raison :</strong> <?= htmlspecialchars($doc['raison_archivage']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Actions -->
                                <div class="d-flex gap-2 mb-3">
                                    <a href="<?= APP_URL ?>/documents/simple_viewer.php?id=<?= $doc['id'] ?>" 
                                       class="btn btn-outline-primary flex-fill btn-sm"
                                       target="_blank"
                                       title="Voir le document">
                                        <i class="fas fa-eye me-1"></i>
                                        Voir
                                    </a>
                                    
                                    <?php if (hasPermission('documents', 'unarchive') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                        <a href="<?= APP_URL ?>/documents/archive.php?id=<?= $doc['id'] ?>&action=unarchive&from=archived" 
                                           class="btn btn-restore flex-fill btn-sm"
                                           onclick="return confirm('Êtes-vous sûr de vouloir restaurer ce document ?')"
                                           title="Restaurer le document">
                                            <i class="fas fa-undo me-1"></i>
                                            Restaurer
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($doc['categorie_nom']): ?>
                                    <div class="mb-2">
                                        <span class="badge" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                            <?= htmlspecialchars($doc['categorie_nom']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($doc['description']): ?>
                                    <p class="small text-muted mb-2"><?= htmlspecialchars(substr($doc['description'], 0, 100)) ?><?= strlen($doc['description']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>Créé le <?= date('d/m/Y', strtotime($doc['date_upload'])) ?></span>
                                    <span><?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Navigation des archives" class="mt-4">
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
function getFileTypeIcon($mimeType) {
    $icons = [
        'application/pdf' => 'file-pdf',
        'application/msword' => 'file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-word',
        'application/vnd.ms-excel' => 'file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-excel',
        'image/jpeg' => 'file-image',
        'image/jpg' => 'file-image',
        'image/png' => 'file-image',
        'image/gif' => 'file-image',
        'image/bmp' => 'file-image',
        'image/webp' => 'file-image'
    ];
    return $icons[$mimeType] ?? 'file';
}
?>
