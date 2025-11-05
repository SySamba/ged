# 🚀 Guide de Migration - DigiDocs vers Document

## Objectif
Déplacer tout le contenu du dossier `digidocs` directement dans le dossier `document` pour simplifier l'URL d'accès.

## Avant / Après

### Avant la migration :
- **Structure :** `C:\xampp\htdocs\document\digidocs\`
- **URL d'accès :** `http://localhost/document/digidocs/`

### Après la migration :
- **Structure :** `C:\xampp\htdocs\document\`
- **URL d'accès :** `http://localhost/document/`

## ✅ Préparatifs (Déjà effectués)

Les fichiers suivants ont été mis à jour avec les nouveaux chemins :

- ✅ `config/config.php` - URL de l'application mise à jour
- ✅ `config.example.php` - URL d'exemple mise à jour
- ✅ Fichiers de test et HTML - Liens mis à jour

## 📋 Instructions de Migration

### Étape 1 : Arrêter le serveur web
```bash
# Arrêter Apache dans XAMPP
# Ou redémarrer le service web
```

### Étape 2 : Sauvegarder (Optionnel mais recommandé)
```powershell
# Créer une sauvegarde
Copy-Item -Path "C:\xampp\htdocs\document" -Destination "C:\xampp\htdocs\document_backup" -Recurse
```

### Étape 3 : Effectuer le déplacement
```powershell
# Aller dans le dossier document
cd C:\xampp\htdocs\document

# Déplacer tous les fichiers de digidocs vers document
Move-Item -Path .\digidocs\* -Destination . -Force

# Supprimer le dossier vide digidocs
Remove-Item -Path .\digidocs -Force
```

### Étape 4 : Redémarrer le serveur web
```bash
# Redémarrer Apache dans XAMPP
```

### Étape 5 : Tester l'accès
Vérifiez que ces URLs fonctionnent :
- `http://localhost/document/` (page d'accueil)
- `http://localhost/document/dashboard.php` (tableau de bord)
- `http://localhost/document/auth/login.php` (connexion)

## 🔗 Nouvelles URLs

| Page | Ancienne URL | Nouvelle URL |
|------|-------------|-------------|
| Accueil | `/document/digidocs/` | `/document/` |
| Dashboard | `/document/digidocs/dashboard.php` | `/document/dashboard.php` |
| Connexion | `/document/digidocs/auth/login.php` | `/document/auth/login.php` |
| Documents | `/document/digidocs/documents/list.php` | `/document/documents/list.php` |
| Upload | `/document/digidocs/documents/upload.php` | `/document/documents/upload.php` |
| Templates | `/document/digidocs/templates/list.php` | `/document/templates/list.php` |
| Emplois | `/document/digidocs/jobs/list.php` | `/document/jobs/list.php` |
| Profil | `/document/digidocs/profile.php` | `/document/profile.php` |
| Paramètres | `/document/digidocs/settings.php` | `/document/settings.php` |

## 📁 Structure finale

Après migration, la structure sera :
```
C:\xampp\htdocs\document\
├── .gitignore
├── index.php
├── dashboard.php
├── profile.php
├── settings.php
├── config/
│   ├── config.php
│   ├── database.php
│   └── database.example.php
├── database/
│   ├── schema.sql
│   └── migrate_user_profile.php
├── classes/
├── includes/
├── documents/
├── templates/
├── jobs/
├── admin/
├── auth/
├── assets/
├── uploads/
└── logs/
```

## ✅ Tests à effectuer après migration

1. **Accès général :**
   - [ ] Page d'accueil accessible via `http://localhost/document/`
   - [ ] Navigation entre les pages fonctionne

2. **Authentification :**
   - [ ] Connexion fonctionne
   - [ ] Déconnexion fonctionne
   - [ ] Sessions maintenues

3. **Fonctionnalités principales :**
   - [ ] Upload de documents
   - [ ] Recherche de documents
   - [ ] Génération de templates
   - [ ] Gestion des emplois

4. **Base de données :**
   - [ ] Connexion à la base de données OK
   - [ ] Opérations CRUD fonctionnent

## 🚨 En cas de problème

### Problème : Page blanche ou erreur 404
**Solution :** Vérifiez que tous les fichiers ont bien été déplacés et que le serveur web pointe vers le bon dossier.

### Problème : Erreur de base de données
**Solution :** Vérifiez le fichier `config/database.php` et assurez-vous que les paramètres sont corrects.

### Problème : Liens cassés
**Solution :** Vérifiez que tous les fichiers ont été mis à jour avec les nouveaux chemins.

### Restauration d'urgence
```powershell
# Si vous avez fait une sauvegarde
Remove-Item -Path "C:\xampp\htdocs\document" -Recurse -Force
Move-Item -Path "C:\xampp\htdocs\document_backup" -Destination "C:\xampp\htdocs\document"
```

## 📝 Nettoyage post-migration

Après avoir vérifié que tout fonctionne :
1. Supprimer le fichier `migrate_to_document.php`
2. Supprimer ce guide `MIGRATION_GUIDE.md`
3. Supprimer la sauvegarde si elle a été créée
4. Mettre à jour vos signets/favoris avec les nouvelles URLs

## 🎉 Migration terminée !

Une fois tous les tests passés, votre application DigiDocs sera accessible directement via `http://localhost/document/` avec une URL plus propre et plus simple !
