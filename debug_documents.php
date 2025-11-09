<?php
/**
 * Script de diagnostic pour les documents générés
 */

require_once __DIR__ . '/config/config.php';

// Simuler une session pour le test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
}

echo "<h1>🔍 Diagnostic des documents générés</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // 1. Vérifier les documents avec source_generation
    echo "<h2>1. Documents générés dans la base</h2>";
    $stmt = $pdo->query("
        SELECT id, nom_original, nom_fichier, chemin_fichier, type_mime, source_generation 
        FROM documents 
        WHERE source_generation IS NOT NULL 
        ORDER BY date_upload DESC 
        LIMIT 5
    ");
    $docs = $stmt->fetchAll();
    
    if (empty($docs)) {
        echo "<p>❌ Aucun document généré trouvé dans la base de données.</p>";
        echo "<p>💡 Générez d'abord un document via les modèles.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Fichier</th><th>Chemin</th><th>Existe?</th><th>Lisible?</th><th>Taille</th></tr>";
        
        foreach ($docs as $doc) {
            $exists = file_exists($doc['chemin_fichier']) ? '✅' : '❌';
            $readable = is_readable($doc['chemin_fichier']) ? '✅' : '❌';
            $size = file_exists($doc['chemin_fichier']) ? filesize($doc['chemin_fichier']) . ' bytes' : 'N/A';
            
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['nom_original']}</td>";
            echo "<td>{$doc['nom_fichier']}</td>";
            echo "<td>{$doc['chemin_fichier']}</td>";
            echo "<td>{$exists}</td>";
            echo "<td>{$readable}</td>";
            echo "<td>{$size}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Vérifier les répertoires
    echo "<h2>2. Vérification des répertoires</h2>";
    $uploadPath = UPLOAD_PATH . '/documents';
    echo "<p><strong>Chemin d'upload :</strong> $uploadPath</p>";
    echo "<p><strong>Existe :</strong> " . (is_dir($uploadPath) ? '✅' : '❌') . "</p>";
    echo "<p><strong>Accessible en écriture :</strong> " . (is_writable($uploadPath) ? '✅' : '❌') . "</p>";
    
    // 3. Lister les fichiers dans le répertoire
    echo "<h2>3. Fichiers dans le répertoire</h2>";
    if (is_dir($uploadPath)) {
        $files = scandir($uploadPath);
        $files = array_filter($files, function($file) {
            return !in_array($file, ['.', '..']);
        });
        
        if (empty($files)) {
            echo "<p>❌ Aucun fichier dans le répertoire d'upload.</p>";
        } else {
            echo "<ul>";
            foreach ($files as $file) {
                $fullPath = $uploadPath . '/' . $file;
                $size = filesize($fullPath);
                echo "<li>$file ($size bytes)</li>";
            }
            echo "</ul>";
        }
    }
    
    // 4. Test de création de fichier
    echo "<h2>4. Test de création de fichier</h2>";
    $testFile = $uploadPath . '/test_' . time() . '.html';
    $testContent = '<html><body><h1>Test</h1></body></html>';
    
    if (file_put_contents($testFile, $testContent)) {
        echo "<p>✅ Création de fichier réussie : $testFile</p>";
        if (file_exists($testFile)) {
            echo "<p>✅ Fichier existe après création</p>";
            unlink($testFile); // Nettoyer
            echo "<p>✅ Fichier supprimé après test</p>";
        }
    } else {
        echo "<p>❌ Impossible de créer un fichier de test</p>";
    }
    
    // 5. Vérifier la configuration
    echo "<h2>5. Configuration</h2>";
    echo "<p><strong>UPLOAD_PATH :</strong> " . UPLOAD_PATH . "</p>";
    echo "<p><strong>APP_URL :</strong> " . APP_URL . "</p>";
    echo "<p><strong>ROOT_PATH :</strong> " . ROOT_PATH . "</p>";
    
    // 6. Test avec un document existant
    if (!empty($docs)) {
        echo "<h2>6. Test d'ouverture du premier document</h2>";
        $firstDoc = $docs[0];
        echo "<p><strong>Document testé :</strong> {$firstDoc['nom_original']}</p>";
        echo "<p><strong>Chemin :</strong> {$firstDoc['chemin_fichier']}</p>";
        
        if (file_exists($firstDoc['chemin_fichier'])) {
            $content = file_get_contents($firstDoc['chemin_fichier']);
            if ($content !== false) {
                echo "<p>✅ Contenu lu avec succès (" . strlen($content) . " caractères)</p>";
                echo "<p><strong>Début du contenu :</strong></p>";
                echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>";
                
                // Lien de test
                echo "<p><a href='documents/simple_viewer.php?id={$firstDoc['id']}' target='_blank'>🔗 Tester l'ouverture de ce document</a></p>";
            } else {
                echo "<p>❌ Impossible de lire le contenu du fichier</p>";
            }
        } else {
            echo "<p>❌ Le fichier n'existe pas</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erreur :</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='documents/list.php'>🔗 Retour à la liste des documents</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>
