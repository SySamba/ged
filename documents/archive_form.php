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
        .archive-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
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
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-archive:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
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
        
        .warning-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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

                <!-- Avertissement d'archivage -->
                <div class="archive-warning">
                    <div class="warning-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <h3 class="mb-3">Archivage du document</h3>
                    <p class="mb-0">
                        Le document sera <strong>archivé</strong> et ne sera plus visible dans la liste principale. 
                        Il pourra être restauré à tout moment depuis les archives.
                    </p>
                </div>

                <!-- Informations du document à archiver -->
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
                            
                            <?php if ($doc['mots_cles']): ?>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-tags me-1"></i>
                                        <strong>Mots-clés :</strong>
                                    </small>
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
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'archivage -->
                <div class="document-info-card">
                    <h5 class="mb-4">
                        <i class="fas fa-comment me-2"></i>
                        Raison de l'archivage
                    </h5>
                    
                    <form method="POST" id="archiveForm">
                        <div class="mb-4">
                            <label for="raison" class="form-label">
                                Pourquoi archiver ce document ? <span class="text-muted">(optionnel)</span>
                            </label>
                            <textarea class="form-control" id="raison" name="raison" rows="3" 
                                      placeholder="Ex: Document obsolète, remplacé par une nouvelle version, fin de projet..."></textarea>
                            <div class="form-text">
                                Cette information sera conservée dans l'historique d'archivage
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Que se passe-t-il après l'archivage ?</strong>
                            <ul class="mb-0 mt-2">
                                <li>Le document ne sera plus visible dans la liste principale</li>
                                <li>Il sera accessible depuis la section "Documents archivés"</li>
                                <li>Il pourra être restauré à tout moment</li>
                                <li>Le fichier physique reste intact sur le serveur</li>
                            </ul>
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
        // Confirmation avant archivage
        document.getElementById('archiveForm').addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir archiver ce document ?')) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-resize textarea
        const textarea = document.getElementById('raison');
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
