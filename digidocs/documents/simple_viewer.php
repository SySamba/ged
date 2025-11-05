<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$document = new Document();
$doc = $document->getById($id);

if (!$doc) {
    echo '<h1>Document introuvable</h1>';
    exit;
}

// Vérifier les permissions
if (!hasPermission('documents', 'read') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
    echo '<h1>Permission insuffisante</h1>';
    exit;
}

// Le chemin_fichier contient déjà le chemin complet
$filePath = $doc['chemin_fichier'];
$extension = strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION));

if (!file_exists($filePath)) {
    echo '<h1>Fichier introuvable</h1>';
    echo '<p>Chemin: ' . $filePath . '</p>';
    exit;
}

// Pour les PDF, affichage direct
if ($extension === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $doc['nom_original'] . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

// Pour les images, affichage direct
if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . $doc['nom_original'] . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
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
        <a href="<?= APP_URL ?>/documents/download.php?id=<?= $doc['id'] ?>" 
           class="btn btn-primary btn-lg">
            <i class="fas fa-download me-2"></i>
            Télécharger
        </a>
    </div>
</body>
</html>

