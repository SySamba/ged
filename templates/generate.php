<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$template = new Template();
$modele = $template->getById($id);

if (!$modele) {
    $_SESSION['error'] = 'Modèle introuvable';
    header('Location: ' . APP_URL . '/templates/list.php');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    
    // Récupérer toutes les données du formulaire
    foreach ($_POST as $key => $value) {
        if ($key !== 'items' && $key !== 'generate_pdf') {
            $data[$key] = sanitize($value);
        }
    }
    
    // Traitement spécial pour les items (factures, bons de commande)
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $items = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['designation'])) {
                $items[] = [
                    'designation' => sanitize($item['designation']),
                    'quantite' => (float)($item['quantite'] ?? 0),
                    'prix_unitaire' => (float)($item['prix_unitaire'] ?? 0),
                    'total' => (float)($item['quantite'] ?? 0) * (float)($item['prix_unitaire'] ?? 0)
                ];
            }
        }
        $data['items'] = $items;
        
        // Calculer les totaux pour les factures
        if ($modele['type'] === 'facture') {
            $sousTotal = array_sum(array_column($items, 'total'));
            $tauxTva = (float)($data['taux_tva'] ?? 0);
            $montantTva = $sousTotal * ($tauxTva / 100);
            $totalTtc = $sousTotal + $montantTva;
            
            $data['sous_total'] = number_format($sousTotal, 0, ',', ' ');
            $data['montant_tva'] = number_format($montantTva, 0, ',', ' ');
            $data['total_ttc'] = number_format($totalTtc, 0, ',', ' ');
        }
        
        // Calculer le total pour les bons de commande
        if ($modele['type'] === 'bon_commande') {
            $total = array_sum(array_column($items, 'total'));
            $data['total'] = number_format($total, 0, ',', ' ');
        }
    }
    
    $data['generate_pdf'] = isset($_POST['generate_pdf']);
    
    $result = $template->generateDocument($id, $data);
    
    if ($result['success']) {
        $success = $result['message'];
        $generatedHtml = $result['html'];
        $documentId = $result['document_id'];
        $gedDocumentId = $result['ged_document_id'];
        
        // Redirection automatique vers la liste des documents après 3 secondes
        $_SESSION['success'] = $success . " Le document a été ajouté à votre liste de documents.";
        header("refresh:3;url=" . APP_URL . "/documents/list.php");
    } else {
        $error = $result['message'];
    }
}

// Décoder les champs variables du modèle
$champsVariables = json_decode($modele['champs_variables'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générer - <?= htmlspecialchars($modele['nom']) ?> - <?= APP_NAME ?></title>
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
                        <i class="fas fa-<?= getTypeIcon($modele['type']) ?> me-2"></i>
                        <?= htmlspecialchars($modele['nom']) ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
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
                        <?php if (isset($documentId)): ?>
                            <div class="mt-2">
                                <a href="<?= APP_URL ?>/templates/view.php?id=<?= $documentId ?>" class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-eye me-1"></i>Voir le document
                                </a>
                                <a href="<?= APP_URL ?>/documents/list.php" class="btn btn-sm btn-success me-2">
                                    <i class="fas fa-folder me-1"></i>Voir dans mes documents
                                </a>
                                <a href="<?= APP_URL ?>/templates/list.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>Mes modèles
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Remplir le modèle
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="templateForm">
                                    <div class="mb-3">
                                        <label for="nom_document" class="form-label">Nom du document *</label>
                                        <input type="text" class="form-control" id="nom_document" name="nom_document" 
                                               value="<?= htmlspecialchars($_POST['nom_document'] ?? $modele['nom'] . '_' . date('Y-m-d')) ?>" required>
                                    </div>

                                    <?php foreach ($champsVariables as $champ => $type): ?>
                                        <?php if ($champ === 'items'): continue; endif; ?>
                                        
                                        <div class="mb-3">
                                            <label for="<?= $champ ?>" class="form-label">
                                                <?= ucfirst(str_replace('_', ' ', $champ)) ?>
                                                <?php if (in_array($type, ['text', 'date', 'number'])): ?> *<?php endif; ?>
                                            </label>
                                            
                                            <?php if ($type === 'text'): ?>
                                                <input type="text" class="form-control" id="<?= $champ ?>" name="<?= $champ ?>" 
                                                       value="<?= htmlspecialchars($_POST[$champ] ?? '') ?>" required>
                                            
                                            <?php elseif ($type === 'date'): ?>
                                                <input type="date" class="form-control" id="<?= $champ ?>" name="<?= $champ ?>" 
                                                       value="<?= htmlspecialchars($_POST[$champ] ?? '') ?>" required>
                                            
                                            <?php elseif ($type === 'number'): ?>
                                                <input type="number" class="form-control" id="<?= $champ ?>" name="<?= $champ ?>" 
                                                       value="<?= htmlspecialchars($_POST[$champ] ?? '') ?>" step="0.01" required>
                                            
                                            <?php elseif ($type === 'select' && $champ === 'type_contrat'): ?>
                                                <select class="form-select" id="<?= $champ ?>" name="<?= $champ ?>" required>
                                                    <option value="">Sélectionner...</option>
                                                    <option value="indéterminée" <?= ($_POST[$champ] ?? '') === 'indéterminée' ? 'selected' : '' ?>>Durée indéterminée (CDI)</option>
                                                    <option value="déterminée" <?= ($_POST[$champ] ?? '') === 'déterminée' ? 'selected' : '' ?>>Durée déterminée (CDD)</option>
                                                    <option value="stage" <?= ($_POST[$champ] ?? '') === 'stage' ? 'selected' : '' ?>>Stage</option>
                                                </select>
                                            
                                            <?php else: ?>
                                                <input type="text" class="form-control" id="<?= $champ ?>" name="<?= $champ ?>" 
                                                       value="<?= htmlspecialchars($_POST[$champ] ?? '') ?>">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Section items pour factures et bons de commande -->
                                    <?php if (isset($champsVariables['items'])): ?>
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6>Articles/Services</h6>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                                                    <i class="fas fa-plus me-1"></i>Ajouter un article
                                                </button>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="itemsTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Désignation</th>
                                                            <th width="100">Quantité</th>
                                                            <th width="120">Prix unitaire</th>
                                                            <th width="120">Total</th>
                                                            <th width="50">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="itemsBody">
                                                        <?php if (isset($_POST['items']) && is_array($_POST['items'])): ?>
                                                            <?php foreach ($_POST['items'] as $index => $item): ?>
                                                                <tr>
                                                                    <td><input type="text" class="form-control" name="items[<?= $index ?>][designation]" value="<?= htmlspecialchars($item['designation'] ?? '') ?>" required></td>
                                                                    <td><input type="number" class="form-control quantity" name="items[<?= $index ?>][quantite]" value="<?= $item['quantite'] ?? '' ?>" step="0.01" onchange="calculateTotal(this)" required></td>
                                                                    <td><input type="number" class="form-control price" name="items[<?= $index ?>][prix_unitaire]" value="<?= $item['prix_unitaire'] ?? '' ?>" step="0.01" onchange="calculateTotal(this)" required></td>
                                                                    <td><input type="number" class="form-control total" name="items[<?= $index ?>][total]" value="<?= $item['total'] ?? '' ?>" readonly></td>
                                                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)"><i class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td><input type="text" class="form-control" name="items[0][designation]" required></td>
                                                                <td><input type="number" class="form-control quantity" name="items[0][quantite]" step="0.01" onchange="calculateTotal(this)" required></td>
                                                                <td><input type="number" class="form-control price" name="items[0][prix_unitaire]" step="0.01" onchange="calculateTotal(this)" required></td>
                                                                <td><input type="number" class="form-control total" name="items[0][total]" readonly></td>
                                                                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)"><i class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <?php if ($modele['type'] === 'facture'): ?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="taux_tva" class="form-label">Taux TVA (%)</label>
                                                    <input type="number" class="form-control" id="taux_tva" name="taux_tva" 
                                                           value="<?= htmlspecialchars($_POST['taux_tva'] ?? '18') ?>" step="0.01">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="generate_pdf" name="generate_pdf" 
                                               <?= isset($_POST['generate_pdf']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="generate_pdf">
                                            Générer aussi un fichier PDF
                                        </label>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-secondary me-md-2" onclick="resetForm()">
                                            <i class="fas fa-undo me-2"></i>
                                            Réinitialiser
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-file-alt me-2"></i>
                                            Générer le document
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
                                    Informations sur le modèle
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Type :</strong>
                                    <span class="badge bg-<?= getTypeColor($modele['type']) ?>">
                                        <?= getTypeLabel($modele['type']) ?>
                                    </span>
                                </div>
                                
                                <?php if ($modele['description']): ?>
                                    <div class="mb-3">
                                        <strong>Description :</strong><br>
                                        <?= htmlspecialchars($modele['description']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <strong>Champs requis :</strong>
                                    <ul class="list-unstyled mt-2">
                                        <?php foreach ($champsVariables as $champ => $type): ?>
                                            <?php if ($champ !== 'items'): ?>
                                                <li>
                                                    <i class="fas fa-check text-success me-1"></i>
                                                    <?= ucfirst(str_replace('_', ' ', $champ)) ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (isset($champsVariables['items'])): ?>
                                            <li>
                                                <i class="fas fa-check text-success me-1"></i>
                                                Articles/Services
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($generatedHtml)): ?>
                            <div class="card shadow mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-eye me-2"></i>
                                        Aperçu du document
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="border p-3" style="max-height: 400px; overflow-y: auto; font-size: 0.8em;">
                                        <?= $generatedHtml ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemIndex = <?= isset($_POST['items']) ? count($_POST['items']) : 1 ?>;

        function addItem() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" class="form-control" name="items[${itemIndex}][designation]" required></td>
                <td><input type="number" class="form-control quantity" name="items[${itemIndex}][quantite]" step="0.01" onchange="calculateTotal(this)" required></td>
                <td><input type="number" class="form-control price" name="items[${itemIndex}][prix_unitaire]" step="0.01" onchange="calculateTotal(this)" required></td>
                <td><input type="number" class="form-control total" name="items[${itemIndex}][total]" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(row);
            itemIndex++;
        }

        function removeItem(button) {
            const row = button.closest('tr');
            if (document.querySelectorAll('#itemsBody tr').length > 1) {
                row.remove();
            } else {
                alert('Vous devez conserver au moins un article.');
            }
        }

        function calculateTotal(input) {
            const row = input.closest('tr');
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const total = quantity * price;
            row.querySelector('.total').value = total.toFixed(2);
        }

        function resetForm() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
                document.getElementById('templateForm').reset();
                // Réinitialiser les totaux
                document.querySelectorAll('.total').forEach(input => input.value = '');
            }
        }

        // Calculer les totaux au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.quantity, .price').forEach(input => {
                if (input.value) {
                    calculateTotal(input);
                }
            });
        });
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
