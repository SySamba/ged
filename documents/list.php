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
    'limit' => $limit,
    'offset' => $offset,
    'order_by' => 'date_upload',
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

$documents = $document->search($filters);
$categories = $category->getAll();

// Compter le total pour la pagination
$totalFilters = $filters;
unset($totalFilters['limit'], $totalFilters['offset']);
$totalDocuments = count($document->search($totalFilters));
$totalPages = ceil($totalDocuments / $limit);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes documents - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        /* Styles pour les cartes de documents */
        .document-card {
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }
        
        .file-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .file-icon.pdf { background: #dc3545; }
        .file-icon.doc, .file-icon.docx { background: #0d6efd; }
        .file-icon.xls, .file-icon.xlsx { background: #198754; }
        .file-icon.jpg, .file-icon.jpeg, .file-icon.png, .file-icon.gif { background: #fd7e14; }
        .file-icon { background: #6c757d; }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
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
                        <i class="fas fa-folder me-2"></i>
                        Mes documents
                        <span class="badge bg-secondary"><?= $totalDocuments ?></span>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?= APP_URL ?>/documents/upload.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-upload me-1"></i>
                                Uploader
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filtres de recherche -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filtres de recherche
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
                                <label for="date_debut" class="form-label">Du</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_fin" class="form-label">Au</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($search || $categorie_id || $date_debut || $date_fin): ?>
                            <div class="mt-3">
                                <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Effacer les filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Liste des documents -->
                <?php if (empty($documents)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <h4>Aucun document trouvé</h4>
                        <p class="text-muted">
                            <?php if ($search || $categorie_id || $date_debut || $date_fin): ?>
                                Aucun document ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Vous n'avez pas encore uploadé de documents.
                            <?php endif; ?>
                        </p>
                        <a href="<?= APP_URL ?>/documents/upload.php" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            Uploader votre premier document
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($documents as $doc): ?>
                            <div class="col-lg-6 col-md-6">
                                <div class="card shadow-sm document-card h-100">
                                    <div class="card-body d-flex flex-column">
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
                                        
                                        <!-- Actions principales -->
                                        <div class="d-flex gap-2 mb-3">
                                            <a href="<?= APP_URL ?>/documents/simple_viewer.php?id=<?= $doc['id'] ?>" 
                                               class="btn btn-primary flex-fill"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               title="Ouvrir le document dans un nouvel onglet">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Voir
                                            </a>
                                            <button class="btn btn-info flex-fill" 
                                                    onclick="showDocumentDetails(<?= htmlspecialchars(json_encode($doc)) ?>)"
                                                    title="Voir les informations détaillées">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Détails
                                            </button>
                                        </div>
                                        
                                        <!-- Actions de gestion -->
                                        <?php if (hasPermission('documents', 'update') || $doc['utilisateur_id'] == $_SESSION['user_id'] || hasPermission('documents', 'delete') || hasPermission('documents', 'archive')): ?>
                                        <div class="d-flex gap-2 mb-3">
                                            <?php if (hasPermission('documents', 'update') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                                <a href="<?= APP_URL ?>/documents/edit.php?id=<?= $doc['id'] ?>" 
                                                   class="btn btn-outline-warning flex-fill btn-sm"
                                                   title="Modifier les informations du document">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Modifier
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (hasPermission('documents', 'archive') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                                <a href="<?= APP_URL ?>/documents/archive.php?id=<?= $doc['id'] ?>&action=archive" 
                                                   class="btn btn-outline-secondary flex-fill btn-sm"
                                                   title="Archiver ce document">
                                                    <i class="fas fa-archive me-1"></i>
                                                    Archiver
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (hasPermission('documents', 'delete') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                                <a href="<?= APP_URL ?>/documents/delete.php?id=<?= $doc['id'] ?>" 
                                                   class="btn btn-outline-danger flex-fill btn-sm"
                                                   title="Supprimer ce document"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    Supprimer
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Catégorie -->
                                        <?php if ($doc['categorie_nom']): ?>
                                            <div class="mb-2">
                                                <span class="badge" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                                    <?= htmlspecialchars($doc['categorie_nom']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Description -->
                                        <?php if ($doc['description']): ?>
                                            <p class="small text-muted mb-2"><?= htmlspecialchars(substr($doc['description'], 0, 100)) ?><?= strlen($doc['description']) > 100 ? '...' : '' ?></p>
                                        <?php endif; ?>
                                        
                                        <!-- Footer avec date et utilisateur -->
                                        <div class="d-flex justify-content-between align-items-center text-muted small mt-auto">
                                            <span><?= date('d/m/Y', strtotime($doc['date_upload'])) ?></span>
                                            <span><?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Navigation des documents" class="mt-4">
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
    <script>
        // Fonction pour afficher les détails d'un document dans un modal
        function showDocumentDetails(doc) {
            const modalHtml = `
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Détails du document
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <div class="file-icon ${doc.nom_original.split('.').pop().toLowerCase()} mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <h6>${doc.nom_original}</h6>
                                        <small class="text-muted">${formatFileSize(doc.taille_fichier)}</small>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <h6><i class="fas fa-calendar me-2"></i>Informations générales</h6>
                                            <p class="mb-1"><strong>Date d'upload :</strong> ${new Date(doc.date_upload).toLocaleDateString('fr-FR')}</p>
                                            <p class="mb-1"><strong>Uploadé par :</strong> ${doc.utilisateur_prenom} ${doc.utilisateur_nom}</p>
                                            <p class="mb-1"><strong>Type MIME :</strong> ${doc.type_mime}</p>
                                        </div>
                                        
                                        ${doc.categorie_nom ? `
                                        <div class="mb-3">
                                            <h6><i class="fas fa-folder me-2"></i>Catégorie</h6>
                                            <span class="badge" style="background-color: ${doc.categorie_couleur}">${doc.categorie_nom}</span>
                                        </div>
                                        ` : ''}
                                        
                                        ${doc.mots_cles ? `
                                        <div class="mb-3">
                                            <h6><i class="fas fa-tags me-2"></i>Mots-clés</h6>
                                            ${doc.mots_cles.split(',').map(m => m.trim()).filter(m => m).map(m => `<span class="badge bg-secondary me-1">${m}</span>`).join('')}
                                        </div>
                                        ` : ''}
                                        
                                        ${doc.description ? `
                                        <div class="mb-3">
                                            <h6><i class="fas fa-align-left me-2"></i>Description</h6>
                                            <p class="text-muted">${doc.description}</p>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Fermer
                                </button>
                                <a href="<?= APP_URL ?>/documents/download.php?id=${doc.id}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Télécharger
                                </a>
                                ${(<?= hasPermission('documents', 'update') ? 'true' : 'false' ?> || doc.utilisateur_id == <?= $_SESSION['user_id'] ?>) ? `
                                <a href="<?= APP_URL ?>/documents/edit.php?id=${doc.id}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                                ` : ''}
                                ${(<?= hasPermission('documents', 'delete') ? 'true' : 'false' ?> || doc.utilisateur_id == <?= $_SESSION['user_id'] ?>) ? `
                                <a href="<?= APP_URL ?>/documents/delete.php?id=${doc.id}" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                    <i class="fas fa-trash-alt me-2"></i>Supprimer
                                </a>
                                ` : ''}
                                <a href="<?= APP_URL ?>/documents/viewer.php?id=${doc.id}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-2"></i>Ouvrir le document
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Supprimer le modal existant s'il y en a un
            const existingModal = document.getElementById('detailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Ajouter le nouveau modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
            
            // Supprimer le modal après fermeture
            document.getElementById('detailsModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
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
