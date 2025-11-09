# Intégration des Documents Générés dans le Système GED

## Vue d'ensemble

Cette fonctionnalité permet l'enregistrement automatique des contrats, factures et bons de commande générés à partir des modèles directement dans le système de gestion électronique de documents (GED). Les documents générés apparaissent maintenant dans la liste des documents avec tous les boutons et fonctionnalités disponibles.

## Fonctionnalités implémentées

### 1. Enregistrement automatique
- **Double enregistrement** : Les documents sont enregistrés dans `documents_generes` (table existante) ET dans `documents` (table GED)
- **Fichiers physiques** : Création automatique de fichiers HTML/PDF dans le répertoire `/uploads/documents/`
- **Métadonnées complètes** : Nom, taille, type MIME, catégorie, mots-clés, description

### 2. Catégorisation automatique
- **Contrats** → Catégorie "Contrats" (couleur bleue, icône handshake)
- **Factures** → Catégorie "Factures" (couleur verte, icône receipt)
- **Bons de commande** → Catégorie "Bons de commande" (couleur jaune, icône shopping-cart)
- **Création automatique** : Les catégories sont créées automatiquement si elles n'existent pas

### 3. Mots-clés intelligents
- **Type de document** : contrat, facture, bon_commande
- **Données spécifiques** : nom employé, client, fournisseur, numéros
- **Date** : mois/année de génération
- **Contexte** : emploi, facturation, commande

### 4. Interface utilisateur améliorée
- **Boutons complets** : Voir, Détails, Modifier, Archiver, Supprimer
- **Navigation fluide** : Lien direct vers la liste des documents après génération
- **Cohérence visuelle** : Même apparence que les documents uploadés

## Structure de base de données

### Nouvelles colonnes dans `documents`
```sql
source_generation VARCHAR(50) NULL          -- 'template_contrat', 'template_facture', etc.
statut ENUM('actif','archive','supprime')   -- Statut du document
date_archivage TIMESTAMP NULL               -- Date d'archivage
archive_par INT NULL                        -- Utilisateur qui a archivé
raison_archivage TEXT NULL                  -- Raison de l'archivage
date_suppression_prevue TIMESTAMP NULL      -- Date de suppression prévue
```

## Utilisation

### Pour les utilisateurs
1. **Générer un document** : Utiliser les modèles de contrats, factures ou bons de commande
2. **Accéder aux documents** : Cliquer sur "Voir dans mes documents" après génération
3. **Gérer les documents** : Utiliser tous les boutons disponibles (Voir, Modifier, Archiver, etc.)

### Pour les développeurs
```php
// Générer un document avec intégration GED
$template = new Template();
$result = $template->generateDocument($modeleId, $data);

if ($result['success']) {
    $documentGenereId = $result['document_id'];      // ID dans documents_generes
    $gedDocumentId = $result['ged_document_id'];     // ID dans documents (GED)
    $filePath = $result['file_path'];                // Chemin du fichier physique
}
```

## Fichiers modifiés

### Classes
- `classes/Template.php` : Logique de génération et enregistrement
  - `generateDocument()` : Génération avec double enregistrement
  - `saveToDocumentsTable()` : Enregistrement dans la table GED
  - `getCategoryIdByType()` : Gestion des catégories automatiques
  - `generateKeywords()` : Génération de mots-clés intelligents

### Interface
- `templates/generate.php` : Lien vers la liste des documents
- `documents/list.php` : Affichage unifié des documents

### Base de données
- `database/migrations/add_source_generation_column.sql` : Migration
- `database/run_migration.php` : Script de vérification

## Tests

Exécuter le script de test pour vérifier l'intégration :
```bash
php test_integration.php
```

Le script teste :
- Disponibilité des modèles
- Génération d'un document de test
- Enregistrement dans la table documents
- Création du fichier physique
- Catégories automatiques
- Statistiques du système

## Avantages

### Pour les utilisateurs
- **Interface unifiée** : Tous les documents au même endroit
- **Fonctionnalités complètes** : Archivage, modification, suppression
- **Recherche avancée** : Par mots-clés, catégories, dates
- **Traçabilité** : Historique complet des actions

### Pour le système
- **Cohérence** : Même logique pour tous les documents
- **Sécurité** : Permissions et contrôles d'accès uniformes
- **Maintenance** : Code centralisé et réutilisable
- **Évolutivité** : Facilité d'ajout de nouveaux types de documents

## Migration et déploiement

1. **Sauvegarder la base de données**
2. **Exécuter la migration** : `database/migrations/add_source_generation_column.sql`
3. **Vérifier la structure** : `php database/run_migration.php`
4. **Tester l'intégration** : `php test_integration.php`
5. **Déployer les fichiers modifiés**

## Support et maintenance

- **Logs** : Les erreurs sont enregistrées dans les logs système
- **Rollback** : Possibilité de revenir à l'ancien système si nécessaire
- **Monitoring** : Surveillance des générations et enregistrements
- **Optimisation** : Index sur les nouvelles colonnes pour les performances
