<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
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

// Déterminer le type de fichier
$extension = strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION));
$isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
$isPdf = $extension === 'pdf';
$isOffice = in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
$isText = in_array($extension, ['txt', 'csv']);

// Pour les PDF, créer une interface de visualisation
if ($isPdf) {
    $filePath = $doc['chemin_fichier'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo '<h1>Fichier introuvable</h1>';
        exit;
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($doc['nom_original']) ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #2c3e50;
                overflow: hidden;
            }
            .header {
                background: rgba(0,0,0,0.8);
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                color: white;
            }
            .document-title {
                font-size: 1.1rem;
                font-weight: 600;
                display: flex;
                align-items: center;
            }
            .document-title i {
                margin-right: 10px;
                color: #e74c3c;
            }
            .actions {
                display: flex;
                gap: 10px;
            }
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                transition: all 0.2s ease;
            }
            .btn-success {
                background: #27ae60;
                color: white;
            }
            .btn-secondary {
                background: #95a5a6;
                color: white;
            }
            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            .pdf-container {
                height: 100vh;
                width: 100%;
            }
            .pdf-iframe {
                width: 100%;
                height: 100%;
                border: none;
            }
        </style>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="header">
            <div class="document-title">
                <i class="fas fa-file-pdf"></i>
                <?= htmlspecialchars($doc['nom_original']) ?>
                <small style="margin-left: 10px; color: #bdc3c7; font-weight: normal;">
                    (<?= formatFileSize($doc['taille_fichier']) ?>)
                </small>
            </div>
            <div class="actions">
                <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download" style="margin-right: 5px;"></i>
                    Télécharger
                </a>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times" style="margin-right: 5px;"></i>
                    Fermer
                </button>
            </div>
        </div>
        
        <div class="pdf-container">
            <iframe src="<?= APP_URL ?>/documents/preview.php?id=<?= $doc['id'] ?>" 
                    class="pdf-iframe">
                Votre navigateur ne supporte pas l'affichage des PDF.
                <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>">Télécharger le PDF</a>
            </iframe>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Pour les images, créer une interface de visualisation
if ($isImage) {
    $filePath = $doc['chemin_fichier'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo '<h1>Fichier introuvable</h1>';
        exit;
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($doc['nom_original']) ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #000;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            .header {
                background: rgba(0,0,0,0.8);
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: white;
                z-index: 1000;
            }
            .document-title {
                font-size: 1.1rem;
                font-weight: 600;
                display: flex;
                align-items: center;
            }
            .document-title i {
                margin-right: 10px;
                color: #3498db;
            }
            .actions {
                display: flex;
                gap: 10px;
            }
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                transition: all 0.2s ease;
            }
            .btn-success {
                background: #27ae60;
                color: white;
            }
            .btn-secondary {
                background: #95a5a6;
                color: white;
            }
            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            .image-container {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .image-viewer {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            }
        </style>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="header">
            <div class="document-title">
                <i class="fas fa-image"></i>
                <?= htmlspecialchars($doc['nom_original']) ?>
                <small style="margin-left: 10px; color: #bdc3c7; font-weight: normal;">
                    (<?= formatFileSize($doc['taille_fichier']) ?>)
                </small>
            </div>
            <div class="actions">
                <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download" style="margin-right: 5px;"></i>
                    Télécharger
                </a>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times" style="margin-right: 5px;"></i>
                    Fermer
                </button>
            </div>
        </div>
        
        <div class="image-container">
            <img src="<?= APP_URL ?>/documents/preview.php?id=<?= $doc['id'] ?>" 
                 alt="<?= htmlspecialchars($doc['nom_original']) ?>"
                 class="image-viewer">
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Pour les documents Office, on crée une page avec iframe pour Google Docs Viewer
if ($isOffice) {
    // Construire l'URL complète pour Google Docs Viewer
    $fileUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . APP_URL . '/documents/download.php?id=' . $doc['id']);
    $viewerUrl = 'https://docs.google.com/viewer?url=' . $fileUrl . '&embedded=true';
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($doc['nom_original']) ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f8f9fa;
                overflow: hidden;
            }
            .header {
                background: white;
                padding: 15px 20px;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                position: relative;
                z-index: 1000;
            }
            .document-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #495057;
                display: flex;
                align-items: center;
            }
            .document-title i {
                margin-right: 10px;
                color: #007bff;
            }
            .actions {
                display: flex;
                gap: 10px;
            }
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
                font-size: 0.9rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                transition: all 0.2s ease;
            }
            .btn-primary {
                background: #007bff;
                color: white;
            }
            .btn-success {
                background: #28a745;
                color: white;
            }
            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            }
            .viewer-container {
                height: calc(100vh - 70px);
                position: relative;
            }
            .viewer-iframe {
                width: 100%;
                height: 100%;
                border: none;
                background: white;
            }
            .loading {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                color: #6c757d;
            }
            .loading i {
                font-size: 3rem;
                margin-bottom: 15px;
                animation: spin 2s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="header">
            <div class="document-title">
                <i class="fas fa-file-word"></i>
                <?= htmlspecialchars($doc['nom_original']) ?>
                <small style="margin-left: 10px; color: #6c757d; font-weight: normal;">
                    (<?= formatFileSize($doc['taille_fichier']) ?>)
                </small>
            </div>
            <div class="actions">
                <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download" style="margin-right: 5px;"></i>
                    Télécharger
                </a>
                <a href="<?= APP_URL ?>/documents/list.php" 
                   class="btn btn-primary" target="_parent">
                    <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                    Retour
                </a>
            </div>
        </div>
        
        <div class="viewer-container">
            <div class="loading" id="loading">
                <i class="fas fa-spinner"></i>
                <div>Chargement du document...</div>
                <small style="display: block; margin-top: 10px;">
                    Si le document ne s'affiche pas, 
                    <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>">cliquez ici pour le télécharger</a>
                </small>
            </div>
            <iframe src="<?= $viewerUrl ?>" 
                    class="viewer-iframe" 
                    onload="document.getElementById('loading').style.display='none'">
            </iframe>
        </div>
        
        <script>
            // Masquer le loading après 10 secondes si l'iframe ne charge pas
            setTimeout(function() {
                document.getElementById('loading').style.display = 'none';
            }, 10000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Pour les fichiers texte, on peut les afficher
if ($isText) {
    $filePath = $doc['chemin_fichier'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo '<h1>Fichier introuvable</h1>';
        exit;
    }
    
    $content = file_get_contents($filePath);
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($doc['nom_original']) ?></title>
        <style>
            body {
                font-family: 'Courier New', monospace;
                margin: 20px;
                background: #f8f9fa;
                line-height: 1.6;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 1200px;
                margin: 0 auto;
            }
            .header {
                border-bottom: 2px solid #007bff;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
            .filename {
                color: #007bff;
                font-size: 1.5em;
                font-weight: bold;
            }
            .content {
                white-space: pre-wrap;
                word-wrap: break-word;
                font-size: 14px;
                line-height: 1.5;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="filename"><?= htmlspecialchars($doc['nom_original']) ?></div>
                <small class="text-muted">Taille: <?= formatFileSize($doc['taille_fichier']) ?></small>
            </div>
            <div class="content"><?= htmlspecialchars($content) ?></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Pour tous les autres types, proposer le téléchargement
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
        .download-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="file-icon">
            <i class="fas fa-<?= getFileTypeIcon($doc['type_mime']) ?>"></i>
        </div>
        <h3 class="mb-3"><?= htmlspecialchars($doc['nom_original']) ?></h3>
        <p class="text-muted mb-4">
            Ce type de fichier ne peut pas être prévisualisé dans le navigateur.<br>
            Téléchargez-le pour l'ouvrir avec l'application appropriée.
        </p>
        <p class="mb-4">
            <strong>Taille :</strong> <?= formatFileSize($doc['taille_fichier']) ?><br>
            <strong>Type :</strong> <?= htmlspecialchars($doc['type_mime']) ?>
        </p>
        <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
           class="btn btn-success download-btn">
            <i class="fas fa-download me-2"></i>
            Télécharger le fichier
        </a>
    </div>
    
    <script>
        // Auto-téléchargement après 3 secondes
        setTimeout(function() {
            window.location.href = '<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>';
        }, 3000);
    </script>
</body>
</html>

<?php
function getFileTypeIcon($mimeType) {
    $icons = [
        'application/pdf' => 'file-pdf',
        'application/msword' => 'file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-word',
        'application/vnd.ms-excel' => 'file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-excel',
        'application/vnd.ms-powerpoint' => 'file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'file-powerpoint',
        'image/jpeg' => 'file-image',
        'image/jpg' => 'file-image',
        'image/png' => 'file-image',
        'image/gif' => 'file-image',
        'image/bmp' => 'file-image',
        'image/webp' => 'file-image',
        'text/plain' => 'file-alt',
        'text/csv' => 'file-csv'
    ];
    return $icons[$mimeType] ?? 'file';
}
?>
