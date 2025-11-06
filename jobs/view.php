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

// Récupérer les candidatures si l'utilisateur a les permissions
$candidatures = [];
if (hasPermission('offres', 'read') || $offre['utilisateur_id'] == $_SESSION['user_id']) {
    $candidatures = $job->getCandidatures($id);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($offre['titre']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .job-info-card {
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
        
        .badge-contract {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .info-item i {
            width: 20px;
            margin-right: 0.75rem;
            color: #667eea;
        }
        
        .description-section {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .candidatures-section {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
        }
        
        .btn-action {
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .expired-notice {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
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
                        Détails de l'offre
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php if (hasPermission('offres', 'update') || $offre['utilisateur_id'] == $_SESSION['user_id']): ?>
                                <a href="<?= APP_URL ?>/jobs/edit.php?id=<?= $offre['id'] ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Modifier
                                </a>
                            <?php endif; ?>
                            <?php if (hasPermission('offres', 'delete') || $offre['utilisateur_id'] == $_SESSION['user_id']): ?>
                                <a href="<?= APP_URL ?>/jobs/delete.php?id=<?= $offre['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')">
                                    <i class="fas fa-trash-alt me-1"></i>
                                    Supprimer
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="<?= APP_URL ?>/jobs/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- En-tête de l'offre -->
                <div class="job-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="company-icon">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h2 class="mb-2"><?= htmlspecialchars($offre['titre']) ?></h2>
                            <h4 class="mb-3 opacity-75"><?= htmlspecialchars($offre['entreprise']) ?></h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-contract bg-<?= getContractTypeColor($offre['type_contrat']) ?>">
                                    <?= htmlspecialchars($offre['type_contrat']) ?>
                                </span>
                                <span class="badge bg-<?= $offre['statut'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $offre['statut'] === 'active' ? 'Active' : 'Inactive' ?>
                                </span>
                                <?php if (count($candidatures) > 0): ?>
                                    <span class="badge bg-info">
                                        <?= count($candidatures) ?> candidature<?= count($candidatures) > 1 ? 's' : '' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vérification de la date limite -->
                <?php if ($offre['date_limite'] && strtotime($offre['date_limite']) < time()): ?>
                    <div class="expired-notice">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Cette offre a expiré</strong> - Date limite de candidature dépassée
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Informations de l'offre -->
                    <div class="col-lg-8">
                        <div class="job-info-card">
                            <h5 class="mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations générales
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($offre['lieu']): ?>
                                        <div class="info-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><strong>Lieu :</strong> <?= htmlspecialchars($offre['lieu']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($offre['salaire']): ?>
                                        <div class="info-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><strong>Salaire :</strong> <?= htmlspecialchars($offre['salaire']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-calendar-plus"></i>
                                        <span><strong>Publié le :</strong> <?= date('d/m/Y à H:i', strtotime($offre['date_creation'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <?php if ($offre['date_limite']): ?>
                                        <div class="info-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <span><strong>Date limite :</strong> <?= date('d/m/Y', strtotime($offre['date_limite'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-user"></i>
                                        <span><strong>Publié par :</strong> <?= htmlspecialchars($offre['createur_prenom'] . ' ' . $offre['createur_nom']) ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <span><strong>Dernière modification :</strong> <?= date('d/m/Y à H:i', strtotime($offre['date_modification'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="description-section">
                            <h5 class="mb-3">
                                <i class="fas fa-align-left me-2"></i>
                                Description du poste
                            </h5>
                            <div class="text-justify">
                                <?= nl2br(htmlspecialchars($offre['description'])) ?>
                            </div>
                        </div>

                        <!-- Compétences requises -->
                        <?php if ($offre['competences_requises']): ?>
                            <div class="description-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-cogs me-2"></i>
                                    Compétences requises
                                </h5>
                                <div class="text-justify">
                                    <?= nl2br(htmlspecialchars($offre['competences_requises'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar avec actions et candidatures -->
                    <div class="col-lg-4">
                        <!-- Actions rapides -->
                        <div class="job-info-card">
                            <h6 class="mb-3">Actions rapides</h6>
                            <div class="d-grid gap-2">
                                <?php if (hasPermission('offres', 'update') || $offre['utilisateur_id'] == $_SESSION['user_id']): ?>
                                    <a href="<?= APP_URL ?>/jobs/edit.php?id=<?= $offre['id'] ?>" 
                                       class="btn btn-warning btn-action">
                                        <i class="fas fa-edit me-2"></i>
                                        Modifier l'offre
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-primary btn-action" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>
                                    Imprimer
                                </button>
                                
                                <button class="btn btn-info btn-action" onclick="shareJob()">
                                    <i class="fas fa-share me-2"></i>
                                    Partager
                                </button>
                            </div>
                        </div>

                        <!-- Candidatures (si permissions) -->
                        <?php if (!empty($candidatures)): ?>
                            <div class="candidatures-section">
                                <h6 class="mb-3">
                                    <i class="fas fa-users me-2"></i>
                                    Candidatures (<?= count($candidatures) ?>)
                                </h6>
                                
                                <?php foreach (array_slice($candidatures, 0, 5) as $candidature): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                        <div>
                                            <strong><?= htmlspecialchars($candidature['prenom'] . ' ' . $candidature['nom']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($candidature['email']) ?></small>
                                        </div>
                                        <div>
                                            <span class="status-badge bg-<?= getCandidatureStatusColor($candidature['statut']) ?>">
                                                <?= ucfirst($candidature['statut']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($candidatures) > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="<?= APP_URL ?>/jobs/candidatures.php?offre_id=<?= $offre['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            Voir toutes les candidatures
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function shareJob() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($offre['titre']) ?>',
                    text: 'Offre d\'emploi chez <?= htmlspecialchars($offre['entreprise']) ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copier l'URL dans le presse-papiers
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Lien copié dans le presse-papiers !');
                });
            }
        }
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

function getCandidatureStatusColor($status) {
    $colors = [
        'en_attente' => 'warning',
        'acceptee' => 'success',
        'refusee' => 'danger',
        'en_cours' => 'info'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
