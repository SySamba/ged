<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Vérifier les permissions
if (!hasPermission('offres', 'create')) {
    $_SESSION['error'] = 'Permission insuffisante pour créer une offre d\'emploi';
    header('Location: ' . APP_URL . '/jobs/list.php');
    exit;
}

$offre = new Job();

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
        'date_limite' => $_POST['date_limite'] ?? null
    ];
    
    // Validation
    $errors = [];
    if (empty($data['titre'])) $errors[] = 'Le titre est obligatoire';
    if (empty($data['description'])) $errors[] = 'La description est obligatoire';
    if (empty($data['entreprise'])) $errors[] = 'Le nom de l\'entreprise est obligatoire';
    if (empty($data['type_contrat'])) $errors[] = 'Le type de contrat est obligatoire';
    
    if (empty($errors)) {
        $result = $offre->createOffre($data);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Offre d\'emploi créée avec succès';
            header('Location: ' . APP_URL . '/jobs/list.php');
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
    <title>Créer une Offre d'Emploi - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .create-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            margin: 2rem auto;
            max-width: 900px;
            overflow: hidden;
        }

        .create-header {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .create-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .create-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }

        .create-subtitle {
            margin-top: 0.5rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .form-container {
            padding: 3rem;
        }

        .form-section {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .form-section h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid var(--border-color);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .btn-modern {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-success-modern {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--border-color);
        }

        .btn-outline-modern:hover {
            background: var(--light-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .create-container {
                margin: 1rem;
            }
            
            .form-container {
                padding: 2rem 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation cachée pour mobile -->
    <div class="d-none d-md-block">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>
    </div>

    <!-- Container principal -->
    <div class="create-container">
        <!-- En-tête -->
        <div class="create-header">
            <div class="create-title">
                <i class="fas fa-plus-circle me-3"></i>
                Créer une Offre d'Emploi
            </div>
            <div class="create-subtitle">
                Publiez une nouvelle opportunité professionnelle
            </div>
        </div>

        <!-- Formulaire -->
        <div class="form-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" id="jobForm">
                <!-- Informations générales -->
                <div class="form-section">
                    <h4>
                        <i class="fas fa-info-circle"></i>
                        Informations Générales
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="titre" class="form-label">Titre du poste *</label>
                            <input type="text" class="form-control" id="titre" name="titre" 
                                   value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="type_contrat" class="form-label">Type de contrat *</label>
                            <select class="form-select" id="type_contrat" name="type_contrat" required>
                                <option value="">Sélectionner...</option>
                                <option value="CDI" <?= ($_POST['type_contrat'] ?? '') === 'CDI' ? 'selected' : '' ?>>CDI</option>
                                <option value="CDD" <?= ($_POST['type_contrat'] ?? '') === 'CDD' ? 'selected' : '' ?>>CDD</option>
                                <option value="Stage" <?= ($_POST['type_contrat'] ?? '') === 'Stage' ? 'selected' : '' ?>>Stage</option>
                                <option value="Freelance" <?= ($_POST['type_contrat'] ?? '') === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="entreprise" class="form-label">Entreprise *</label>
                            <input type="text" class="form-control" id="entreprise" name="entreprise" 
                                   value="<?= htmlspecialchars($_POST['entreprise'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lieu" class="form-label">Lieu</label>
                            <input type="text" class="form-control" id="lieu" name="lieu" 
                                   value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" 
                                   placeholder="Ex: Dakar, Sénégal">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salaire" class="form-label">Salaire</label>
                            <input type="text" class="form-control" id="salaire" name="salaire" 
                                   value="<?= htmlspecialchars($_POST['salaire'] ?? '') ?>" 
                                   placeholder="Ex: 500 000 - 800 000 FCFA">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_limite" class="form-label">Date limite de candidature</label>
                            <input type="date" class="form-control" id="date_limite" name="date_limite" 
                                   value="<?= $_POST['date_limite'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Description du poste -->
                <div class="form-section">
                    <h4>
                        <i class="fas fa-file-alt"></i>
                        Description du Poste
                    </h4>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description détaillée *</label>
                        <textarea class="form-control" id="description" name="description" rows="8" required 
                                  placeholder="Décrivez le poste, les missions, l'environnement de travail..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="competences_requises" class="form-label">Compétences requises</label>
                        <textarea class="form-control" id="competences_requises" name="competences_requises" rows="4" 
                                  placeholder="Listez les compétences, qualifications et expériences requises..."><?= htmlspecialchars($_POST['competences_requises'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="action-buttons">
                    <button type="submit" class="btn-modern btn-success-modern">
                        <i class="fas fa-save"></i>
                        Créer l'Offre
                    </button>
                    <a href="<?= APP_URL ?>/jobs/list.php" class="btn-modern btn-outline-modern">
                        <i class="fas fa-arrow-left"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.create-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);

            // Animation des sections
            const sections = document.querySelectorAll('.form-section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    section.style.transition = 'all 0.4s ease';
                    section.style.opacity = '1';
                    section.style.transform = 'translateX(0)';
                }, 300 + (index * 200));
            });
        });

        // Validation du formulaire
        document.getElementById('jobForm').addEventListener('submit', function(e) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const entreprise = document.getElementById('entreprise').value.trim();
            const typeContrat = document.getElementById('type_contrat').value;

            if (!titre || !description || !entreprise || !typeContrat) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires (*)');
                return false;
            }

            // Animation du bouton de soumission
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
