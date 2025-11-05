<?php
/**
 * Diagnostic des fichiers physiques
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>🔍 Diagnostic des Fichiers Physiques</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier la structure des dossiers
    echo "<h2>1. Structure des dossiers</h2>";
    
    $basePath = __DIR__;
    echo "Répertoire de base: $basePath<br>";
    
    $folders = ['uploads', 'uploads/documents', 'uploads/cv', 'uploads/generated', 'uploads/temp'];
    foreach ($folders as $folder) {
        $fullPath = $basePath . '/' . $folder;
        if (is_dir($fullPath)) {
            $fileCount = count(glob($fullPath . '/*'));
            echo "✅ $folder/ ($fileCount fichiers)<br>";
        } else {
            echo "❌ $folder/ (n'existe pas)<br>";
        }
    }
    
    // 2. Vérifier les chemins en base vs réalité
    echo "<h2>2. Vérification des documents</h2>";
    
    $stmt = $pdo->query("SELECT id, nom_original, chemin_fichier FROM documents ORDER BY id LIMIT 10");
    $documents = $stmt->fetchAll();
    
    foreach ($documents as $doc) {
        echo "<h4>Document ID {$doc['id']}: {$doc['nom_original']}</h4>";
        echo "Chemin DB: {$doc['chemin_fichier']}<br>";
        
        // Vérifier si le fichier existe
        if (file_exists($doc['chemin_fichier'])) {
            echo "✅ Fichier trouvé<br>";
        } else {
            echo "❌ Fichier non trouvé<br>";
            
            // Essayer différents chemins possibles
            $possiblePaths = [
                $basePath . '/uploads/documents/' . basename($doc['chemin_fichier']),
                $basePath . '/digidocs/uploads/documents/' . basename($doc['chemin_fichier']),
                str_replace('C:\\xampp\\htdocs\\document\\', $basePath . '/', $doc['chemin_fichier']),
                str_replace('C:\\xampp\\htdocs\\document\\digidocs\\', $basePath . '/', $doc['chemin_fichier']),
                str_replace('\\', '/', $doc['chemin_fichier'])
            ];
            
            echo "Tentatives de localisation:<br>";
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    echo "✅ Trouvé à: $path<br>";
                    
                    // Proposer une correction
                    echo "<strong>Correction suggérée:</strong> Mettre à jour le chemin vers: $path<br>";
                    break;
                } else {
                    echo "❌ Pas à: $path<br>";
                }
            }
        }
        echo "<hr>";
    }
    
    // 3. Lister les fichiers réellement présents
    echo "<h2>3. Fichiers réellement présents</h2>";
    
    $uploadsPath = $basePath . '/uploads/documents';
    if (is_dir($uploadsPath)) {
        $files = glob($uploadsPath . '/*');
        echo "Fichiers dans uploads/documents/ :<br>";
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $size = filesize($file);
                echo "📄 $filename (" . formatFileSize($size) . ")<br>";
            }
        }
    } else {
        echo "❌ Dossier uploads/documents/ n'existe pas<br>";
    }
    
    // 4. Vérifier l'ancien dossier digidocs
    echo "<h2>4. Vérification ancien dossier digidocs</h2>";
    
    $oldPath = $basePath . '/digidocs/uploads/documents';
    if (is_dir($oldPath)) {
        $oldFiles = glob($oldPath . '/*');
        echo "⚠️ Ancien dossier digidocs existe encore avec " . count($oldFiles) . " fichiers:<br>";
        foreach ($oldFiles as $file) {
            if (is_file($file)) {
                echo "📄 " . basename($file) . "<br>";
            }
        }
        echo "<strong>Action recommandée:</strong> Déplacer ces fichiers vers uploads/documents/<br>";
    } else {
        echo "✅ Ancien dossier digidocs supprimé<br>";
    }
    
    echo "<h2>5. Actions recommandées</h2>";
    echo "<ul>";
    echo "<li>Si des fichiers sont dans l'ancien dossier digidocs : les déplacer</li>";
    echo "<li>Si les chemins en base sont incorrects : les corriger</li>";
    echo "<li>Si les fichiers sont perdus : les re-uploader</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}
?>
