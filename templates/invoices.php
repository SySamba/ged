<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$template = new Template();
$factures = $template->getAll('facture');
$generatedDocs = $template->getGeneratedDocuments($_SESSION['user_id']);
$facturesGenerees = array_filter($generatedDocs, function($doc) {
    return $doc['modele_type'] === 'facture';
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modèles de factures - <?= APP_NAME ?></title>
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
                        <i class="fas fa-receipt me-2"></i>
                        Modèles de factures
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Tous les modèles
                        </a>
                    </div>
                </div>

                <!-- Modèles de factures disponibles -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-invoice me-2"></i>
                            Modèles disponibles
                        </h4>
                        
                        <?php if (empty($factures)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                                <h5>Aucun modèle de facture disponible</h5>
                                <p class="text-muted">Les modèles de factures seront bientôt disponibles.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($factures as $facture): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card shadow h-100 border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-receipt me-2"></i>
                                                    <?= htmlspecialchars($facture['nom']) ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fas fa-file-invoice fa-4x text-success"></i>
                                                </div>
                                                
                                                <?php if ($facture['description']): ?>
                                                    <p class="text-muted"><?= htmlspecialchars($facture['description']) ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calculator me-1"></i>
                                                        Calcul automatique des totaux et TVA
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="text-success">
                                                        <i class="fas fa-check me-1"></i>
                                                        Articles multiples • TVA • Totaux
                                                    </small>
                                                </div>
                                                
                                                <div class="d-grid">
                                                    <a href="<?= APP_URL ?>/templates/generate.php?id=<?= $facture['id'] ?>" 
                                                       class="btn btn-success">
                                                        <i class="fas fa-plus me-2"></i>
                                                        Créer une facture
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted small">
                                                <i class="fas fa-calendar me-1"></i>
                                                Créé le <?= date('d/m/Y', strtotime($facture['date_creation'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mes factures générées -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-alt me-2"></i>
                            Mes factures générées
                            <span class="badge bg-secondary"><?= count($facturesGenerees) ?></span>
                        </h4>
                        
                        <?php if (empty($facturesGenerees)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                                    <h5>Aucune facture générée</h5>
                                    <p class="text-muted">Utilisez les modèles ci-dessus pour créer vos premières factures.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Numéro de facture</th>
                                                    <th>Modèle utilisé</th>
                                                    <th>Date de génération</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($facturesGenerees as $doc): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-receipt me-2 text-success"></i>
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

                <!-- Informations sur les factures -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    À propos des factures commerciales
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Fonctionnalités incluses :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i>Numérotation automatique</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Calcul automatique des totaux</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Gestion de la TVA (18% par défaut)</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Articles multiples</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Informations requises :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-building me-2"></i>Informations de l'entreprise</li>
                                            <li><i class="fas fa-user me-2"></i>Informations du client</li>
                                            <li><i class="fas fa-list me-2"></i>Liste des articles/services</li>
                                            <li><i class="fas fa-percent me-2"></i>Taux de TVA applicable</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Astuce :</strong> Les totaux sont calculés automatiquement lors de la saisie des articles. 
                                    Vous pouvez modifier le taux de TVA selon vos besoins.
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
            if (confirm(`Êtes-vous sûr de vouloir supprimer la facture "${name}" ?\n\nCette action est irréversible.`)) {
                window.location.href = `<?= APP_URL ?>/templates/delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
