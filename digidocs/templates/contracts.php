<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$template = new Template();
$contrats = $template->getAll('contrat');
$generatedDocs = $template->getGeneratedDocuments($_SESSION['user_id']);
$contratsGeneres = array_filter($generatedDocs, function($doc) {
    return $doc['modele_type'] === 'contrat';
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modèles de contrats - <?= APP_NAME ?></title>
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
                        <i class="fas fa-handshake me-2"></i>
                        Modèles de contrats
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Tous les modèles
                        </a>
                    </div>
                </div>

                <!-- Modèles de contrats disponibles -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-contract me-2"></i>
                            Modèles disponibles
                        </h4>
                        
                        <?php if (empty($contrats)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-handshake fa-4x text-muted mb-3"></i>
                                <h5>Aucun modèle de contrat disponible</h5>
                                <p class="text-muted">Les modèles de contrats seront bientôt disponibles.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($contrats as $contrat): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card shadow h-100 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-handshake me-2"></i>
                                                    <?= htmlspecialchars($contrat['nom']) ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fas fa-file-contract fa-4x text-primary"></i>
                                                </div>
                                                
                                                <?php if ($contrat['description']): ?>
                                                    <p class="text-muted"><?= htmlspecialchars($contrat['description']) ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Champs requis : nom, prénom, poste, salaire, dates
                                                    </small>
                                                </div>
                                                
                                                <div class="d-grid">
                                                    <a href="<?= APP_URL ?>/templates/generate.php?id=<?= $contrat['id'] ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-plus me-2"></i>
                                                        Créer un contrat
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted small">
                                                <i class="fas fa-calendar me-1"></i>
                                                Créé le <?= date('d/m/Y', strtotime($contrat['date_creation'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mes contrats générés -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-alt me-2"></i>
                            Mes contrats générés
                            <span class="badge bg-secondary"><?= count($contratsGeneres) ?></span>
                        </h4>
                        
                        <?php if (empty($contratsGeneres)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                                    <h5>Aucun contrat généré</h5>
                                    <p class="text-muted">Utilisez les modèles ci-dessus pour créer vos premiers contrats.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nom du contrat</th>
                                                    <th>Modèle utilisé</th>
                                                    <th>Date de génération</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contratsGeneres as $doc): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-file-contract me-2 text-primary"></i>
                                                            <?= htmlspecialchars($doc['nom_document']) ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($doc['modele_nom']) ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($doc['date_generation'])) ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?= APP_URL ?>/templates/view.php?id=<?= $doc['id'] ?>" 
                                                                   class="btn btn-outline-primary" title="Voir">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <?php if ($doc['chemin_pdf']): ?>
                                                                    <a href="<?= APP_URL ?>/templates/download.php?id=<?= $doc['id'] ?>" 
                                                                       class="btn btn-outline-success" title="Télécharger">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <button class="btn btn-outline-danger" 
                                                                        onclick="deleteDocument(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['nom_document']) ?>')" 
                                                                        title="Supprimer">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informations sur les contrats -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    À propos des contrats de travail
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Types de contrats disponibles :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i>Contrat à Durée Indéterminée (CDI)</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Contrat à Durée Déterminée (CDD)</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Contrat de stage</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Informations requises :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-user me-2"></i>Informations de l'employé</li>
                                            <li><i class="fas fa-building me-2"></i>Informations de l'entreprise</li>
                                            <li><i class="fas fa-briefcase me-2"></i>Poste et responsabilités</li>
                                            <li><i class="fas fa-money-bill-wave me-2"></i>Rémunération et avantages</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteDocument(id, name) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le contrat "${name}" ?\n\nCette action est irréversible.`)) {
                window.location.href = `<?= APP_URL ?>/templates/delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
