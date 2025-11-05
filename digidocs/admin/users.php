<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Vérifier les permissions admin
if (!hasPermission('users', 'read')) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$user = new User();
$users = $user->getAll();
$stats = $user->getStatistics();

$error = '';
$success = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_status' && $userId) {
        $targetUser = $user->getById($userId);
        if ($targetUser) {
            $newStatus = $targetUser['actif'] ? 0 : 1;
            $result = $user->update($userId, ['actif' => $newStatus]);
            
            if ($result['success']) {
                $success = 'Statut utilisateur mis à jour';
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Recharger les utilisateurs après modification
    $users = $user->getAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - <?= APP_NAME ?></title>
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
                        <i class="fas fa-users me-2"></i>
                        Gestion des utilisateurs
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter un utilisateur
                        </button>
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
                    </div>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total utilisateurs
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $stats['total_users'] ?? 0 ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Connexions récentes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $stats['recent_logins'] ?? 0 ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Administrateurs
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $admins = array_filter($stats['by_role'] ?? [], function($r) { return $r['role'] === 'admin'; });
                                            echo !empty($admins) ? $admins[0]['count'] : 0;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Employés
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $employes = array_filter($stats['by_role'] ?? [], function($r) { return $r['role'] === 'employe'; });
                                            echo !empty($employes) ? $employes[0]['count'] : 0;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Liste des utilisateurs</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Nom complet</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Dernière connexion</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar me-3">
                                                        <div class="avatar-initial rounded-circle bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                                            <?= strtoupper(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1)) ?>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                                    <i class="fas fa-<?= $u['role'] === 'admin' ? 'user-shield' : 'user' ?> me-1"></i>
                                                    <?= ucfirst($u['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $u['actif'] ? 'success' : 'danger' ?>">
                                                    <i class="fas fa-<?= $u['actif'] ? 'check-circle' : 'times-circle' ?> me-1"></i>
                                                    <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($u['derniere_connexion']): ?>
                                                    <?= date('d/m/Y H:i', strtotime($u['derniere_connexion'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Jamais</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($u['date_creation'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($u['email'] !== 'sambasy837@gmail.com'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-<?= $u['actif'] ? 'warning' : 'success' ?>" 
                                                                    title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>">
                                                                <i class="fas fa-<?= $u['actif'] ? 'pause' : 'play' ?>"></i>
                                                            </button>
                                                        </form>
                                                        <button class="btn btn-outline-primary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" title="Supprimer" 
                                                                onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Compte principal</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal d'ajout d'utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= APP_URL ?>/admin/add_user.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="employe">Employé</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteUser(id, name) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${name}" ?\n\nCette action est irréversible.`)) {
                window.location.href = `<?= APP_URL ?>/admin/delete_user.php?id=${id}`;
            }
        }
    </script>

    <style>
        .avatar-initial {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
    </style>
</body>
</html>
