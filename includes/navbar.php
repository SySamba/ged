<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= APP_URL ?>/dashboard.php">
            <i class="fas fa-file-alt me-2"></i>
            <?= APP_NAME ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/documents/list.php">
                        <i class="fas fa-folder me-1"></i>
                        Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/templates/list.php">
                        <i class="fas fa-file-contract me-1"></i>
                        Modèles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/jobs/list.php">
                        <i class="fas fa-briefcase me-1"></i>
                        Emplois
                    </a>
                </li>
                <?php if (hasPermission('users', 'read')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/admin/users.php">
                        <i class="fas fa-users me-1"></i>
                        Utilisateurs
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= APP_URL ?>/profile.php">
                                <i class="fas fa-user me-2"></i>
                                Mon profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= APP_URL ?>/settings.php">
                                <i class="fas fa-cog me-2"></i>
                                Paramètres
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= APP_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
