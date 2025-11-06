<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$job = new Job();

// Récupérer l'offre
$offre = $job->getOffreById($id);
if (!$offre) {
    $_SESSION['error'] = 'Offre d\'emploi introuvable';
    header('Location: ' . APP_URL . '/jobs/list.php');
    exit;
}

// Vérifier les permissions
if (!hasPermission('offres', 'update') && $offre['utilisateur_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Permission insuffisante pour modifier cette offre';
    header('Location: ' . APP_URL . '/jobs/list.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => sanitize($_POST['titre'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'entreprise' => sanitize($_POST['entreprise'] ?? ''),
        'lieu' => sanitize($_POST['lieu'] ?? ''),
        'type_contrat' => sanitize($_POST['type_contrat'] ?? ''),
        'salaire' => sanitize($_POST['salaire'] ?? ''),
        'competences_requises' => sanitize($_POST['competences_requises'] ?? ''),
        'date_limite' => !empty($_POST['date_limite']) ? $_POST['date_limite'] : null,
        'statut' => sanitize($_POST['statut'] ?? 'active')
    ];
    
    // Validation
    $errors = [];
    if (empty($data['titre'])) $errors[] = 'Le titre est obligatoire';
    if (empty($data['description'])) $errors[] = 'La description est obligatoire';
    if (empty($data['entreprise'])) $errors[] = 'Le nom de l\'entreprise est obligatoire';
    if (empty($data['type_contrat'])) $errors[] = 'Le type de contrat est obligatoire';
    
    if (empty($errors)) {
        $result = $job->updateOffre($id, $data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . APP_URL . '/jobs/view.php?id=' . $id);
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'offre - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .job-edit-header {
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
            margin-bottom: 2rem;
        }
        
        .company-icon {
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
        
        .badge-status {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        
        .required {
            color: #dc3545;
        }
        
        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
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
                        Modifier l'offre d'emploi
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/jobs/view.php?id=<?= $offre['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour à l'offre
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

                <!-- En-tête de l'offre -->
                <div class="job-edit-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="company-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-2"><?= htmlspecialchars($offre['titre']) ?></h3>
                            <h5 class="mb-3 opacity-75"><?= htmlspecialchars($offre['entreprise']) ?></h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-status bg-<?= getContractTypeColor($offre['type_contrat']) ?>">
                                    <?= htmlspecialchars($offre['type_contrat']) ?>
                                </span>
                                <span class="badge bg-<?= $offre['statut'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $offre['statut'] === 'active' ? 'Active' : 'Inactive' ?>
                                </span>
                                <span class="badge bg-info">
                                    Créée le <?= date('d/m/Y', strtotime($offre['date_creation'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification -->
                <div class="form-card">
                    <form method="POST" id="editForm">
                        <div class="row">
                            <!-- Informations principales -->
                            <div class="col-lg-8">
                                <h5 class="mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations principales
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="titre" class="form-label">
                                                <i class="fas fa-heading me-2"></i>
                                                Titre du poste <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="titre" name="titre" 
                                                   value="<?= htmlspecialchars($offre['titre']) ?>" 
                                                   placeholder="Ex: Développeur Full Stack" required maxlength="200">
                                            <div class="char-counter">
                                                <span id="titre-count">0</span>/200 caractères
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="entreprise" class="form-label">
                                                <i class="fas fa-building me-2"></i>
                                                Entreprise <span class="required">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="entreprise" name="entreprise" 
                                                   value="<?= htmlspecialchars($offre['entreprise']) ?>" 
                                                   placeholder="Nom de l'entreprise" required maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="type_contrat" class="form-label">
                                                <i class="fas fa-file-contract me-2"></i>
                                                Type de contrat <span class="required">*</span>
                                            </label>
                                            <select class="form-select" id="type_contrat" name="type_contrat" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="CDI" <?= $offre['type_contrat'] === 'CDI' ? 'selected' : '' ?>>CDI</option>
                                                <option value="CDD" <?= $offre['type_contrat'] === 'CDD' ? 'selected' : '' ?>>CDD</option>
                                                <option value="Stage" <?= $offre['type_contrat'] === 'Stage' ? 'selected' : '' ?>>Stage</option>
                                                <option value="Freelance" <?= $offre['type_contrat'] === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="lieu" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Lieu
                                            </label>
                                            <input type="text" class="form-control" id="lieu" name="lieu" 
                                                   value="<?= htmlspecialchars($offre['lieu']) ?>" 
                                                   placeholder="Ville, région...">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="salaire" class="form-label">
                                                <i class="fas fa-money-bill-wave me-2"></i>
                                                Salaire
                                            </label>
                                            <input type="text" class="form-control" id="salaire" name="salaire" 
                                                   value="<?= htmlspecialchars($offre['salaire']) ?>" 
                                                   placeholder="Ex: 35-45k€, À négocier...">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>
                                        Description du poste <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="6" 
                                              placeholder="Décrivez le poste, les missions, l'environnement de travail..." 
                                              required maxlength="2000"><?= htmlspecialchars($offre['description']) ?></textarea>
                                    <div class="char-counter">
                                        <span id="description-count">0</span>/2000 caractères
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="competences_requises" class="form-label">
                                        <i class="fas fa-cogs me-2"></i>
                                        Compétences requises
                                    </label>
                                    <textarea class="form-control" id="competences_requises" name="competences_requises" rows="4" 
                                              placeholder="Listez les compétences techniques et soft skills requises..." 
                                              maxlength="1000"><?= htmlspecialchars($offre['competences_requises']) ?></textarea>
                                    <div class="char-counter">
                                        <span id="competences-count">0</span>/1000 caractères
                                    </div>
                                </div>
                            </div>

                            <!-- Paramètres -->
                            <div class="col-lg-4">
                                <h5 class="mb-4">
                                    <i class="fas fa-cog me-2"></i>
                                    Paramètres
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="date_limite" class="form-label">
                                        <i class="fas fa-calendar-times me-2"></i>
                                        Date limite de candidature
                                    </label>
                                    <input type="date" class="form-control" id="date_limite" name="date_limite" 
                                           value="<?= $offre['date_limite'] ? date('Y-m-d', strtotime($offre['date_limite'])) : '' ?>"
                                           min="<?= date('Y-m-d') ?>">
                                    <div class="form-text">
                                        Laissez vide si pas de limite
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="statut" class="form-label">
                                        <i class="fas fa-toggle-on me-2"></i>
                                        Statut de l'offre
                                    </label>
                                    <select class="form-select" id="statut" name="statut">
                                        <option value="active" <?= $offre['statut'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $offre['statut'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="pourvue" <?= $offre['statut'] === 'pourvue' ? 'selected' : '' ?>>Pourvue</option>
                                    </select>
                                    <div class="form-text">
                                        Une offre inactive n'apparaît plus dans les recherches
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informations :</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Créée le <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?></li>
                                        <li>Modifiée le <?= date('d/m/Y à H:i', strtotime($offre['date_modification'])) ?></li>
                                        <li>Par <?= htmlspecialchars($offre['createur_prenom'] . ' ' . $offre['createur_nom']) ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end mt-4">
                            <a href="<?= APP_URL ?>/jobs/view.php?id=<?= $offre['id'] ?>" 
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
        // Compteurs de caractères
        function updateCharCounter(inputId, counterId, maxLength) {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);
            
            function updateCount() {
                const length = input.value.length;
                counter.textContent = length;
                
                if (length > maxLength * 0.9) {
                    counter.style.color = '#dc3545';
                } else if (length > maxLength * 0.7) {
                    counter.style.color = '#ffc107';
                } else {
                    counter.style.color = '#6c757d';
                }
            }
            
            input.addEventListener('input', updateCount);
            updateCount(); // Initial count
        }

        // Initialiser les compteurs
        updateCharCounter('titre', 'titre-count', 200);
        updateCharCounter('description', 'description-count', 2000);
        updateCharCounter('competences_requises', 'competences-count', 1000);

        // Auto-resize des textareas
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        document.getElementById('description').addEventListener('input', function() {
            autoResize(this);
        });

        document.getElementById('competences_requises').addEventListener('input', function() {
            autoResize(this);
        });

        // Validation du formulaire
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const entreprise = document.getElementById('entreprise').value.trim();
            const typeContrat = document.getElementById('type_contrat').value;
            
            if (!titre || !description || !entreprise || !typeContrat) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires (marqués d\'un *).');
                return false;
            }
            
            if (titre.length < 5) {
                e.preventDefault();
                alert('Le titre doit contenir au moins 5 caractères.');
                return false;
            }
            
            if (description.length < 50) {
                e.preventDefault();
                alert('La description doit contenir au moins 50 caractères.');
                return false;
            }
        });

        // Initialiser les auto-resize
        autoResize(document.getElementById('description'));
        autoResize(document.getElementById('competences_requises'));
    </script>
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
