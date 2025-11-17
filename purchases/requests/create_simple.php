<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connexion directe à la base de données
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Générer un numéro de demande
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM purchase_requests WHERE YEAR(created_at) = :year";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        $request_number = 'DA' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Insérer la demande d'achat
        $query = "INSERT INTO purchase_requests 
                  SET request_number = :request_number,
                      requester_id = :requester_id,
                      department = :department,
                      title = :title,
                      description = :description,
                      justification = :justification,
                      priority = :priority,
                      currency = :currency,
                      requested_date = :requested_date,
                      status = 'draft'";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':request_number', $request_number);
        $stmt->bindParam(':requester_id', $_SESSION['user_id']);
        $stmt->bindParam(':department', $_POST['department']);
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':justification', $_POST['justification']);
        $stmt->bindParam(':priority', $_POST['priority']);
        $stmt->bindParam(':currency', $_POST['currency']);
        $stmt->bindParam(':requested_date', $_POST['requested_date']);
        
        if ($stmt->execute()) {
            $message = "Demande d'achat créée avec succès. Numéro: " . $request_number;
        } else {
            $error = "Erreur lors de la création de la demande d'achat.";
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

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
                                            <label for="department" class="form-label">Département *</label>
                                            <input type="text" class="form-control" id="department" name="department" required>
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
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info me-2"></i>Informations</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Demandeur:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></p>
                                    <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                                    <p><strong>Statut:</strong> <span class="badge bg-secondary">Brouillon</span></p>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer la demande
                                        </button>
                                        <a href="../index.php" class="btn btn-outline-secondary">
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
                                            <small>Soyez précis dans le titre</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Justifiez clairement le besoin</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Indiquez une date réaliste</small>
                                        </li>
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Vous pourrez ajouter des articles plus tard</small>
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
