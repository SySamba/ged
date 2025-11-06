<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <style>
            /* Améliorer la visibilité des liens de la sidebar */
            .sidebar .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
                font-weight: 500;
                border-radius: 0.5rem;
                margin: 0.25rem 0.5rem;
                transition: all 0.2s ease;
            }
            
            .sidebar .nav-link:hover {
                background-color: rgba(0, 123, 255, 0.1);
                color: #0d6efd;
                transform: translateX(5px);
            }
            
            .sidebar .nav-link.active {
                background-color: #0d6efd;
                color: white;
            }
            
            /* Mettre en évidence certains liens importants */
            .sidebar .nav-link[href*="orders.php"] {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                font-weight: 600;
            }
            
            .sidebar .nav-link[href*="orders.php"]:hover {
                background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
                color: white;
                transform: translateX(8px) scale(1.02);
            }
            
            .sidebar .nav-link i {
                width: 20px;
                text-align: center;
            }
        </style>
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
                <a class="nav-link" href="<?= APP_URL ?>/documents/search.php">
                    <i class="fas fa-search me-2"></i>
                    Rechercher
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?= APP_URL ?>/documents/archived.php">
                    <i class="fas fa-archive me-2"></i>
                    Archives
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
