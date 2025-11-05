# Script PowerShell pour migrer DigiDocs vers Document
# Auteur: Assistant IA
# Date: $(Get-Date)

Write-Host "🚀 Migration DigiDocs vers Document" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan

# Vérifier que nous sommes dans le bon dossier
$currentPath = Get-Location
$expectedPath = "C:\xampp\htdocs\document"

if ($currentPath.Path -ne $expectedPath) {
    Write-Host "⚠️ Changement vers le dossier document..." -ForegroundColor Yellow
    Set-Location -Path $expectedPath
}

# Vérifier que le dossier digidocs existe
if (-not (Test-Path ".\digidocs")) {
    Write-Host "❌ Erreur: Le dossier digidocs n'existe pas!" -ForegroundColor Red
    exit 1
}

# Demander confirmation
Write-Host ""
Write-Host "Cette opération va:" -ForegroundColor Yellow
Write-Host "• Déplacer tous les fichiers de .\digidocs\ vers ." -ForegroundColor Yellow
Write-Host "• Supprimer le dossier digidocs vide" -ForegroundColor Yellow
Write-Host "• Changer l'URL d'accès de /document/digidocs/ vers /document/" -ForegroundColor Yellow
Write-Host ""

$confirmation = Read-Host "Voulez-vous continuer? (O/N)"
if ($confirmation -ne "O" -and $confirmation -ne "o" -and $confirmation -ne "Y" -and $confirmation -ne "y") {
    Write-Host "❌ Migration annulée." -ForegroundColor Red
    exit 0
}

Write-Host ""
Write-Host "📋 Début de la migration..." -ForegroundColor Green

# Étape 1: Créer une sauvegarde (optionnel)
Write-Host "1️⃣ Création d'une sauvegarde..." -ForegroundColor Blue
$backupPath = "C:\xampp\htdocs\document_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
try {
    Copy-Item -Path "." -Destination $backupPath -Recurse -Force
    Write-Host "   ✅ Sauvegarde créée: $backupPath" -ForegroundColor Green
} catch {
    Write-Host "   ⚠️ Impossible de créer la sauvegarde: $($_.Exception.Message)" -ForegroundColor Yellow
    $continueWithoutBackup = Read-Host "   Continuer sans sauvegarde? (O/N)"
    if ($continueWithoutBackup -ne "O" -and $continueWithoutBackup -ne "o") {
        Write-Host "❌ Migration annulée." -ForegroundColor Red
        exit 1
    }
}

# Étape 2: Lister les fichiers à déplacer
Write-Host "2️⃣ Analyse des fichiers à déplacer..." -ForegroundColor Blue
$filesToMove = Get-ChildItem -Path ".\digidocs" -Recurse
$fileCount = $filesToMove.Count
Write-Host "   📁 $fileCount éléments trouvés" -ForegroundColor Green

# Étape 3: Déplacer les fichiers
Write-Host "3️⃣ Déplacement des fichiers..." -ForegroundColor Blue
try {
    # Déplacer tous les éléments de digidocs vers le dossier parent
    Get-ChildItem -Path ".\digidocs" | ForEach-Object {
        $destination = Join-Path -Path "." -ChildPath $_.Name
        if (Test-Path $destination) {
            Write-Host "   ⚠️ $($_.Name) existe déjà, remplacement..." -ForegroundColor Yellow
            Remove-Item -Path $destination -Recurse -Force
        }
        Move-Item -Path $_.FullName -Destination "." -Force
        Write-Host "   ✅ Déplacé: $($_.Name)" -ForegroundColor Green
    }
} catch {
    Write-Host "   ❌ Erreur lors du déplacement: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   💡 Restauration depuis la sauvegarde recommandée!" -ForegroundColor Yellow
    exit 1
}

# Étape 4: Supprimer le dossier digidocs vide
Write-Host "4️⃣ Suppression du dossier digidocs..." -ForegroundColor Blue
try {
    Remove-Item -Path ".\digidocs" -Force
    Write-Host "   ✅ Dossier digidocs supprimé" -ForegroundColor Green
} catch {
    Write-Host "   ⚠️ Impossible de supprimer le dossier digidocs: $($_.Exception.Message)" -ForegroundColor Yellow
}

# Étape 5: Vérification
Write-Host "5️⃣ Vérification de la migration..." -ForegroundColor Blue
$criticalFiles = @("index.php", "dashboard.php", "config\config.php")
$allFilesPresent = $true

foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Host "   ✅ $file présent" -ForegroundColor Green
    } else {
        Write-Host "   ❌ $file manquant!" -ForegroundColor Red
        $allFilesPresent = $false
    }
}

# Résumé final
Write-Host ""
Write-Host "🎉 MIGRATION TERMINÉE!" -ForegroundColor Green
Write-Host "=====================" -ForegroundColor Green
Write-Host ""

if ($allFilesPresent) {
    Write-Host "✅ Tous les fichiers critiques sont présents" -ForegroundColor Green
    Write-Host ""
    Write-Host "🔗 Nouvelles URLs d'accès:" -ForegroundColor Cyan
    Write-Host "   • Accueil: http://localhost/document/" -ForegroundColor White
    Write-Host "   • Dashboard: http://localhost/document/dashboard.php" -ForegroundColor White
    Write-Host "   • Connexion: http://localhost/document/auth/login.php" -ForegroundColor White
    Write-Host ""
    Write-Host "📋 Prochaines étapes:" -ForegroundColor Yellow
    Write-Host "   1. Redémarrer Apache/Nginx si nécessaire" -ForegroundColor White
    Write-Host "   2. Tester l'accès via http://localhost/document/" -ForegroundColor White
    Write-Host "   3. Vérifier toutes les fonctionnalités" -ForegroundColor White
    Write-Host "   4. Supprimer les fichiers de migration si tout fonctionne" -ForegroundColor White
    
    if (Test-Path $backupPath) {
        Write-Host "   5. Supprimer la sauvegarde: $backupPath" -ForegroundColor White
    }
} else {
    Write-Host "⚠️ Certains fichiers semblent manquer!" -ForegroundColor Red
    Write-Host "💡 Vérifiez manuellement ou restaurez depuis la sauvegarde" -ForegroundColor Yellow
    if (Test-Path $backupPath) {
        Write-Host "📁 Sauvegarde disponible: $backupPath" -ForegroundColor Cyan
    }
}

Write-Host ""
Write-Host "Appuyez sur Entrée pour fermer..." -ForegroundColor Gray
Read-Host
