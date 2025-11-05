<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$document = new Document();
$doc = $document->getById($id);

if (!$doc) {
    http_response_code(404);
    exit('Document introuvable');
}

// Vérifier les permissions
if (!hasPermission('documents', 'read') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    exit('Permission insuffisante');
}

// Le chemin_fichier contient déjà le chemin complet
$filePath = $doc['chemin_fichier'];

// Vérifier si le fichier existe
if (!file_exists($filePath)) {
    http_response_code(404);
    exit('Fichier physique introuvable: ' . $filePath);
}

// Log de l'activité
logActivity('preview_document', 'documents', $id, [
    'nom_original' => $doc['nom_original']
]);

// Déterminer le type de contenu
$extension = strtolower(pathinfo($doc['nom_original'], PATHINFO_EXTENSION));

// Headers pour l'affichage
header('Content-Type: ' . $doc['type_mime']);
header('Content-Length: ' . $doc['taille_fichier']);
header('Cache-Control: private, max-age=3600');
header('Pragma: cache');

// Pour les PDF, permettre l'affichage dans le navigateur
if ($extension === 'pdf') {
    header('Content-Disposition: inline; filename="' . $doc['nom_original'] . '"');
} else {
    // Pour les images et autres, affichage direct
    header('Content-Disposition: inline');
}

// Lire et afficher le fichier
readfile($filePath);
exit;
