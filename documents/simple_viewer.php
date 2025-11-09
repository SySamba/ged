<?php
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
    
    // Le chemin_fichier contient déjà le chemin complet
    $filePath = $doc['chemin_fichier'];
    
    // Debug : afficher les informations du fichier
    error_log("DEBUG simple_viewer: Document ID={$doc['id']}, Nom={$doc['nom_original']}, Chemin=$filePath");
    error_log("DEBUG simple_viewer: Fichier existe=" . (file_exists($filePath) ? 'OUI' : 'NON'));
    error_log("DEBUG simple_viewer: Source generation=" . ($doc['source_generation'] ?? 'NULL'));
    $extension = strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION));
    
    // Vérifier que le fichier existe
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo '<h1>Fichier introuvable</h1>';
        echo '<p>Le fichier a peut-être été déplacé ou supprimé.</p>';
        echo '<p><a href="' . APP_URL . '/documents/list.php">Retour à la liste</a></p>';
        exit;
    }
    
    // Vérifier que le fichier est lisible
    if (!is_readable($filePath)) {
        http_response_code(500);
        echo '<h1>Erreur d\'accès au fichier</h1>';
        echo '<p>Le fichier ne peut pas être lu.</p>';
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Erreur serveur</h1>';
    echo '<p>Une erreur est survenue lors de l\'accès au document.</p>';
    error_log("Erreur simple_viewer.php: " . $e->getMessage());
    exit;
}

// Pour les fichiers PDF, vérifier d'abord si c'est vraiment un PDF ou du HTML
if ($extension === 'pdf') {
    // Lire les premiers caractères pour détecter le type réel
    $handle = fopen($filePath, 'r');
    $firstBytes = fread($handle, 100);
    fclose($handle);
    
    // Si le fichier commence par du HTML, le traiter comme tel
    if (stripos($firstBytes, '<!DOCTYPE html') !== false || stripos($firstBytes, '<html') !== false) {
        // C'est un fichier HTML avec extension .pdf - le traiter comme HTML
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $htmlContent = file_get_contents($filePath);
        
        // Ajouter un bouton de retour
        $backButton = '
        <div style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
            <a href="' . APP_URL . '/documents/list.php" 
               style="background: #007bff; color: white; padding: 10px 15px; 
                      text-decoration: none; border-radius: 5px; font-family: Arial;">
                ← Retour à la liste
            </a>
        </div>';
        
        $htmlContent = str_replace('<body>', '<body>' . $backButton, $htmlContent);
        echo $htmlContent;
        exit;
    } else {
        // C'est un vrai PDF
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($doc['nom_original']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
}

// Pour les images, affichage direct
if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
    // Nettoyer le buffer de sortie
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Détecter le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // Vérifier que c'est bien une image
    if (strpos($mimeType, 'image/') !== 0) {
        $mimeType = 'application/octet-stream';
    }
    
    // Headers sécurisés pour images
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . addslashes($doc['nom_original']) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=3600');
    
    // Lire et envoyer le fichier
    readfile($filePath);
    exit;
}

// Pour les fichiers HTML (documents générés), affichage direct
if ($extension === 'html' || $doc['source_generation']) {
    // Nettoyer le buffer de sortie
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Lire le contenu HTML
    $htmlContent = file_get_contents($filePath);
    
    // Ajouter un bouton de retour en haut du document
    $backButton = '
    <div style="position: fixed; top: 10px; right: 10px; z-index: 9999;">
        <a href="' . APP_URL . '/documents/list.php" 
           style="background: #007bff; color: white; padding: 10px 15px; 
                  text-decoration: none; border-radius: 5px; font-family: Arial;">
            ← Retour à la liste
        </a>
    </div>';
    
    // Injecter le bouton après la balise <body>
    $htmlContent = str_replace('<body>', '<body>' . $backButton, $htmlContent);
    
    // Afficher le contenu HTML
    echo $htmlContent;
    exit;
}

// Pour les autres fichiers, page de téléchargement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($doc['nom_original']) ?></title>
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
        .viewer-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
        }
        .file-icon {
            font-size: 5rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="file-icon">
            <i class="fas fa-file"></i>
        </div>
        <h3 class="mb-3"><?= htmlspecialchars($doc['nom_original']) ?></h3>
        <p class="text-muted mb-4">
            Ce type de fichier ne peut pas être prévisualisé.<br>
            Cliquez pour télécharger.
        </p>
        <p class="mb-4">
            <strong>Taille :</strong> <?= formatFileSize($doc['taille_fichier']) ?><br>
            <strong>Type :</strong> <?= htmlspecialchars($doc['type_mime']) ?>
        </p>
        <div class="d-grid gap-2">
            <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
               class="btn btn-primary btn-lg">
                <i class="fas fa-download me-2"></i>
                Télécharger
            </a>
            <a href="<?= APP_URL ?>/documents/list.php" 
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la liste
            </a>
        </div>
    </div>
</body>
</html>

