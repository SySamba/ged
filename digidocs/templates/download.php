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

// Vérifier si le fichier existe
if (!$document['chemin_pdf'] || !file_exists($document['chemin_pdf'])) {
    $_SESSION['error'] = 'Fichier PDF introuvable';
    header('Location: ' . APP_URL . '/templates/list.php');
    exit;
}

// Log de l'activité
logActivity('telechargement_document_genere', 'documents_generes', $id, [
    'nom_document' => $document['nom_document']
]);

// Headers pour le téléchargement
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $document['nom_document'] . '.html"');
header('Content-Length: ' . filesize($document['chemin_pdf']));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($document['chemin_pdf']);
exit;
