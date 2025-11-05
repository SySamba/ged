<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$error = '';
$success = '';

// Récupérer les catégories
$category = new Category();
$categories = $category->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $document = new Document();
        
        $data = [
            'categorie_id' => !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
            'mots_cles' => sanitize($_POST['mots_cles'] ?? ''),
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        $result = $document->upload($_FILES['document'], $data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            // Rediriger vers la liste des documents
            header('Location: ' . APP_URL . '/documents/list.php');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Veuillez sélectionner un fichier à uploader';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploader un document - <?= APP_NAME ?></title>
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
                        <i class="fas fa-upload me-2"></i>
                        Uploader un document
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>
                            Mes documents
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-upload me-2"></i>
                                    Sélectionner un fichier
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                    <!-- Zone de drop -->
                                    <div class="upload-zone mb-4" id="uploadZone">
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>Glissez-déposez votre fichier ici</h5>
                                            <p class="text-muted mb-3">ou cliquez pour sélectionner</p>
                                            <input type="file" class="form-control d-none" id="document" name="document" 
                                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.bmp,.webp" required>
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('document').click()">
                                                <i class="fas fa-folder-open me-2"></i>
                                                Choisir un fichier
                                            </button>
                                        </div>
                                        <div class="upload-info d-none" id="uploadInfo">
                                            <div class="d-flex align-items-center">
                                                <div class="file-icon me-3" id="fileIcon">
                                                    <i class="fas fa-file"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1" id="fileName"></h6>
                                                    <small class="text-muted" id="fileSize"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="categorie_id" class="form-label">Catégorie</label>
                                            <select class="form-select" id="categorie_id" name="categorie_id">
                                                <option value="">Sélectionner une catégorie</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>" 
                                                            <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $cat['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cat['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="mots_cles" class="form-label">Mots-clés</label>
                                            <input type="text" class="form-control" id="mots_cles" name="mots_cles" 
                                                   value="<?= htmlspecialchars($_POST['mots_cles'] ?? '') ?>"
                                                   placeholder="Séparez par des virgules">
                                            <div class="form-text">Exemple: contrat, client, 2024</div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Description du document (optionnel)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-secondary me-md-2" onclick="resetForm()">
                                            <i class="fas fa-undo me-2"></i>
                                            Réinitialiser
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-upload me-2"></i>
                                            Uploader le document
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations
                                </h6>
                            </div>
                            <div class="card-body">
                                <h6>Types de fichiers autorisés :</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-file-pdf text-danger me-2"></i>PDF</li>
                                    <li><i class="fas fa-file-word text-primary me-2"></i>Word (DOC, DOCX)</li>
                                    <li><i class="fas fa-file-excel text-success me-2"></i>Excel (XLS, XLSX)</li>
                                    <li><i class="fas fa-file-image text-warning me-2"></i>Images (JPG, PNG, GIF)</li>
                                </ul>
                                
                                <hr>
                                
                                <h6>Limites :</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-weight-hanging me-2"></i>Taille max: <?= formatFileSize(MAX_FILE_SIZE) ?></li>
                                    <li><i class="fas fa-shield-alt me-2"></i>Stockage sécurisé</li>
                                    <li><i class="fas fa-search me-2"></i>Recherche intégrée</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card shadow mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Conseils
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Utilisez des noms de fichiers descriptifs
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Ajoutez des mots-clés pour faciliter la recherche
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Choisissez la bonne catégorie
                                    </li>
                                    <li>
                                        <i class="fas fa-check text-success me-2"></i>
                                        Ajoutez une description détaillée
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('document');
        const uploadContent = document.querySelector('.upload-content');
        const uploadInfo = document.getElementById('uploadInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');
        const submitBtn = document.getElementById('submitBtn');

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        // Click to select
        uploadZone.addEventListener('click', (e) => {
            if (e.target === uploadZone || e.target.closest('.upload-content')) {
                fileInput.click();
            }
        });

        // File input change
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                // Vérifier la taille
                if (file.size > <?= MAX_FILE_SIZE ?>) {
                    alert('Le fichier est trop volumineux (max: <?= formatFileSize(MAX_FILE_SIZE) ?>)');
                    clearFile();
                    return;
                }

                // Afficher les informations du fichier
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                
                // Icône selon le type
                const extension = file.name.split('.').pop().toLowerCase();
                const iconClass = getFileIcon(extension);
                fileIcon.innerHTML = `<i class="fas fa-${iconClass}"></i>`;
                fileIcon.className = `file-icon ${extension}`;

                // Masquer la zone de drop et afficher les infos
                uploadContent.classList.add('d-none');
                uploadInfo.classList.remove('d-none');
                
                // Activer le bouton submit
                submitBtn.disabled = false;
            }
        }

        function clearFile() {
            fileInput.value = '';
            uploadContent.classList.remove('d-none');
            uploadInfo.classList.add('d-none');
            submitBtn.disabled = true;
        }

        function resetForm() {
            document.getElementById('uploadForm').reset();
            clearFile();
        }

        function getFileIcon(extension) {
            const icons = {
                'pdf': 'file-pdf',
                'doc': 'file-word',
                'docx': 'file-word',
                'xls': 'file-excel',
                'xlsx': 'file-excel',
                'jpg': 'file-image',
                'jpeg': 'file-image',
                'png': 'file-image',
                'gif': 'file-image',
                'bmp': 'file-image',
                'webp': 'file-image'
            };
            return icons[extension] || 'file';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Désactiver le bouton submit au chargement
        submitBtn.disabled = true;
    </script>
</body>
</html>
