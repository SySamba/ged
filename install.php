<?php
/**
 * Script d'installation de DigiDocs
 * Ce script initialise la base de données et crée l'utilisateur admin
 */

require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';
$step = $_GET['step'] ?? 1;

// Vérifier si l'installation est déjà effectuée
function isInstalled() {
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Créer la base de données
        $db = new Database();
        $db->executeSqlFile(__DIR__ . '/database/schema.sql');
        
        // Mettre à jour le mot de passe admin
        $pdo = $db->getConnection();
        $hashedPassword = password_hash('Touba2021@', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'sambasy837@gmail.com'");
        $stmt->execute([$hashedPassword]);
        
        $success = 'Installation terminée avec succès !';
        $step = 3;
        
    } catch (Exception $e) {
        $error = 'Erreur lors de l\'installation : ' . $e->getMessage();
    }
}

if (isInstalled() && $step != 3) {
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .install-body {
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            position: relative;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step.pending {
            background: #e9ecef;
            color: #6c757d;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }
        .step.completed:not(:last-child)::after {
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <div class="mb-3">
                <i class="fas fa-file-alt fa-3x"></i>
            </div>
            <h2 class="mb-0"><?= APP_NAME ?></h2>
            <p class="mb-0 opacity-75">Installation du système</p>
        </div>
        
        <div class="install-body">
            <!-- Indicateur d'étapes -->
            <div class="step-indicator">
                <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'pending' ?>">1</div>
                <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'pending' ?>">2</div>
                <div class="step <?= $step >= 3 ? 'active' : 'pending' ?>">3</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <div class="text-center">
                    <h4>Bienvenue dans l'installation de DigiDocs</h4>
                    <p class="text-muted mb-4">
                        Ce système vous permettra de gérer vos documents électroniques de manière sécurisée et efficace.
                    </p>
                    
                    <div class="row text-start mb-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check text-success me-2"></i>Fonctionnalités incluses :</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-upload me-2"></i>Upload sécurisé de documents</li>
                                <li><i class="fas fa-search me-2"></i>Recherche avancée</li>
                                <li><i class="fas fa-tags me-2"></i>Catégorisation</li>
                                <li><i class="fas fa-users me-2"></i>Gestion des utilisateurs</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog text-primary me-2"></i>Modèles Canva :</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-file-contract me-2"></i>Contrats de travail</li>
                                <li><i class="fas fa-receipt me-2"></i>Factures commerciales</li>
                                <li><i class="fas fa-shopping-cart me-2"></i>Bons de commande</li>
                                <li><i class="fas fa-briefcase me-2"></i>Gestion d'emplois</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Prérequis :</strong> PHP 7.4+, MySQL 5.7+, Extensions PHP : PDO, GD, FileInfo
                    </div>
                    
                    <a href="?step=2" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>
                        Commencer l'installation
                    </a>
                </div>

            <?php elseif ($step == 2): ?>
                <div class="text-center">
                    <h4>Configuration de la base de données</h4>
                    <p class="text-muted mb-4">
                        Nous allons maintenant créer la base de données et les tables nécessaires.
                    </p>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette opération va créer/modifier la base de données "digidocs".
                        Assurez-vous que MySQL est démarré et accessible.
                    </div>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <h6>Compte administrateur par défaut :</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Email :</strong> sambasy837@gmail.com
                                </div>
                                <div class="col-md-6">
                                    <strong>Mot de passe :</strong> Touba2021@
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-database me-2"></i>
                            Installer la base de données
                        </button>
                    </form>
                </div>

            <?php elseif ($step == 3): ?>
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h4>Installation terminée !</h4>
                    <p class="text-muted mb-4">
                        DigiDocs a été installé avec succès. Vous pouvez maintenant vous connecter et commencer à utiliser le système.
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-user-shield me-2"></i>
                        <strong>Compte administrateur :</strong><br>
                        Email: sambasy837@gmail.com<br>
                        Mot de passe: Touba2021@
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Sécurité :</strong> Pensez à supprimer le fichier install.php après l'installation.
                    </div>
                    
                    <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Se connecter
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
