<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/PurchaseRequest.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = new User();
$user->read($_SESSION['user_id']);

$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $request = new PurchaseRequest();
        $request->requester_id = $_SESSION['user_id'];
        $request->department = $_POST['department'];
        $request->title = $_POST['title'];
        $request->description = $_POST['description'];
        $request->justification = $_POST['justification'];
        $request->priority = $_POST['priority'];
        $request->currency = $_POST['currency'];
        $request->requested_date = $_POST['requested_date'];
        $request->status = 'draft';
        
        if ($request->create()) {
            // Ajouter les articles
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['item_name']) && !empty($item['quantity'])) {
                        $request->addItem(
                            $item['category_id'] ?: null,
                            $item['item_name'],
                            $item['description'],
                            $item['quantity'],
                            $item['unit'],
                            $item['unit_price'] ?: 0
                        );
                    }
                }
            }
            
            $message = "Demande d'achat créée avec succès. Numéro: " . $request->request_number;
        } else {
            $error = "Erreur lors de la création de la demande d'achat.";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les catégories
$db = new Database();
$pdo = $db->getConnection();
$categories_query = "SELECT * FROM purchase_categories ORDER BY name";
$categories_stmt = $pdo->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Nouvelle demande d'achat";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .item-row {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        .remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus-circle me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" id="purchase-request-form">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label">Département *</label>
                                            <input type="text" class="form-control" id="department" name="department" 
                                                   value="<?php echo htmlspecialchars($user->department ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="priority" class="form-label">Priorité *</label>
                                            <select class="form-select" id="priority" name="priority" required>
                                                <option value="low">Faible</option>
                                                <option value="medium" selected>Moyenne</option>
                                                <option value="high">Élevée</option>
                                                <option value="urgent">Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titre de la demande *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="justification" class="form-label">Justification *</label>
                                        <textarea class="form-control" id="justification" name="justification" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="requested_date" class="form-label">Date souhaitée</label>
                                            <input type="date" class="form-control" id="requested_date" name="requested_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="currency" class="form-label">Devise</label>
                                            <select class="form-select" id="currency" name="currency">
                                                <option value="EUR" selected>EUR (€)</option>
                                                <option value="USD">USD ($)</option>
                                                <option value="GBP">GBP (£)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Articles -->
                            <div class="card mt-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Articles demandés</h5>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="items-container">
                                        <!-- Les articles seront ajoutés ici dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info me-2"></i>Informations</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Demandeur:</strong> <?php echo htmlspecialchars($user->username); ?></p>
                                    <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                                    <p><strong>Statut:</strong> <span class="badge bg-secondary">Brouillon</span></p>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer en brouillon
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="submitForApproval()">
                                            <i class="fas fa-paper-plane me-2"></i>Soumettre pour approbation
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Total estimé</h5>
                                </div>
                                <div class="card-body">
                                    <div class="h4 text-primary" id="total-amount">0,00 €</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCounter = 0;

        function addItem() {
            itemCounter++;
            const container = document.getElementById('items-container');
            const itemHtml = `
                <div class="item-row position-relative" id="item-${itemCounter}">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" onclick="removeItem(${itemCounter})">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom de l'article *</label>
                            <input type="text" class="form-control" name="items[${itemCounter}][item_name]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Catégorie</label>
                            <select class="form-select" name="items[${itemCounter}][category_id]">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="items[${itemCounter}][description]" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantité *</label>
                            <input type="number" step="0.01" class="form-control quantity-input" 
                                   name="items[${itemCounter}][quantity]" onchange="calculateItemTotal(${itemCounter})" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unité</label>
                            <input type="text" class="form-control" name="items[${itemCounter}][unit]" placeholder="pcs, kg, m...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix unitaire</label>
                            <input type="number" step="0.01" class="form-control price-input" 
                                   name="items[${itemCounter}][unit_price]" onchange="calculateItemTotal(${itemCounter})">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" id="item-total-${itemCounter}" readonly>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHtml);
        }

        function removeItem(itemId) {
            const item = document.getElementById(`item-${itemId}`);
            if (item) {
                item.remove();
                calculateGrandTotal();
            }
        }

        function calculateItemTotal(itemId) {
            const quantityInput = document.querySelector(`input[name="items[${itemId}][quantity]"]`);
            const priceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
            const totalInput = document.getElementById(`item-total-${itemId}`);
            
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            
            totalInput.value = total.toFixed(2) + ' €';
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            const totalInputs = document.querySelectorAll('[id^="item-total-"]');
            
            totalInputs.forEach(input => {
                const value = parseFloat(input.value.replace(' €', '')) || 0;
                grandTotal += value;
            });
            
            document.getElementById('total-amount').textContent = grandTotal.toFixed(2) + ' €';
        }

        function submitForApproval() {
            // Changer le statut à "submitted" avant soumission
            const form = document.getElementById('purchase-request-form');
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'submit_for_approval';
            statusInput.value = '1';
            form.appendChild(statusInput);
            form.submit();
        }

        // Ajouter un premier article par défaut
        document.addEventListener('DOMContentLoaded', function() {
            addItem();
        });
    </script>
</body>
</html>
