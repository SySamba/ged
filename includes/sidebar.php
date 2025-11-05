<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Documents</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/documents/upload.php">
                    <i class="fas fa-upload me-2"></i>
                    Uploader
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/documents/list.php">
                    <i class="fas fa-list me-2"></i>
                    Mes documents
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/documents/list.php">
                    <i class="fas fa-search me-2"></i>
                    Rechercher
                </a>
            </li>
            
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Modèles</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/templates/list.php">
                    <i class="fas fa-file-contract me-2"></i>
                    Tous les modèles
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/templates/contracts.php">
                    <i class="fas fa-handshake me-2"></i>
                    Contrats
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/templates/invoices.php">
                    <i class="fas fa-receipt me-2"></i>
                    Factures
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/templates/orders.php">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Bons de commande
                </a>
            </li>
            
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Emploi</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/jobs/list.php">
                    <i class="fas fa-briefcase me-2"></i>
                    Offres d'emploi
                </a>
            </li>
            
            <?php if (hasPermission('offres', 'create')): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/jobs/create.php">
                    <i class="fas fa-plus me-2"></i>
                    Publier une offre
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/jobs/applications.php">
                    <i class="fas fa-users me-2"></i>
                    Candidatures
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('users', 'read')): ?>
            <li class="nav-item">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Administration</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/admin/users.php">
                    <i class="fas fa-users me-2"></i>
                    Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/admin/categories.php">
                    <i class="fas fa-tags me-2"></i>
                    Catégories
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/admin/logs.php">
                    <i class="fas fa-history me-2"></i>
                    Journaux
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/admin/settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Paramètres
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
