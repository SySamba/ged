<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$document = new Document();
$category = new Category();

// Récupérer le document
$doc = $document->getById($id);
if (!$doc) {
    $_SESSION['error'] = 'Document introuvable';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Vérifier les permissions
if (!hasPermission('documents', 'update') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Permission insuffisante pour modifier ce document';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Récupérer les catégories
$categories = $category->getAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'categorie_id' => !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
        'mots_cles' => sanitize($_POST['mots_cles'] ?? ''),
        'description' => sanitize($_POST['description'] ?? '')
    ];
    
    $result = $document->update($id, $data);
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: ' . APP_URL . '/documents/view.php?id=' . $id);
        exit;
    } else {
        $_SESSION['error'] = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le document - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .document-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
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
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        
        .badge-category {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
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
                        <i class="fas fa-edit me-2"></i>
                        Modifier le document
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/documents/view.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour au document
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Informations du document -->
                <div class="document-info-card">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="file-icon-large">
                                <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-2"><?= htmlspecialchars($doc['nom_original']) ?></h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="opacity-75">
                                        <i class="fas fa-hdd me-1"></i>
                                        <?= formatFileSize($doc['taille_fichier']) ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="opacity-75">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d/m/Y', strtotime($doc['date_upload'])) ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="opacity-75">
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($doc['utilisateur_prenom'] . ' ' . $doc['utilisateur_nom']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification -->
                <div class="form-card">
                    <form method="POST" id="editForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="categorie_id" class="form-label">
                                        <i class="fas fa-folder me-2"></i>
                                        Catégorie
                                    </label>
                                    <select class="form-select" id="categorie_id" name="categorie_id">
                                        <option value="">Aucune catégorie</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" 
                                                    <?= $doc['categorie_id'] == $cat['id'] ? 'selected' : '' ?>
                                                    data-color="<?= $cat['couleur'] ?>">
                                                <?= htmlspecialchars($cat['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        Choisissez une catégorie pour organiser vos documents
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-tag me-2"></i>
                                        Catégorie actuelle
                                    </label>
                                    <div>
                                        <?php if ($doc['categorie_nom']): ?>
                                            <span class="badge badge-category" style="background-color: <?= $doc['categorie_couleur'] ?>">
                                                <?= htmlspecialchars($doc['categorie_nom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Aucune catégorie assignée</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="mots_cles" class="form-label">
                                <i class="fas fa-tags me-2"></i>
                                Mots-clés
                            </label>
                            <input type="text" class="form-control" id="mots_cles" name="mots_cles" 
                                   value="<?= htmlspecialchars($doc['mots_cles']) ?>"
                                   placeholder="Séparez les mots-clés par des virgules">
                            <div class="form-text">
                                Exemple: contrat, client, 2024
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Description détaillée du document..."><?= htmlspecialchars($doc['description']) ?></textarea>
                            <div class="form-text">
                                Décrivez le contenu et l'objectif de ce document
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="<?= APP_URL ?>/documents/view.php?id=<?= $doc['id'] ?>" 
                               class="btn btn-cancel">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aperçu de la catégorie sélectionnée
        document.getElementById('categorie_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const color = selectedOption.getAttribute('data-color');
            
            // Vous pouvez ajouter ici un aperçu visuel de la catégorie sélectionnée
            if (color && selectedOption.value) {
                this.style.borderColor = color;
            } else {
                this.style.borderColor = '';
            }
        });

        // Validation du formulaire
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const motsCles = document.getElementById('mots_cles').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if (!motsCles && !description) {
                e.preventDefault();
                alert('Veuillez remplir au moins les mots-clés ou la description pour justifier la modification.');
                return false;
            }
        });

        // Auto-resize textarea
        const textarea = document.getElementById('description');
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
