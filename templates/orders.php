<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$template = new Template();
$bonsCommande = $template->getAll('bon_commande');
$generatedDocs = $template->getGeneratedDocuments($_SESSION['user_id']);
$bonsGeneres = array_filter($generatedDocs, function($doc) {
    return $doc['modele_type'] === 'bon_commande';
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modèles de bons de commande - <?= APP_NAME ?></title>
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
                        <i class="fas fa-shopping-cart me-2"></i>
                        Modèles de bons de commande
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Tous les modèles
                        </a>
                    </div>
                </div>

                <!-- Modèles de bons de commande disponibles -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-invoice me-2"></i>
                            Modèles disponibles
                        </h4>
                        
                        <?php if (empty($bonsCommande)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                                <h5>Aucun modèle de bon de commande disponible</h5>
                                <p class="text-muted">Les modèles de bons de commande seront bientôt disponibles.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($bonsCommande as $bon): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card shadow h-100 border-warning">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-shopping-cart me-2"></i>
                                                    <?= htmlspecialchars($bon['nom']) ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="fas fa-file-invoice fa-4x text-warning"></i>
                                                </div>
                                                
                                                <?php if ($bon['description']): ?>
                                                    <p class="text-muted"><?= htmlspecialchars($bon['description']) ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-list me-1"></i>
                                                        Gestion des articles et fournisseurs
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="text-warning">
                                                        <i class="fas fa-check me-1"></i>
                                                        Articles • Références • Conditions
                                                    </small>
                                                </div>
                                                
                                                <div class="d-grid">
                                                    <a href="<?= APP_URL ?>/templates/generate.php?id=<?= $bon['id'] ?>" 
                                                       class="btn btn-warning">
                                                        <i class="fas fa-plus me-2"></i>
                                                        Créer un bon de commande
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted small">
                                                <i class="fas fa-calendar me-1"></i>
                                                Créé le <?= date('d/m/Y', strtotime($bon['date_creation'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mes bons de commande générés -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-alt me-2"></i>
                            Mes bons de commande générés
                            <span class="badge bg-secondary"><?= count($bonsGeneres) ?></span>
                        </h4>
                        
                        <?php if (empty($bonsGeneres)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                                    <h5>Aucun bon de commande généré</h5>
                                    <p class="text-muted">Utilisez les modèles ci-dessus pour créer vos premiers bons de commande.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Numéro de bon</th>
                                                    <th>Modèle utilisé</th>
                                                    <th>Date de génération</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bonsGeneres as $doc): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-shopping-cart me-2 text-warning"></i>
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

                <!-- Informations sur les bons de commande -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    À propos des bons de commande
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Fonctionnalités incluses :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i>Numérotation automatique</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Gestion des références produits</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Calcul automatique des totaux</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Conditions de livraison</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Informations requises :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-user me-2"></i>Informations de l'acheteur</li>
                                            <li><i class="fas fa-truck me-2"></i>Informations du fournisseur</li>
                                            <li><i class="fas fa-boxes me-2"></i>Liste des articles à commander</li>
                                            <li><i class="fas fa-calendar me-2"></i>Délais et conditions</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important :</strong> Vérifiez toujours les conditions de livraison et les délais 
                                    avant d'envoyer le bon de commande au fournisseur.
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
            if (confirm(`Êtes-vous sûr de vouloir supprimer le bon de commande "${name}" ?\n\nCette action est irréversible.`)) {
                window.location.href = `<?= APP_URL ?>/templates/delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
