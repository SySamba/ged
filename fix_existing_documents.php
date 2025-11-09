<?php
/**
 * Script pour corriger les documents existants avec de mauvaises extensions
 */

require_once __DIR__ . '/config/config.php';

// Simuler une session pour le script
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
}

echo "<h1>🔧 Correction des documents existants</h1>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Trouver les documents générés avec extension .pdf mais contenu HTML
    $stmt = $pdo->query("
        SELECT id, nom_original, nom_fichier, chemin_fichier, type_mime 
        FROM documents 
        WHERE source_generation IS NOT NULL 
        AND nom_original LIKE '%.pdf' 
        AND type_mime = 'text/html'
    ");
    
    $documents = $stmt->fetchAll();
    
    if (empty($documents)) {
        echo "<p>✅ Aucun document à corriger trouvé.</p>";
    } else {
        echo "<p>🔍 Trouvé " . count($documents) . " document(s) à corriger :</p>";
        
        foreach ($documents as $doc) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Document ID: {$doc['id']}</h3>";
            echo "<p><strong>Nom actuel :</strong> {$doc['nom_original']}</p>";
            
            // Corriger le nom
            $nouveauNom = preg_replace('/\.pdf$/i', '.html', $doc['nom_original']);
            
            // Mettre à jour en base
            $updateStmt = $pdo->prepare("UPDATE documents SET nom_original = ? WHERE id = ?");
            $updateStmt->execute([$nouveauNom, $doc['id']]);
            
            echo "<p><strong>Nouveau nom :</strong> {$nouveauNom}</p>";
            echo "<p>✅ Document corrigé !</p>";
            echo "</div>";
        }
        
        echo "<h2>✅ Correction terminée !</h2>";
        echo "<p>Les documents peuvent maintenant s'ouvrir correctement.</p>";
    }
    
    // Afficher tous les documents générés après correction
    echo "<h2>📋 Documents générés après correction :</h2>";
    $stmt = $pdo->query("
        SELECT id, nom_original, type_mime, source_generation 
        FROM documents 
        WHERE source_generation IS NOT NULL 
        ORDER BY date_upload DESC
    ");
    
    $allDocs = $stmt->fetchAll();
    
    if (!empty($allDocs)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Type MIME</th><th>Source</th><th>Action</th></tr>";
        
        foreach ($allDocs as $doc) {
            echo "<tr>";
            echo "<td>{$doc['id']}</td>";
            echo "<td>{$doc['nom_original']}</td>";
            echo "<td>{$doc['type_mime']}</td>";
            echo "<td>{$doc['source_generation']}</td>";
            echo "<td><a href='documents/simple_viewer.php?id={$doc['id']}' target='_blank'>🔗 Tester</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erreur :</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='documents/list.php'>🔗 Retour à la liste des documents</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
