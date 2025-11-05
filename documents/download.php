<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

$document = new Document();
$doc = $document->getById($id);

if (!$doc) {
    $_SESSION['error'] = 'Document introuvable';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Vérifier les permissions
if (!hasPermission('documents', 'read') && $doc['utilisateur_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Permission insuffisante';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Le chemin_fichier contient déjà le chemin complet
$filePath = $doc['chemin_fichier'];

// Vérifier si le fichier existe
if (!file_exists($filePath)) {
    $_SESSION['error'] = 'Fichier physique introuvable';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

// Log de l'activité
logActivity('download_document', 'documents', $id, [
    'nom_original' => $doc['nom_original']
]);

// Headers pour le téléchargement
header('Content-Type: ' . $doc['type_mime']);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: attachment; filename="' . $doc['nom_original'] . '"');
header('Cache-Control: private, max-age=0');
header('Pragma: no-cache');

// Lire et envoyer le fichier
readfile($filePath);
exit;
