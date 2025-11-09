<?php
/**
 * Script d'installation de DomPDF pour la génération de PDF
 */

echo "<h1>🔧 Installation de DomPDF pour génération PDF</h1>";

// Vérifier si Composer est disponible
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<h2>📦 Installation de DomPDF</h2>";
    
    // Créer le fichier composer.json
    $composerJson = [
        "require" => [
            "dompdf/dompdf" => "^2.0"
        ]
    ];
    
    file_put_contents(__DIR__ . '/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
    echo "<p>✅ Fichier composer.json créé</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;'>";
    echo "<h3>📋 Instructions d'installation :</h3>";
    echo "<ol>";
    echo "<li><strong>Via cPanel File Manager (Hostinger) :</strong>";
    echo "<ul>";
    echo "<li>Allez dans le File Manager de votre cPanel</li>";
    echo "<li>Naviguez vers le dossier de votre site</li>";
    echo "<li>Ouvrez le Terminal (si disponible)</li>";
    echo "<li>Exécutez : <code>composer install</code></li>";
    echo "</ul></li>";
    echo "<li><strong>Via SSH (si disponible) :</strong>";
    echo "<ul>";
    echo "<li>Connectez-vous en SSH à votre serveur</li>";
    echo "<li>Naviguez vers : <code>cd /home/u588247422/domains/ged.teranganumerique.com/public_html</code></li>";
    echo "<li>Exécutez : <code>composer install</code></li>";
    echo "</ul></li>";
    echo "<li><strong>Installation manuelle :</strong>";
    echo "<ul>";
    echo "<li>Téléchargez DomPDF depuis : <a href='https://github.com/dompdf/dompdf/releases' target='_blank'>GitHub</a></li>";
    echo "<li>Extrayez dans le dossier <code>vendor/dompdf/</code></li>";
    echo "</ul></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<p>✅ Composer autoload détecté</p>";
}

// Vérifier si DomPDF est disponible
try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    if (class_exists('Dompdf\Dompdf')) {
        echo "<p>✅ <strong>DomPDF est installé et disponible !</strong></p>";
        
        // Test de génération PDF
        echo "<h2>🧪 Test de génération PDF</h2>";
        
        $dompdf = new \Dompdf\Dompdf();
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Test PDF</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #007bff; }
                .test-content { background: #f8f9fa; padding: 15px; border-radius: 5px; }
            </style>
        </head>
        <body>
            <h1>Test de génération PDF</h1>
            <div class="test-content">
                <p>Ce document PDF a été généré avec succès par DomPDF.</p>
                <p><strong>Date :</strong> ' . date('d/m/Y H:i:s') . '</p>
                <p><strong>Système :</strong> DigiDocs</p>
            </div>
        </body>
        </html>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $testFile = __DIR__ . '/uploads/documents/test_pdf_' . time() . '.pdf';
        
        // Créer le répertoire si nécessaire
        $uploadDir = dirname($testFile);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $output = $dompdf->output();
        if (file_put_contents($testFile, $output)) {
            echo "<p>✅ <strong>Test PDF généré avec succès !</strong></p>";
            echo "<p>📁 Fichier : " . basename($testFile) . "</p>";
            echo "<p>📏 Taille : " . formatFileSize(filesize($testFile)) . "</p>";
            echo "<p><a href='uploads/documents/" . basename($testFile) . "' target='_blank'>🔗 Voir le PDF de test</a></p>";
            
            // Nettoyer le fichier de test après quelques secondes
            echo "<script>
                setTimeout(function() {
                    fetch('cleanup_test.php?file=" . basename($testFile) . "');
                }, 10000);
            </script>";
        } else {
            echo "<p>❌ Erreur lors de la création du fichier PDF</p>";
        }
        
    } else {
        echo "<p>❌ DomPDF n'est pas disponible</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Erreur :</strong> " . $e->getMessage() . "</p>";
    echo "<p>DomPDF n'est pas encore installé. Suivez les instructions ci-dessus.</p>";
}

// Créer un autoloader simple si Composer n'est pas disponible
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<h2>🔧 Solution alternative sans Composer</h2>";
    echo "<p>Si vous ne pouvez pas installer Composer, voici une solution alternative :</p>";
    
    $alternativeCode = '<?php
/**
 * Autoloader simple pour DomPDF
 */

// Télécharger DomPDF manuellement et placer dans vendor/dompdf/dompdf/
if (file_exists(__DIR__ . \'/vendor/dompdf/dompdf/autoload.inc.php\')) {
    require_once __DIR__ . \'/vendor/dompdf/dompdf/autoload.inc.php\';
}
?>';
    
    file_put_contents(__DIR__ . '/dompdf_autoload.php', $alternativeCode);
    echo "<p>✅ Fichier dompdf_autoload.php créé</p>";
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

echo "<hr>";
echo "<p><strong>🎯 Prochaines étapes :</strong></p>";
echo "<ol>";
echo "<li>Installez DomPDF selon les instructions ci-dessus</li>";
echo "<li>Rechargez cette page pour vérifier l'installation</li>";
echo "<li>Testez la génération d'un nouveau document</li>";
echo "</ol>";
echo "<p><a href='documents/list.php'>🔗 Retour à la liste des documents</a></p>";
?>
