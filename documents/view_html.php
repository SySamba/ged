<?php
/**
 * Visualiseur spécialement conçu pour les documents HTML générés
 * Force l'affichage HTML même si l'extension est .pdf
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo '<h1>ID de document invalide</h1>';
    exit;
}

try {
    $document = new Document();
    $doc = $document->getById($id);
    
    if (!$doc) {
        http_response_code(404);
        echo '<h1>Document introuvable</h1>';
        exit;
    }
    
    // Vérifier les permissions
    if (!hasPermission('documents', 'read') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo '<h1>Permission insuffisante</h1>';
        exit;
    }
    
    $filePath = $doc['chemin_fichier'];
    
    // Vérifier que le fichier existe
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo '<h1>Fichier introuvable</h1>';
        echo '<p><a href="' . APP_URL . '/documents/list.php">Retour à la liste</a></p>';
        exit;
    }
    
    // Nettoyer le buffer de sortie
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // FORCER l'affichage HTML dans le navigateur
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('X-Content-Type-Options: nosniff');
    
    // Lire le contenu
    $htmlContent = file_get_contents($filePath);
    
    // Ajouter des boutons d'action
    $actionButtons = '
    <div style="position: fixed; top: 10px; right: 10px; z-index: 9999; display: flex; gap: 10px;">
        <a href="' . APP_URL . '/documents/list.php" 
           style="background: #007bff; color: white; padding: 10px 15px; 
                  text-decoration: none; border-radius: 5px; font-family: Arial; 
                  box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            ← Retour à la liste
        </a>
        <a href="' . APP_URL . '/documents/download.php?id=' . $id . '" 
           style="background: #28a745; color: white; padding: 10px 15px; 
                  text-decoration: none; border-radius: 5px; font-family: Arial; 
                  box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            📥 Télécharger
        </a>
        <button onclick="window.print()" 
                style="background: #6c757d; color: white; padding: 10px 15px; 
                       border: none; border-radius: 5px; font-family: Arial; 
                       box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor: pointer;">
            🖨️ Imprimer
        </button>
    </div>';
    
    // Injecter les boutons après la balise <body>
    $htmlContent = str_replace('<body>', '<body>' . $actionButtons, $htmlContent);
    
    // Ajouter du CSS pour l'impression
    $printCSS = '
    <style>
        @media print {
            div[style*="position: fixed"] { display: none !important; }
        }
    </style>';
    
    $htmlContent = str_replace('</head>', $printCSS . '</head>', $htmlContent);
    
    // Afficher le contenu
    echo $htmlContent;
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Erreur serveur</h1>';
    echo '<p>Une erreur est survenue : ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="' . APP_URL . '/documents/list.php">Retour à la liste</a></p>';
}
?>
