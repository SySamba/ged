<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$document = new Document();
$doc = $document->getById($id);

echo "<h2>Debug Document ID: $id</h2>";

if (!$doc) {
    echo "<p style='color: red;'>Document introuvable dans la base de données</p>";
    exit;
}

echo "<h3>Informations du document :</h3>";
echo "<pre>";
print_r($doc);
echo "</pre>";

// Le chemin_fichier contient déjà le chemin complet
$filePath = $doc['chemin_fichier'];
echo "<h3>Vérifications :</h3>";
echo "<p><strong>UPLOAD_PATH:</strong> " . UPLOAD_PATH . "</p>";
echo "<p><strong>Chemin stocké en BDD:</strong> " . $doc['chemin_fichier'] . "</p>";
echo "<p><strong>Chemin utilisé:</strong> " . $filePath . "</p>";
echo "<p><strong>Fichier existe:</strong> " . (file_exists($filePath) ? 'OUI' : 'NON') . "</p>";

if (file_exists($filePath)) {
    echo "<p><strong>Taille réelle:</strong> " . filesize($filePath) . " bytes</p>";
    echo "<p><strong>Type MIME détecté:</strong> " . mime_content_type($filePath) . "</p>";
} else {
    echo "<p style='color: red;'>Le fichier n'existe pas sur le serveur !</p>";
    
    // Vérifier le dossier uploads
    echo "<h3>Contenu du dossier uploads :</h3>";
    if (is_dir(UPLOAD_PATH)) {
        $files = scandir(UPLOAD_PATH);
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li>$file</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Le dossier uploads n'existe pas !</p>";
    }
}

echo "<h3>URLs de test :</h3>";
echo "<p><a href='" . APP_URL . "/documents/preview.php?id=$id' target='_blank'>Test preview.php</a></p>";
echo "<p><a href='" . APP_URL . "/documents/download.php?id=$id' target='_blank'>Test download.php</a></p>";
echo "<p><a href='" . APP_URL . "/documents/viewer.php?id=$id' target='_blank'>Test viewer.php</a></p>";
?>
