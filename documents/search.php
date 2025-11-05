<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Récupérer les paramètres de recherche avancée
$search = sanitize($_GET['search'] ?? '');
$categorie_id = !empty($_GET['categorie_id']) ? (int)$_GET['categorie_id'] : null;
$utilisateur_id = !empty($_GET['utilisateur_id']) ? (int)$_GET['utilisateur_id'] : null;
$type_mime = sanitize($_GET['type_mime'] ?? '');
$date_debut = sanitize($_GET['date_debut'] ?? '');
$date_fin = sanitize($_GET['date_fin'] ?? '');
$taille_min = !empty($_GET['taille_min']) ? (int)$_GET['taille_min'] : null;
$taille_max = !empty($_GET['taille_max']) ? (int)$_GET['taille_max'] : null;

$results = [];
$searchPerformed = false;

// Effectuer la recherche si des critères sont fournis
if ($search || $categorie_id || $utilisateur_id || $type_mime || $date_debut || $date_fin || $taille_min || $taille_max) {
    $searchPerformed = true;
    
    $filters = [];
    if ($search) $filters['search'] = $search;
    if ($categorie_id) $filters['categorie_id'] = $categorie_id;
    if ($utilisateur_id) $filters['utilisateur_id'] = $utilisateur_id;
    if ($type_mime) $filters['type_mime'] = $type_mime;
    if ($date_debut) $filters['date_debut'] = $date_debut;
    if ($date_fin) $filters['date_fin'] = $date_fin;
    
    $document = new Document();
    $allResults = $document->search($filters);
    
    // Filtrer par taille si spécifié
    if ($taille_min || $taille_max) {
        $results = array_filter($allResults, function($doc) use ($taille_min, $taille_max) {
            $size = $doc['taille_fichier'];
            if ($taille_min && $size < ($taille_min * 1024 * 1024)) return false;
            if ($taille_max && $size > ($taille_max * 1024 * 1024)) return false;
            return true;
        });
    } else {
        $results = $allResults;
    }
}

// Récupérer les données pour les filtres
$category = new Category();
$categories = $category->getAll();

$user = new User();
$users = $user->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche avancée - <?= APP_NAME ?></title>
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
                        <i class="fas fa-search me-2"></i>
                        Recherche avancée
                        <?php if ($searchPerformed): ?>
                            <span class="badge bg-secondary"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                    </h1>
                </div>

                <!-- Formulaire de recherche avancée -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Critères de recherche
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="row g-3">
                                <!-- Recherche textuelle -->
                                <div class="col-md-6">
                                    <label for="search" class="form-label">
                                        <i class="fas fa-search me-1"></i>
                                        Recherche textuelle
                                    </label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?= htmlspecialchars($search) ?>" 
                                           placeholder="Nom, mots-clés, description...">
                                    <div class="form-text">Recherche dans le nom, les mots-clés et la description</div>
                                </div>
                                
                                <!-- Catégorie -->
                                <div class="col-md-3">
                                    <label for="categorie_id" class="form-label">
                                        <i class="fas fa-tags me-1"></i>
                                        Catégorie
                                    </label>
                                    <select class="form-select" id="categorie_id" name="categorie_id">
                                        <option value="">Toutes les catégories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= $categorie_id == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nom']) ?> (<?= $cat['nb_documents'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Type de fichier -->
                                <div class="col-md-3">
                                    <label for="type_mime" class="form-label">
                                        <i class="fas fa-file me-1"></i>
                                        Type de fichier
                                    </label>
                                    <select class="form-select" id="type_mime" name="type_mime">
                                        <option value="">Tous les types</option>
                                        <option value="pdf" <?= $type_mime === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                        <option value="image" <?= $type_mime === 'image' ? 'selected' : '' ?>>Images</option>
                                        <option value="word" <?= $type_mime === 'word' ? 'selected' : '' ?>>Word</option>
                                        <option value="excel" <?= $type_mime === 'excel' ? 'selected' : '' ?>>Excel</option>
                                    </select>
                                </div>
                                
                                <!-- Utilisateur -->
                                <?php if (hasPermission('documents', 'read')): ?>
                                <div class="col-md-4">
                                    <label for="utilisateur_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Uploadé par
                                    </label>
                                    <select class="form-select" id="utilisateur_id" name="utilisateur_id">
                                        <option value="">Tous les utilisateurs</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= $utilisateur_id == $u['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Période -->
                                <div class="col-md-3">
                                    <label for="date_debut" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>
                                        Date début
                                    </label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                           value="<?= htmlspecialchars($date_debut) ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="date_fin" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>
                                        Date fin
                                    </label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                           value="<?= htmlspecialchars($date_fin) ?>">
                                </div>
                                
                                <!-- Taille -->
                                <div class="col-md-3">
                                    <label for="taille_min" class="form-label">
                                        <i class="fas fa-weight-hanging me-1"></i>
                                        Taille min (MB)
                                    </label>
                                    <input type="number" class="form-control" id="taille_min" name="taille_min" 
                                           value="<?= $taille_min ?>" min="0" step="0.1">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="taille_max" class="form-label">
                                        <i class="fas fa-weight-hanging me-1"></i>
                                        Taille max (MB)
                                    </label>
                                    <input type="number" class="form-control" id="taille_max" name="taille_max" 
                                           value="<?= $taille_max ?>" min="0" step="0.1">
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>
                                        Rechercher
                                    </button>
                                    <a href="<?= APP_URL ?>/documents/search.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Effacer
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Résultats de recherche -->
                <?php if ($searchPerformed): ?>
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Résultats de recherche (<?= count($results) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                    <h5>Aucun document trouvé</h5>
                                    <p class="text-muted">Aucun document ne correspond à vos critères de recherche.</p>
                                    <p class="text-muted">Essayez de modifier ou réduire vos critères.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Document</th>
                                                <th>Catégorie</th>
                                                <th>Taille</th>
                                                <th>Utilisateur</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $doc): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="file-icon <?= strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION)) ?> me-3">
                                                                <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?= htmlspecialchars($doc['nom_original']) ?></div>
                                                                <?php if ($doc['description']): ?>
                                                                    <small class="text-muted"><?= htmlspecialchars(substr($doc['description'], 0, 50)) ?><?= strlen($doc['description']) > 50 ? '...' : '' ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($doc['categorie_nom']): ?>
                                                            <span class="badge" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                                                <?= htmlspecialchars($doc['categorie_nom']) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Aucune</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= formatFileSize($doc['taille_fichier']) ?></td>
                                                    <td><?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($doc['date_upload'])) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= APP_URL ?>/documents/view.php?id=<?= $doc['id'] ?>" 
                                                               class="btn btn-outline-primary" title="Voir">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                                                               class="btn btn-outline-success" title="Télécharger">
                                                                <i class="fas fa-download"></i>
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
                <?php else: ?>
                    <!-- Message d'accueil -->
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>Recherche avancée de documents</h4>
                        <p class="text-muted">Utilisez les critères ci-dessus pour rechercher des documents spécifiques.</p>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-font fa-2x text-primary mb-2"></i>
                                        <h6>Recherche textuelle</h6>
                                        <small class="text-muted">Recherchez dans les noms, mots-clés et descriptions</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="fas fa-filter fa-2x text-success mb-2"></i>
                                        <h6>Filtres avancés</h6>
                                        <small class="text-muted">Filtrez par catégorie, type, utilisateur et date</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="fas fa-weight-hanging fa-2x text-info mb-2"></i>
                                        <h6>Taille des fichiers</h6>
                                        <small class="text-muted">Recherchez par taille minimale et maximale</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
