<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$template = new Template();
$templates = $template->getAll();
$generatedDocs = $template->getGeneratedDocuments($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modèles Canva - <?= APP_NAME ?></title>
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
                        <i class="fas fa-file-contract me-2"></i>
                        Modèles Canva
                    </h1>
                </div>

                <!-- Modèles disponibles -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-templates me-2"></i>
                            Modèles disponibles
                        </h4>
                        
                        <div class="row">
                            <?php foreach ($templates as $tpl): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card shadow h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?= htmlspecialchars($tpl['nom']) ?></h6>
                                            <span class="badge bg-<?= getTypeColor($tpl['type']) ?>">
                                                <?= getTypeLabel($tpl['type']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <i class="fas fa-<?= getTypeIcon($tpl['type']) ?> fa-3x text-<?= getTypeColor($tpl['type']) ?>"></i>
                                            </div>
                                            
                                            <?php if ($tpl['description']): ?>
                                                <p class="text-muted small"><?= htmlspecialchars($tpl['description']) ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="d-grid">
                                                <a href="<?= APP_URL ?>/templates/generate.php?id=<?= $tpl['id'] ?>" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>
                                                    Utiliser ce modèle
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            Créé le <?= date('d/m/Y', strtotime($tpl['date_creation'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Documents générés -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-file-alt me-2"></i>
                            Mes documents générés
                            <span class="badge bg-secondary"><?= count($generatedDocs) ?></span>
                        </h4>
                        
                        <?php if (empty($generatedDocs)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                <h5>Aucun document généré</h5>
                                <p class="text-muted">Utilisez les modèles ci-dessus pour créer vos premiers documents.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom du document</th>
                                            <th>Modèle</th>
                                            <th>Type</th>
                                            <th>Date de génération</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($generatedDocs as $doc): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-<?= getTypeIcon($doc['modele_type']) ?> me-2 text-<?= getTypeColor($doc['modele_type']) ?>"></i>
                                                    <?= htmlspecialchars($doc['nom_document']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($doc['modele_nom']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= getTypeColor($doc['modele_type']) ?>">
                                                        <?= getTypeLabel($doc['modele_type']) ?>
                                                    </span>
                                                </td>
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
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteDocument(id, name) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le document "${name}" ?\n\nCette action est irréversible.`)) {
                window.location.href = `<?= APP_URL ?>/templates/delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>

<?php
function getTypeColor($type) {
    $colors = [
        'contrat' => 'primary',
        'facture' => 'success',
        'bon_commande' => 'warning'
    ];
    return $colors[$type] ?? 'secondary';
}

function getTypeLabel($type) {
    $labels = [
        'contrat' => 'Contrat',
        'facture' => 'Facture',
        'bon_commande' => 'Bon de commande'
    ];
    return $labels[$type] ?? $type;
}

function getTypeIcon($type) {
    $icons = [
        'contrat' => 'handshake',
        'facture' => 'receipt',
        'bon_commande' => 'shopping-cart'
    ];
    return $icons[$type] ?? 'file';
}
?>
