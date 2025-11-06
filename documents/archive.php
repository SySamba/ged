<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$action = sanitize($_GET['action'] ?? '');

if (!$id || !in_array($action, ['archive', 'unarchive', 'delete', 'restore'])) {
    $_SESSION['error'] = 'Action ou ID invalide';
    header('Location: ' . APP_URL . '/documents/list.php');
    exit;
}

$document = new Document();

// Traitement des actions
switch ($action) {
    case 'archive':
        $raison = sanitize($_POST['raison'] ?? '');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $document->archive($id, $raison);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
            
            header('Location: ' . APP_URL . '/documents/list.php');
            exit;
        }
        
        // Afficher le formulaire de confirmation d'archivage
        $doc = $document->getById($id);
        if (!$doc) {
            $_SESSION['error'] = 'Document introuvable';
            header('Location: ' . APP_URL . '/documents/list.php');
            exit;
        }
        
        include __DIR__ . '/archive_form.php';
        break;
        
    case 'unarchive':
        $result = $document->unarchive($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        $redirect = $_GET['from'] === 'archived' ? '/documents/archived.php' : '/documents/list.php';
        header('Location: ' . APP_URL . $redirect);
        exit;
        
    case 'delete':
        $raison = sanitize($_POST['raison'] ?? '');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $document->markForDeletion($id, $raison);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
            
            header('Location: ' . APP_URL . '/documents/list.php');
            exit;
        }
        
        // Rediriger vers la page de suppression existante
        header('Location: ' . APP_URL . '/documents/delete.php?id=' . $id);
        exit;
        
    case 'restore':
        $result = $document->restore($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        $redirect = $_GET['from'] === 'trash' ? '/documents/trash.php' : '/documents/list.php';
        header('Location: ' . APP_URL . $redirect);
        exit;
}
?>
