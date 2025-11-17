<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

$message = '';
$error = '';

// Récupérer les fournisseurs actifs
$database = new Database();
$pdo = $database->getConnection();

$suppliers_query = "SELECT * FROM suppliers WHERE status = 'active' ORDER BY name";
$suppliers_stmt = $pdo->prepare($suppliers_query);
$suppliers_stmt->execute();
$suppliers = $suppliers_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les demandes d'achat approuvées
$requests_query = "SELECT * FROM purchase_requests WHERE status = 'approved' ORDER BY request_number DESC";
$requests_stmt = $pdo->prepare($requests_query);
$requests_stmt->execute();
$requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Générer un numéro de commande
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM purchase_orders WHERE YEAR(created_at) = :year";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        $order_number = 'BC' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO purchase_orders 
                  SET order_number = :order_number,
                      supplier_id = :supplier_id,
                      request_id = :request_id,
                      order_date = :order_date,
                      delivery_date = :delivery_date,
                      delivery_address = :delivery_address,
                      payment_terms = :payment_terms,
                      currency = :currency,
                      notes = :notes,
                      status = 'draft'";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':order_number', $order_number);
        $stmt->bindParam(':supplier_id', $_POST['supplier_id']);
        $stmt->bindParam(':request_id', $_POST['request_id'] ?: null);
        $stmt->bindParam(':order_date', $_POST['order_date']);
        $stmt->bindParam(':delivery_date', $_POST['delivery_date']);
        $stmt->bindParam(':delivery_address', $_POST['delivery_address']);
        $stmt->bindParam(':payment_terms', $_POST['payment_terms']);
        $stmt->bindParam(':currency', $_POST['currency']);
        $stmt->bindParam(':notes', $_POST['notes']);
        
        if ($stmt->execute()) {
            $message = "Bon de commande créé avec succès. Numéro: " . $order_number;
        } else {
            $error = "Erreur lors de la création du bon de commande.";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

$page_title = "Nouveau bon de commande";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a href="list_simple.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
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

                <form method="POST">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="supplier_id" class="form-label">Fournisseur *</label>
                                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                                <option value="">Sélectionner un fournisseur</option>
                                                <?php foreach ($suppliers as $supplier): ?>
                                                    <option value="<?= $supplier['id'] ?>" <?= (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $supplier['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($supplier['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (empty($suppliers)): ?>
                                                <div class="form-text text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Aucun fournisseur actif. <a href="../suppliers/create_simple.php">Créer un fournisseur</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="request_id" class="form-label">Demande d'achat liée</label>
                                            <select class="form-select" id="request_id" name="request_id">
                                                <option value="">Aucune demande liée</option>
                                                <?php foreach ($requests as $request): ?>
                                                    <option value="<?= $request['id'] ?>">
                                                        <?= htmlspecialchars($request['request_number']) ?> - <?= htmlspecialchars($request['title']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="order_date" class="form-label">Date de commande *</label>
                                            <input type="date" class="form-control" id="order_date" name="order_date" 
                                                   value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="delivery_date" class="form-label">Date de livraison souhaitée</label>
                                            <input type="date" class="form-control" id="delivery_date" name="delivery_date">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="delivery_address" class="form-label">Adresse de livraison</label>
                                        <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="payment_terms" class="form-label">Conditions de paiement</label>
                                            <select class="form-select" id="payment_terms" name="payment_terms">
                                                <option value="0">Comptant</option>
                                                <option value="15">15 jours</option>
                                                <option value="30" selected>30 jours</option>
                                                <option value="45">45 jours</option>
                                                <option value="60">60 jours</option>
                                                <option value="90">90 jours</option>
                                            </select>
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
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes et conditions particulières</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
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
                                    <p><strong>Créé par:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></p>
                                    <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                                    <p><strong>Statut:</strong> <span class="badge bg-secondary">Brouillon</span></p>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer le bon de commande
                                        </button>
                                        <a href="list_simple.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Annuler
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Conseils</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Sélectionnez d'abord un fournisseur</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Liez à une demande d'achat si possible</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Précisez l'adresse de livraison</small>
                                        </li>
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Vous pourrez ajouter des articles après création</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
