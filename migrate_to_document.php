<?php
/**
 * Script de migration - Déplacer digidocs vers document
 * 
 * Ce script :
 * 1. Met à jour tous les chemins et URLs dans les fichiers
 * 2. Prépare les fichiers pour le déplacement
 * 3. Fournit les instructions pour finaliser la migration
 */

echo "<h1>🚀 Migration DigiDocs vers Document</h1>";
echo "<p>Ce script va préparer tous les fichiers pour le déplacement.</p>";

// Fichiers à mettre à jour avec leurs nouveaux chemins
$files_to_update = [
    // Fichiers PHP
    'config/config.php' => [
        "define('APP_URL', 'http://localhost/document/digidocs')" => "define('APP_URL', 'http://localhost/document')"
    ],
    'config.example.php' => [
        "define('APP_URL', 'http://localhost/document/digidocs')" => "define('APP_URL', 'http://localhost/document')"
    ],
    'database/migrate_user_profile.php' => [
        "'/document/digidocs/profile.php'" => "'/document/profile.php'",
        "'/document/digidocs/settings.php'" => "'/document/settings.php'"
    ],
    'test_job_creation.php' => [
        "'/document/digidocs/jobs/create.php'" => "'/document/jobs/create.php'"
    ],
    'test_fixes.php' => [
        "'/document/digidocs/profile.php'" => "'/document/profile.php'",
        "'/document/digidocs/settings.php'" => "'/document/settings.php'"
    ],
    'setup_database.php' => [
        "'http://localhost/document/digidocs/'" => "'http://localhost/document/'",
        "'http://localhost/document/digidocs/auth/login.php'" => "'http://localhost/document/auth/login.php'",
        "'http://localhost/document/digidocs/dashboard.php'" => "'http://localhost/document/dashboard.php'"
    ],
    
    // Fichiers HTML
    'fix_summary.html' => [
        '"/document/digidocs/test_job_creation.php"' => '"/document/test_job_creation.php"',
        '"/document/digidocs/jobs/create.php"' => '"/document/jobs/create.php"',
        '"/document/digidocs/jobs/list.php"' => '"/document/jobs/list.php"'
    ],
    'search_link_fixed.html' => [
        '"/document/digidocs/documents/list.php"' => '"/document/documents/list.php"'
    ],
    'search_reverted.html' => [
        '"/document/digidocs/documents/list.php"' => '"/document/documents/list.php"',
        '"/document/digidocs/dashboard.php"' => '"/document/dashboard.php"'
    ],
    'templates/demo_improvements.html' => [
        '"/document/digidocs/templates/view.php?id=1"' => '"/document/templates/view.php?id=1"',
        '"/document/digidocs/templates/list.php"' => '"/document/templates/list.php"'
    ],
    'test_navigation.html' => [
        '"/document/digidocs/jobs/create.php"' => '"/document/jobs/create.php"',
        '"/document/digidocs/documents/upload.php"' => '"/document/documents/upload.php"',
        '"/document/digidocs/documents/list.php"' => '"/document/documents/list.php"',
        '"/document/digidocs/jobs/list.php"' => '"/document/jobs/list.php"',
        '"/document/digidocs/templates/list.php"' => '"/document/templates/list.php"',
        '"/document/digidocs/templates/view.php?id=1"' => '"/document/templates/view.php?id=1"',
        '"/document/digidocs/profile.php"' => '"/document/profile.php"',
        '"/document/digidocs/settings.php"' => '"/document/settings.php"'
    ],
    'test_search_redirections.html' => [
        '"/document/digidocs/documents/list.php"' => '"/document/documents/list.php"',
        '"/document/digidocs/dashboard.php"' => '"/document/dashboard.php"',
        '"/document/digidocs/documents/search.php"' => '"/document/documents/search.php"'
    ]
];

$updated_files = 0;
$errors = [];

echo "<h2>📝 Mise à jour des fichiers...</h2>";

foreach ($files_to_update as $file => $replacements) {
    $file_path = __DIR__ . '/' . $file;
    
    if (!file_exists($file_path)) {
        $errors[] = "Fichier non trouvé : $file";
        continue;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
    
    if ($content !== $original_content) {
        if (file_put_contents($file_path, $content)) {
            echo "<p style='color: green;'>✅ Mis à jour : $file</p>";
            $updated_files++;
        } else {
            $errors[] = "Erreur d'écriture : $file";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Aucun changement : $file</p>";
    }
}

echo "<h2>📊 Résumé</h2>";
echo "<p><strong>Fichiers mis à jour :</strong> $updated_files</p>";

if (!empty($errors)) {
    echo "<h3 style='color: red;'>❌ Erreurs :</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>• $error</p>";
    }
}

echo "<h2>📋 Instructions pour finaliser la migration</h2>";
echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 10px; border-left: 5px solid #2563eb;'>";
echo "<h3>Étapes à suivre :</h3>";
echo "<ol>";
echo "<li><strong>Arrêter le serveur web</strong> (Apache/Nginx)</li>";
echo "<li><strong>Sauvegarder</strong> le dossier actuel (optionnel mais recommandé)</li>";
echo "<li><strong>Déplacer tous les fichiers</strong> de <code>C:\\xampp\\htdocs\\document\\digidocs\\</code> vers <code>C:\\xampp\\htdocs\\document\\</code></li>";
echo "<li><strong>Supprimer</strong> le dossier vide <code>digidocs</code></li>";
echo "<li><strong>Redémarrer</strong> le serveur web</li>";
echo "<li><strong>Tester</strong> l'accès via <code>http://localhost/document/</code></li>";
echo "</ol>";
echo "</div>";

echo "<h2>🖥️ Commandes PowerShell pour le déplacement</h2>";
echo "<div style='background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 8px; font-family: monospace;'>";
echo "<p># Aller dans le dossier document</p>";
echo "<p>cd C:\\xampp\\htdocs\\document</p>";
echo "<p></p>";
echo "<p># Déplacer tous les fichiers de digidocs vers document</p>";
echo "<p>Move-Item -Path .\\digidocs\\* -Destination . -Force</p>";
echo "<p></p>";
echo "<p># Supprimer le dossier vide digidocs</p>";
echo "<p>Remove-Item -Path .\\digidocs -Force</p>";
echo "</div>";

echo "<h2>🔗 Nouvelles URLs après migration</h2>";
echo "<ul>";
echo "<li><strong>Accueil :</strong> <code>http://localhost/document/</code></li>";
echo "<li><strong>Dashboard :</strong> <code>http://localhost/document/dashboard.php</code></li>";
echo "<li><strong>Connexion :</strong> <code>http://localhost/document/auth/login.php</code></li>";
echo "<li><strong>Documents :</strong> <code>http://localhost/document/documents/list.php</code></li>";
echo "<li><strong>Templates :</strong> <code>http://localhost/document/templates/list.php</code></li>";
echo "<li><strong>Emplois :</strong> <code>http://localhost/document/jobs/list.php</code></li>";
echo "</ul>";

echo "<h2>⚠️ Important</h2>";
echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 5px solid #f59e0b;'>";
echo "<p><strong>Après le déplacement :</strong></p>";
echo "<ul>";
echo "<li>Vérifiez que tous les liens fonctionnent</li>";
echo "<li>Testez l'upload de documents</li>";
echo "<li>Vérifiez la connexion à la base de données</li>";
echo "<li>Supprimez ce fichier de migration</li>";
echo "</ul>";
echo "</div>";

echo "<p style='margin-top: 30px; text-align: center;'>";
echo "<strong>✅ Préparation terminée ! Vous pouvez maintenant effectuer le déplacement.</strong>";
echo "</p>";
?>
