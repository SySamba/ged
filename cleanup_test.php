<?php
/**
 * Script de nettoyage des fichiers de test
 */

$file = $_GET['file'] ?? '';

if ($file && preg_match('/^test_pdf_\d+\.pdf$/', $file)) {
    $filePath = __DIR__ . '/uploads/documents/' . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Fichier de test nettoyé : $file";
    }
}
?>
