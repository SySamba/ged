# Configuration Git - DigiDocs

## Fichiers et Dossiers Ignorés

Ce projet utilise un fichier `.gitignore` pour exclure les fichiers sensibles et temporaires du contrôle de version.

### 🔒 Fichiers de Configuration Sensibles (Ignorés)

- **`config/database.php`** - Configuration de la base de données (mots de passe, etc.)

### 📁 Autres Dossiers Ignorés

- **`uploads/`** - Fichiers uploadés par les utilisateurs
- **`files/`** - Stockage des documents
- **`storage/`** - Fichiers temporaires et cache
- **`logs/`** - Journaux d'activité
- **`cache/`** - Fichiers de cache
- **`vendor/`** - Dépendances Composer
- **`node_modules/`** - Dépendances Node.js

### 🚫 Types de Fichiers Ignorés

- Fichiers d'environnement (`.env`, `.env.local`)
- Logs (`*.log`, `error.log`)
- Fichiers de sauvegarde (`*.bak`, `*.backup`)
- Fichiers système (`.DS_Store`, `Thumbs.db`)
- Fichiers IDE (`.vscode/`, `.idea/`)
- Certificats et clés (`*.pem`, `*.key`, `*.crt`)

## Instructions de Déploiement

### 1. Configuration Initiale

Après avoir cloné le projet, vous devrez créer manuellement :

```bash
# Créer les dossiers nécessaires
mkdir uploads logs cache

# Copier le fichier de configuration de base de données
cp config/database.example.php config/database.php
```

### 2. Configuration de la Base de Données

1. Créer une base de données MySQL
2. Importer le schéma depuis `database/schema.sql`
3. Configurer les paramètres dans `config/database.php`

### 3. Permissions des Dossiers

```bash
# Donner les permissions d'écriture
chmod 755 uploads/
chmod 755 logs/
chmod 755 cache/
chmod 755 storage/
```

## Sécurité

⚠️ **Important** : Le fichier `config/database.php` contient des informations sensibles :
- Mots de passe de base de données
- Paramètres de connexion

Ce fichier ne doit **JAMAIS** être versionné dans Git pour des raisons de sécurité.

## Structure du Projet

```
digidocs/
├── .gitignore              # Fichiers ignorés par Git
├── index.php              # Page d'accueil
├── dashboard.php          # Tableau de bord
├── classes/               # Classes PHP
├── includes/              # Fichiers inclus (navbar, sidebar)
├── documents/             # Gestion des documents
├── templates/             # Modèles Canva
├── jobs/                  # Offres d'emploi
├── admin/                 # Administration
├── assets/                # CSS, JS, images
├── config/                # Configuration
│   ├── database.php       # ⚠️ Config DB (ignoré)
│   └── database.example.php # Exemple de config
├── database/              # Scripts de base de données
├── uploads/               # ⚠️ Fichiers uploadés (ignoré)
└── logs/                  # ⚠️ Journaux (ignoré)
```

## Commandes Git Utiles

```bash
# Vérifier les fichiers ignorés
git status --ignored

# Forcer l'ajout d'un fichier ignoré (si nécessaire)
git add -f fichier.php

# Voir ce qui est ignoré dans un dossier
git check-ignore dossier/*
```
