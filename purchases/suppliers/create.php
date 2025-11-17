<?php
session_start();
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/Supplier.php';

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
        $supplier = new Supplier();
        $supplier->name = $_POST['name'];
        $supplier->contact_person = $_POST['contact_person'];
        $supplier->email = $_POST['email'];
        $supplier->phone = $_POST['phone'];
        $supplier->address = $_POST['address'];
        $supplier->city = $_POST['city'];
        $supplier->postal_code = $_POST['postal_code'];
        $supplier->country = $_POST['country'];
        $supplier->tax_number = $_POST['tax_number'];
        $supplier->payment_terms = $_POST['payment_terms'];
        $supplier->status = $_POST['status'];
        
        if ($supplier->create()) {
            $message = "Fournisseur créé avec succès.";
        } else {
            $error = "Erreur lors de la création du fournisseur.";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

$page_title = "Nouveau fournisseur";
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
                        <a href="list.php" class="btn btn-outline-secondary">
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
                                            <label for="name" class="form-label">Nom de l'entreprise *</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_person" class="form-label">Personne de contact</label>
                                            <input type="text" class="form-control" id="contact_person" name="contact_person">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">Ville</label>
                                            <input type="text" class="form-control" id="city" name="city">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="postal_code" class="form-label">Code postal</label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="country" class="form-label">Pays</label>
                                            <input type="text" class="form-control" id="country" name="country" value="France">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Paramètres commerciaux</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="tax_number" class="form-label">Numéro de TVA</label>
                                            <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                                   placeholder="FR12345678901">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="payment_terms" class="form-label">Délai de paiement (jours)</label>
                                            <select class="form-select" id="payment_terms" name="payment_terms">
                                                <option value="0">Comptant</option>
                                                <option value="15">15 jours</option>
                                                <option value="30" selected>30 jours</option>
                                                <option value="45">45 jours</option>
                                                <option value="60">60 jours</option>
                                                <option value="90">90 jours</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Statut</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Statut du fournisseur</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" selected>Actif</option>
                                            <option value="inactive">Inactif</option>
                                        </select>
                                        <div class="form-text">
                                            Seuls les fournisseurs actifs apparaîtront dans les listes de sélection.
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer le fournisseur
                                        </button>
                                        <a href="list.php" class="btn btn-outline-secondary">
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
                                            <small>Renseignez au minimum le nom de l'entreprise</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>L'email est important pour l'envoi des commandes</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Le délai de paiement sera utilisé par défaut</small>
                                        </li>
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Vous pourrez modifier ces informations plus tard</small>
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
