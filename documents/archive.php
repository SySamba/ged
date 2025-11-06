<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$action = sanitize($_GET['action'] ?? '');

if (!$id || !in_array($action, ['archive', 'unarchive'])) {
    $_SESSION['error'] = 'Paramètres invalides';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

$document = new Document();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motif = sanitize($_POST['motif'] ?? '');
    
    if ($action === 'archive') {
        $result = $document->archive($id, $motif);
    } else {
        $result = $document->unarchive($id);
    }
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    $redirectUrl = $_POST['redirect'] ?? APP_URL . '/documents/list.php';
    header('Location: ' . $redirectUrl);
    exit;
}

// Si GET, rediriger vers la liste avec une action rapide
if ($action === 'unarchive') {
    // Désarchivage direct sans confirmation
    $result = $document->unarchive($id);
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    header('Location: ' . APP_URL . '/documents/archives.php');
    exit;
}

// Pour l'archivage, afficher la page de confirmation
$doc = $document->getById($id);
if (!$doc) {
    $_SESSION['error'] = 'Document introuvable';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archiver le document - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .archive-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .document-info-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .file-icon-large {
            width: 80px;
            height: 80px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-archive {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-archive:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
            color: white;
        }
        
        .btn-cancel {
            background: #6c757d;
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }
        
        .archive-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
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
                        <i class="fas fa-archive me-2 text-warning"></i>
                        Archiver le document
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/documents/view.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour au document
                        </a>
                    </div>
                </div>

                <!-- En-tête d'archivage -->
                <div class="archive-header">
                    <div class="archive-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h3 class="mb-3">Archivage de document</h3>
                    <p class="mb-0">
                        Le document sera déplacé vers les archives et ne sera plus visible dans la liste principale.
                    </p>
                </div>

                <!-- Informations du document -->
                <div class="document-info-card">
                    <h5 class="mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Document à archiver
                    </h5>
                    
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="file-icon-large">
                                <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-2"><?= htmlspecialchars($doc['nom_original']) ?></h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-hdd me-1"></i>
                                        <strong>Taille :</strong> <?= formatFileSize($doc['taille_fichier']) ?>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <strong>Uploadé le :</strong> <?= date('d/m/Y', strtotime($doc['date_upload'])) ?>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <strong>Par :</strong> <?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <?php if ($doc['categorie_nom']): ?>
                                        <span class="badge" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                            <?= htmlspecialchars($doc['categorie_nom']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">Aucune catégorie</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($doc['description']): ?>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-align-left me-1"></i>
                                        <strong>Description :</strong> <?= htmlspecialchars($doc['description']) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'archivage -->
                <div class="document-info-card">
                    <h5 class="mb-4">
                        <i class="fas fa-comment me-2"></i>
                        Motif d'archivage
                    </h5>
                    
                    <form method="POST" id="archiveForm">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? APP_URL . '/documents/list.php') ?>">
                        
                        <div class="mb-4">
                            <label for="motif" class="form-label">
                                Pourquoi archiver ce document ? <span class="text-muted">(optionnel)</span>
                            </label>
                            <textarea class="form-control" id="motif" name="motif" rows="3" 
                                      placeholder="Ex: Document obsolète, remplacé par une nouvelle version, fin de projet..."></textarea>
                            <div class="form-text">
                                Ce motif sera conservé dans l'historique pour traçabilité.
                            </div>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Information :</strong> Le document archivé pourra être restauré à tout moment depuis la section "Archives".
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-end">
                            <a href="<?= APP_URL ?>/documents/view.php?id=<?= $doc['id'] ?>" 
                               class="btn btn-cancel">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-archive">
                                <i class="fas fa-archive me-2"></i>
                                Archiver le document
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('motif');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
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
