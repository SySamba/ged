<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$document = new Document();
$doc = $document->getById($id);

if (!$doc) {
    $_SESSION['error'] = 'Document introuvable';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Vérifier les permissions
if (!hasPermission('documents', 'read') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Déterminer le type de fichier pour l'affichage
$extension = strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION));
$isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
$isPdf = $extension === 'pdf';
$isOffice = in_array($extension, ['doc', 'docx', 'xls', 'xlsx']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($doc['nom_original']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .document-viewer {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            min-height: 600px;
        }
        .document-content {
            padding: 2rem;
            text-align: center;
        }
        .document-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .document-info {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        .file-icon-large {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .download-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
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
                        <i class="fas fa-eye me-2"></i>
                        Visualisation du document
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                               class="btn btn-sm btn-success">
                                <i class="fas fa-download me-1"></i>
                                Télécharger
                            </a>
                            <?php if (hasPermission('documents', 'update') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                <a href="<?= APP_URL ?>/documents/edit.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Modifier
                                </a>
                            <?php endif; ?>
                            <?php if (hasPermission('documents', 'delete') || $doc['utilisateur_id'] == $_SESSION['user_id']): ?>
                                <a href="<?= APP_URL ?>/documents/delete.php?id=<?= $doc['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                    <i class="fas fa-trash-alt me-1"></i>
                                    Supprimer
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Informations du document -->
                    <div class="col-lg-4 mb-4">
                        <div class="document-info">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations du document
                            </h5>
                            
                            <div class="mb-3">
                                <strong>Nom original :</strong><br>
                                <?= htmlspecialchars($doc['nom_original']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Catégorie :</strong><br>
                                <?php if ($doc['categorie_nom']): ?>
                                    <span class="badge" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                        <i class="fas fa-<?= $doc['categorie_icone'] ?? 'folder' ?> me-1"></i>
                                        <?= htmlspecialchars($doc['categorie_nom']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Aucune catégorie</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Taille :</strong><br>
                                <?= formatFileSize($doc['taille_fichier']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Type :</strong><br>
                                <?= htmlspecialchars($doc['type_mime']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Uploadé par :</strong><br>
                                <?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Date d'upload :</strong><br>
                                <?= date('d/m/Y à H:i', strtotime($doc['date_upload'])) ?>
                            </div>
                            
                            <?php if ($doc['date_modification'] != $doc['date_upload']): ?>
                                <div class="mb-3">
                                    <strong>Dernière modification :</strong><br>
                                    <?= date('d/m/Y à H:i', strtotime($doc['date_modification'])) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($doc['mots_cles']): ?>
                                <div class="mb-3">
                                    <strong>Mots-clés :</strong><br>
                                    <?php 
                                    $motsCles = explode(',', $doc['mots_cles']);
                                    foreach ($motsCles as $mot): 
                                        $mot = trim($mot);
                                        if ($mot):
                                    ?>
                                        <span class="badge bg-secondary me-1"><?= htmlspecialchars($mot) ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($doc['description']): ?>
                                <div class="mb-3">
                                    <strong>Description :</strong><br>
                                    <?= nl2br(htmlspecialchars($doc['description'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Visualisation du document -->
                    <div class="col-lg-8">
                        <div class="document-viewer">
                            <div class="document-content">
                                <?php if ($isImage): ?>
                                    <!-- Affichage des images -->
                                    <h5 class="mb-4">
                                        <i class="fas fa-image me-2"></i>
                                        Aperçu de l'image
                                    </h5>
                                    <div class="document-preview">
                                        <img src="<?= APP_URL ?>/documents/preview.php?id=<?= $doc['id'] ?>" 
                                             alt="<?= htmlspecialchars($doc['nom_original']) ?>"
                                             class="img-fluid">
                                    </div>
                                
                                <?php elseif ($isPdf): ?>
                                    <!-- Affichage des PDF -->
                                    <h5 class="mb-4">
                                        <i class="fas fa-file-pdf me-2 text-danger"></i>
                                        Document PDF
                                    </h5>
                                    <div class="document-preview">
                                        <iframe src="<?= APP_URL ?>/documents/preview.php?id=<?= $doc['id'] ?>" 
                                                width="100%" height="600" 
                                                style="border: 1px solid #ddd; border-radius: 0.5rem;">
                                            Votre navigateur ne supporte pas l'affichage des PDF.
                                        </iframe>
                                    </div>
                                
                                <?php elseif ($isOffice): ?>
                                    <!-- Documents Office -->
                                    <div class="file-icon-large text-primary">
                                        <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
                                    </div>
                                    <h5>Document Office</h5>
                                    <p class="text-muted mb-4">
                                        Ce type de document ne peut pas être prévisualisé dans le navigateur.
                                    </p>
                                    <div class="download-section">
                                        <h6>Télécharger pour ouvrir</h6>
                                        <p class="mb-3">Cliquez sur le bouton ci-dessous pour télécharger et ouvrir le document.</p>
                                        <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                                           class="btn btn-light btn-lg">
                                            <i class="fas fa-download me-2"></i>
                                            Télécharger <?= htmlspecialchars($doc['nom_original']) ?>
                                        </a>
                                    </div>
                                
                                <?php else: ?>
                                    <!-- Autres types de fichiers -->
                                    <div class="file-icon-large text-secondary">
                                        <i class="fas fa-file"></i>
                                    </div>
                                    <h5>Fichier non prévisualisable</h5>
                                    <p class="text-muted mb-4">
                                        Ce type de fichier ne peut pas être affiché dans le navigateur.
                                    </p>
                                    <div class="download-section">
                                        <h6>Télécharger le fichier</h6>
                                        <p class="mb-3">Téléchargez le fichier pour l'ouvrir avec l'application appropriée.</p>
                                        <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                                           class="btn btn-light btn-lg">
                                            <i class="fas fa-download me-2"></i>
                                            Télécharger <?= htmlspecialchars($doc['nom_original']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
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
