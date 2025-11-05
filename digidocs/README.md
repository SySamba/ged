# DigiDocs - Système de Gestion Électronique des Documents pour PME

## 📋 Description

DigiDocs est une solution complète de gestion électronique des documents spécialement conçue pour les PME sénégalaises. Le système permet de digitaliser et centraliser la gestion des documents avec des fonctionnalités avancées de recherche, de modèles prédéfinis et de gestion des offres d'emploi.

## 🚀 Fonctionnalités principales

### 📁 Gestion des documents
- **Upload sécurisé** : Support des formats PDF, Word, Excel et images
- **Catégorisation intelligente** : Organisation par catégories personnalisables
- **Recherche avancée** : Recherche par mot-clé, catégorie, date et utilisateur
- **Gestion des droits** : Permissions différenciées selon les rôles utilisateur
- **Stockage sécurisé** : Fichiers protégés avec noms sécurisés

### 🎨 Modèles Canva intégrés
- **Contrats de travail** : Modèles standardisés avec champs variables
- **Factures commerciales** : Calcul automatique des totaux et TVA
- **Bons de commande** : Gestion des articles et fournisseurs
- **Génération PDF** : Export des documents en format imprimable

### 💼 Gestion des offres d'emploi
- **Publication d'offres** : Interface complète pour créer des annonces
- **Candidatures en ligne** : Réception et gestion des CV
- **Suivi des candidatures** : Statuts et historique des réponses
- **Notifications automatiques** : Accusés de réception

### 👥 Gestion des utilisateurs
- **Authentification sécurisée** : Système de connexion avec bcrypt
- **Rôles et permissions** : Admin et employés avec droits différenciés
- **Journalisation** : Suivi de toutes les activités utilisateur
- **Profils personnalisés** : Gestion des informations personnelles

## 🛠 Technologies utilisées

- **Backend** : PHP 7.4+ avec PDO
- **Base de données** : MySQL 5.7+
- **Frontend** : HTML5, CSS3, JavaScript ES6
- **Framework CSS** : Bootstrap 5.3
- **Icônes** : Font Awesome 6.0
- **Architecture** : MVC avec classes PHP

## 📦 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP : PDO, GD, FileInfo, JSON
- Serveur web (Apache/Nginx)

### Étapes d'installation

1. **Cloner ou télécharger le projet**
   ```bash
   git clone [url-du-projet]
   cd digidocs
   ```

2. **Configuration de la base de données**
   - Créer une base de données MySQL nommée `digidocs`
   - Modifier les paramètres de connexion dans `config/database.php` si nécessaire

3. **Installation via l'interface web**
   - Accéder à `http://localhost/digidocs/install.php`
   - Suivre les étapes d'installation
   - Le script créera automatiquement les tables et l'utilisateur admin

4. **Compte administrateur par défaut**
   - **Email** : sambasy837@gmail.com
   - **Mot de passe** : Touba2021@

5. **Sécurisation**
   - Supprimer le fichier `install.php` après installation
   - Modifier les permissions des dossiers d'upload (755)
   - Changer le mot de passe admin par défaut

## 📁 Structure du projet

```
digidocs/
├── assets/                 # Ressources CSS, JS, images
│   └── css/
│       └── dashboard.css
├── auth/                   # Pages d'authentification
│   ├── login.php
│   └── logout.php
├── classes/                # Classes PHP métier
│   ├── User.php
│   ├── Document.php
│   ├── Category.php
│   ├── Template.php
│   └── Job.php
├── config/                 # Configuration
│   ├── config.php
│   └── database.php
├── database/               # Scripts SQL
│   └── schema.sql
├── documents/              # Gestion des documents
│   ├── list.php
│   ├── upload.php
│   ├── download.php
│   └── delete.php
├── includes/               # Fichiers d'inclusion
│   ├── navbar.php
│   └── sidebar.php
├── jobs/                   # Gestion des emplois
│   └── list.php
├── templates/              # Modèles Canva
│   ├── list.php
│   ├── generate.php
│   ├── view.php
│   └── download.php
├── uploads/                # Fichiers uploadés
│   ├── documents/
│   ├── cv/
│   ├── generated/
│   └── temp/
├── dashboard.php           # Tableau de bord principal
├── index.php              # Page d'accueil
├── install.php            # Script d'installation
└── README.md              # Documentation
```

## 🔧 Configuration

### Paramètres principaux (config/config.php)
- `MAX_FILE_SIZE` : Taille maximale des fichiers (10MB par défaut)
- `ALLOWED_FILE_TYPES` : Types de fichiers autorisés
- `SESSION_LIFETIME` : Durée de session (1 heure)
- `APP_URL` : URL de base de l'application

### Base de données
- 8 tables principales avec relations optimisées
- Index pour les performances de recherche
- Contraintes de sécurité et intégrité

## 👤 Utilisation

### Pour les administrateurs
1. **Gestion des utilisateurs** : Créer et gérer les comptes employés
2. **Configuration des catégories** : Organiser la classification des documents
3. **Supervision des activités** : Consulter les journaux d'activité
4. **Gestion des offres d'emploi** : Publier et gérer les annonces

### Pour les employés
1. **Upload de documents** : Télécharger et catégoriser les fichiers
2. **Recherche et consultation** : Trouver rapidement les documents
3. **Utilisation des modèles** : Générer des contrats, factures, bons de commande
4. **Candidatures** : Postuler aux offres d'emploi internes

## 🔒 Sécurité

- **Authentification** : Hachage bcrypt des mots de passe
- **Sessions sécurisées** : Gestion avancée des sessions PHP
- **Validation des fichiers** : Vérification des types MIME et extensions
- **Permissions granulaires** : Contrôle d'accès basé sur les rôles
- **Journalisation** : Traçabilité de toutes les actions
- **Protection CSRF** : Tokens de sécurité sur les formulaires sensibles

## 📊 Fonctionnalités avancées

### Recherche intelligente
- Recherche full-text dans les contenus
- Filtres multiples (catégorie, date, utilisateur)
- Pagination optimisée
- Tri personnalisable

### Modèles Canva
- Templates HTML personnalisables
- Champs variables avec validation
- Calculs automatiques (totaux, TVA)
- Export PDF intégré

### Gestion des emplois
- Publication d'offres avec dates limites
- Réception de candidatures avec CV
- Workflow de validation
- Notifications automatiques

## 🚀 Déploiement en production

1. **Serveur web** : Configurer Apache/Nginx avec PHP-FPM
2. **Base de données** : MySQL avec sauvegarde automatique
3. **SSL/HTTPS** : Certificat SSL obligatoire
4. **Monitoring** : Logs d'erreur et surveillance
5. **Sauvegarde** : Script automatique des données et fichiers

## 🤝 Support et maintenance

### Maintenance préventive
- Nettoyage des logs anciens (90 jours par défaut)
- Optimisation des index de base de données
- Vérification de l'intégrité des fichiers
- Mise à jour des dépendances

### Dépannage courant
- **Erreur de connexion DB** : Vérifier les paramètres dans `config/database.php`
- **Upload impossible** : Contrôler les permissions des dossiers
- **Session expirée** : Ajuster `SESSION_LIFETIME` si nécessaire

## 📈 Évolutions futures

- [ ] API REST pour intégrations externes
- [ ] Application mobile companion
- [ ] Signature électronique des documents
- [ ] Workflow d'approbation avancé
- [ ] Intégration avec services cloud (Google Drive, Dropbox)
- [ ] Reconnaissance OCR pour les documents scannés
- [ ] Tableau de bord analytique avancé

## 📝 Licence

Ce projet est développé pour les PME sénégalaises dans le cadre de la digitalisation des processus documentaires.

## 👨‍💻 Développeur

Développé par l'équipe DigiDocs pour répondre aux besoins spécifiques des PME sénégalaises.

---

**Version** : 1.0.0  
**Date de création** : Novembre 2024  
**Dernière mise à jour** : Novembre 2024
