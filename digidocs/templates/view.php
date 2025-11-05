<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$template = new Template();
$document = $template->getGeneratedDocument($id);

if (!$document) {
    $_SESSION['error'] = 'Document introuvable';
    header('Location: ' . APP_URL . '/templates/list.php');
    exit;
}

// Vérifier les permissions
if ($document['utilisateur_id'] != $_SESSION['user_id'] && !hasPermission('modeles', 'read')) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/templates/list.php');
    exit;
}

// Reconstituer le HTML du document
$donnees = json_decode($document['donnees_remplies'], true);
$html = $template->replaceVariables($document['template_html'], $donnees);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($document['nom_document']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/dashboard.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .viewer-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            margin: 2rem auto;
            max-width: 1200px;
            overflow: hidden;
        }

        .viewer-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .viewer-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .document-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }

        .document-meta {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            position: relative;
            z-index: 2;
        }

        .meta-item {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .action-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .btn-success-modern {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
            color: white;
        }

        .btn-outline-modern {
            background: transparent;
            color: var(--secondary-color);
            border: 2px solid var(--border-color);
        }

        .btn-outline-modern:hover {
            background: var(--light-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .document-content {
            background: white;
            padding: 3rem;
            margin: 0;
            min-height: 600px;
            position: relative;
        }

        .document-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color), var(--warning-color));
        }
        
        /* Styles améliorés pour les templates */
        .contrat-template, .facture-template, .bon-commande-template {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            color: #2d3748;
            background: white;
        }
        
        .contrat-template h1, .facture-template h1, .bon-commande-template h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 15px;
            border: 2px solid rgba(37, 99, 235, 0.2);
            position: relative;
        }

        .contrat-template h1::after, .facture-template h1::after, .bon-commande-template h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            border-radius: 2px;
        }
        
        .contrat-template h2, .contrat-template h3 {
            color: var(--secondary-color);
            font-weight: 600;
            margin: 2rem 0 1rem 0;
            padding: 0.5rem 0;
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
            background: rgba(37, 99, 235, 0.05);
        }

        .contrat-template p, .facture-template p, .bon-commande-template p {
            margin-bottom: 1rem;
            text-align: justify;
        }
        
        .contrat-template .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 4rem;
            gap: 2rem;
        }
        
        .contrat-template .signatures > div {
            text-align: center;
            flex: 1;
            padding: 2rem 1rem 1rem;
            background: rgba(37, 99, 235, 0.05);
            border-radius: 15px;
            border: 2px dashed var(--primary-color);
            font-weight: 600;
            color: var(--secondary-color);
        }

        .contrat-template .signatures > div::before {
            content: '✍️';
            display: block;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .facture-template .header, .bon-commande-template .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(5, 150, 105, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .facture-template .date, .bon-commande-template .date {
            font-weight: 600;
            color: var(--success-color);
            font-size: 1.1rem;
        }
        
        .facture-template .parties, .bon-commande-template .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .facture-template .parties > div, .bon-commande-template .parties > div {
            padding: 1.5rem;
            background: rgba(100, 116, 139, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        .facture-template .parties h3, .bon-commande-template .parties h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .facture-template table, .bon-commande-template table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .facture-template th, .facture-template td,
        .bon-commande-template th, .bon-commande-template td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .facture-template th, .bon-commande-template th {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .facture-template tbody tr:hover, .bon-commande-template tbody tr:hover {
            background: rgba(37, 99, 235, 0.05);
        }
        
        .facture-template .totaux {
            background: rgba(5, 150, 105, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(5, 150, 105, 0.2);
            margin-top: 2rem;
        }

        .facture-template .totaux p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }

        .facture-template .totaux p:last-child {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--success-color);
            padding: 1rem;
            background: white;
            border-radius: 10px;
            text-align: center;
        }
        
        .bon-commande-template .conditions {
            margin: 2rem 0;
            background: rgba(217, 119, 6, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(217, 119, 6, 0.2);
        }

        .bon-commande-template .conditions p {
            margin: 0.5rem 0;
            font-weight: 500;
        }
        
        .bon-commande-template .total {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
            color: white;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }
        
        @media print {
            .no-print { display: none !important; }
            .document-content { 
                box-shadow: none; 
                margin: 0; 
                padding: 0;
            }
            body { background: white !important; }
        }
        /* Styles responsifs */
        @media (max-width: 768px) {
            .document-title {
                font-size: 1.8rem;
            }
            
            .document-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .facture-template .parties, .bon-commande-template .parties {
                grid-template-columns: 1fr;
            }
            
            .contrat-template .signatures {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation cachée pour l'impression -->
    <div class="no-print">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>
    </div>

    <!-- Container principal moderne -->
    <div class="viewer-container">
        <!-- En-tête du document -->
        <div class="viewer-header">
            <div class="document-title">
                <i class="fas fa-file-alt me-3"></i>
                <?= htmlspecialchars($document['nom_document']) ?>
            </div>
            <div class="document-meta">
                <div class="meta-item">
                    <i class="fas fa-layer-group me-2"></i>
                    <?= htmlspecialchars($document['modele_nom']) ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-tag me-2"></i>
                    <?= getTypeLabel($document['modele_type']) ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar me-2"></i>
                    <?= date('d/m/Y à H:i', strtotime($document['date_generation'])) ?>
                </div>
            </div>
        </div>

        <!-- Barre d'actions -->
        <div class="action-bar no-print">
            <div class="d-flex align-items-center">
                <i class="fas fa-tools me-2 text-muted"></i>
                <span class="text-muted">Actions disponibles</span>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn-modern btn-primary-modern" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Imprimer
                </button>
                <?php if ($document['chemin_pdf']): ?>
                    <a href="<?= APP_URL ?>/templates/download.php?id=<?= $document['id'] ?>" 
                       class="btn-modern btn-success-modern">
                        <i class="fas fa-download"></i>
                        Télécharger PDF
                    </a>
                <?php endif; ?>
                <button type="button" class="btn-modern btn-outline-modern" onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i>
                    Plein écran
                </button>
                <a href="<?= APP_URL ?>/templates/list.php" class="btn-modern btn-outline-modern">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </div>

        <!-- Contenu du document -->
        <div class="document-content" id="documentContent">
            <?= $html ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour le mode plein écran
        function toggleFullscreen() {
            const container = document.querySelector('.viewer-container');
            const button = document.querySelector('[onclick="toggleFullscreen()"]');
            const icon = button.querySelector('i');
            
            if (container.classList.contains('fullscreen')) {
                container.classList.remove('fullscreen');
                icon.className = 'fas fa-expand';
                button.innerHTML = '<i class="fas fa-expand"></i> Plein écran';
                document.body.style.overflow = 'auto';
            } else {
                container.classList.add('fullscreen');
                icon.className = 'fas fa-compress';
                button.innerHTML = '<i class="fas fa-compress"></i> Réduire';
                document.body.style.overflow = 'hidden';
            }
        }

        // Styles pour le mode plein écran
        const fullscreenStyles = `
            .viewer-container.fullscreen {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                max-width: none;
                margin: 0;
                border-radius: 0;
                z-index: 9999;
                overflow-y: auto;
            }
            
            .viewer-container.fullscreen .document-content {
                min-height: calc(100vh - 200px);
            }
        `;

        // Ajouter les styles au document
        const styleSheet = document.createElement('style');
        styleSheet.textContent = fullscreenStyles;
        document.head.appendChild(styleSheet);

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.viewer-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);

            // Animation des boutons d'action
            const buttons = document.querySelectorAll('.btn-modern');
            buttons.forEach((button, index) => {
                button.style.opacity = '0';
                button.style.transform = 'translateX(20px)';
                
                setTimeout(() => {
                    button.style.transition = 'all 0.4s ease';
                    button.style.opacity = '1';
                    button.style.transform = 'translateX(0)';
                }, 200 + (index * 100));
            });
        });

        // Fonction pour copier le contenu
        function copyContent() {
            const content = document.getElementById('documentContent');
            const range = document.createRange();
            range.selectNode(content);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            
            try {
                document.execCommand('copy');
                showNotification('Contenu copié dans le presse-papiers', 'success');
            } catch (err) {
                showNotification('Erreur lors de la copie', 'error');
            }
            
            window.getSelection().removeAllRanges();
        }

        // Fonction pour afficher des notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 10000;
                min-width: 300px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'p':
                        e.preventDefault();
                        window.print();
                        break;
                    case 'f':
                        e.preventDefault();
                        toggleFullscreen();
                        break;
                    case 'c':
                        if (e.shiftKey) {
                            e.preventDefault();
                            copyContent();
                        }
                        break;
                }
            }
            
            if (e.key === 'Escape') {
                const container = document.querySelector('.viewer-container');
                if (container.classList.contains('fullscreen')) {
                    toggleFullscreen();
                }
            }
        });

        // Amélioration de l'impression
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
        });

        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
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
?>
