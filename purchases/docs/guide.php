<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

$page_title = "Guide d'utilisation - Module d'achat";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .guide-section {
            margin-bottom: 2rem;
        }
        .step-number {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .workflow-step {
            border-left: 3px solid #007bff;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-book-open me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au module
                        </a>
                    </div>
                </div>

                <!-- Table des matières -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Table des matières</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="#introduction">1. Introduction</a></li>
                                    <li><a href="#workflow">2. Workflow du cycle d'achat</a></li>
                                    <li><a href="#demandes">3. Gestion des demandes d'achat</a></li>
                                    <li><a href="#fournisseurs">4. Gestion des fournisseurs</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="#commandes">5. Bons de commande</a></li>
                                    <li><a href="#suivi">6. Suivi et rapports</a></li>
                                    <li><a href="#bonnes-pratiques">7. Bonnes pratiques</a></li>
                                    <li><a href="#faq">8. FAQ</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Introduction -->
                <div id="introduction" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle me-2"></i>1. Introduction</h4>
                        </div>
                        <div class="card-body">
                            <p>Le module de gestion des achats de DigiDocs vous permet de gérer l'ensemble du cycle d'achat de votre entreprise, depuis la demande initiale jusqu'à la comptabilisation finale.</p>
                            
                            <h5>Fonctionnalités principales :</h5>
                            <ul>
                                <li><strong>Demandes d'achat</strong> : Création et validation des besoins</li>
                                <li><strong>Gestion des fournisseurs</strong> : Base de données centralisée</li>
                                <li><strong>Bons de commande</strong> : Génération et suivi</li>
                                <li><strong>Suivi du workflow</strong> : Visibilité complète du processus</li>
                                <li><strong>Rapports</strong> : Analytics et tableaux de bord</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Workflow -->
                <div id="workflow" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-route me-2"></i>2. Workflow du cycle d'achat</h4>
                        </div>
                        <div class="card-body">
                            <p>Le cycle d'achat suit un processus structuré en 6 étapes principales :</p>
                            
                            <div class="workflow-step">
                                <div class="step-number">1</div>
                                <strong>Demande d'achat</strong>
                                <p class="mb-0">Création de la demande par le demandeur avec justification du besoin.</p>
                            </div>
                            
                            <div class="workflow-step">
                                <div class="step-number">2</div>
                                <strong>Approbation</strong>
                                <p class="mb-0">Validation par le responsable selon les règles d'approbation.</p>
                            </div>
                            
                            <div class="workflow-step">
                                <div class="step-number">3</div>
                                <strong>Bon de commande</strong>
                                <p class="mb-0">Génération et envoi du bon de commande au fournisseur.</p>
                            </div>
                            
                            <div class="workflow-step">
                                <div class="step-number">4</div>
                                <strong>Réception</strong>
                                <p class="mb-0">Contrôle et validation de la réception des marchandises.</p>
                            </div>
                            
                            <div class="workflow-step">
                                <div class="step-number">5</div>
                                <strong>Facturation</strong>
                                <p class="mb-0">Réception et validation de la facture fournisseur.</p>
                            </div>
                            
                            <div class="workflow-step">
                                <div class="step-number">6</div>
                                <strong>Comptabilisation</strong>
                                <p class="mb-0">Génération des écritures comptables et paiement.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demandes d'achat -->
                <div id="demandes" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-file-alt me-2"></i>3. Gestion des demandes d'achat</h4>
                        </div>
                        <div class="card-body">
                            <h5>Créer une nouvelle demande :</h5>
                            <ol>
                                <li>Accédez à <strong>Gestion des Achats > Nouvelle demande</strong></li>
                                <li>Remplissez les informations obligatoires :
                                    <ul>
                                        <li>Département</li>
                                        <li>Titre de la demande</li>
                                        <li>Justification</li>
                                        <li>Priorité</li>
                                    </ul>
                                </li>
                                <li>Enregistrez la demande</li>
                            </ol>

                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Conseil :</strong> Une justification claire et précise accélère le processus d'approbation.
                            </div>

                            <h5>Statuts des demandes :</h5>
                            <ul>
                                <li><span class="badge bg-secondary">Brouillon</span> : Demande en cours de rédaction</li>
                                <li><span class="badge bg-info">Soumise</span> : Demande envoyée pour approbation</li>
                                <li><span class="badge bg-success">Approuvée</span> : Demande validée, peut générer un bon de commande</li>
                                <li><span class="badge bg-danger">Rejetée</span> : Demande refusée</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Fournisseurs -->
                <div id="fournisseurs" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-building me-2"></i>4. Gestion des fournisseurs</h4>
                        </div>
                        <div class="card-body">
                            <h5>Ajouter un nouveau fournisseur :</h5>
                            <ol>
                                <li>Accédez à <strong>Gestion des Achats > Fournisseurs > Nouveau fournisseur</strong></li>
                                <li>Remplissez les informations :
                                    <ul>
                                        <li><strong>Obligatoire :</strong> Nom de l'entreprise</li>
                                        <li><strong>Recommandé :</strong> Email, téléphone, adresse</li>
                                        <li><strong>Commercial :</strong> Délai de paiement, numéro de TVA</li>
                                    </ul>
                                </li>
                                <li>Définissez le statut (Actif/Inactif)</li>
                            </ol>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important :</strong> Seuls les fournisseurs actifs apparaissent dans les listes de sélection.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bons de commande -->
                <div id="commandes" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-shopping-cart me-2"></i>5. Bons de commande</h4>
                        </div>
                        <div class="card-body">
                            <h5>Créer un bon de commande :</h5>
                            <ol>
                                <li>Accédez à <strong>Gestion des Achats > Bons de commande > Nouveau bon</strong></li>
                                <li>Sélectionnez le fournisseur</li>
                                <li>Liez à une demande d'achat (optionnel)</li>
                                <li>Définissez les conditions :
                                    <ul>
                                        <li>Date de commande</li>
                                        <li>Date de livraison souhaitée</li>
                                        <li>Adresse de livraison</li>
                                        <li>Conditions de paiement</li>
                                    </ul>
                                </li>
                            </ol>

                            <h5>Statuts des bons de commande :</h5>
                            <ul>
                                <li><span class="badge bg-secondary">Brouillon</span> : En cours de préparation</li>
                                <li><span class="badge bg-warning">Envoyée</span> : Transmise au fournisseur</li>
                                <li><span class="badge bg-success">Confirmée</span> : Acceptée par le fournisseur</li>
                                <li><span class="badge bg-primary">Terminée</span> : Marchandises reçues</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Suivi et rapports -->
                <div id="suivi" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-chart-bar me-2"></i>6. Suivi et rapports</h4>
                        </div>
                        <div class="card-body">
                            <h5>Suivi du workflow :</h5>
                            <p>La page <strong>Suivi du cycle d'achat</strong> vous permet de :</p>
                            <ul>
                                <li>Visualiser la progression de chaque demande</li>
                                <li>Identifier les blocages</li>
                                <li>Suivre les indicateurs clés</li>
                            </ul>

                            <h5>Rapports disponibles :</h5>
                            <ul>
                                <li><strong>Tableau de bord</strong> : Vue d'ensemble avec KPIs</li>
                                <li><strong>Évolution mensuelle</strong> : Graphiques de tendances</li>
                                <li><strong>Top fournisseurs</strong> : Classement par volume</li>
                                <li><strong>Analyse par priorité</strong> : Répartition des montants</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bonnes pratiques -->
                <div id="bonnes-pratiques" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-star me-2"></i>7. Bonnes pratiques</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-check-circle text-success me-2"></i>À faire :</h5>
                                    <ul>
                                        <li>Justifier clairement chaque demande</li>
                                        <li>Maintenir les informations fournisseurs à jour</li>
                                        <li>Utiliser les priorités de manière cohérente</li>
                                        <li>Suivre régulièrement l'avancement</li>
                                        <li>Archiver les documents importants</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-times-circle text-danger me-2"></i>À éviter :</h5>
                                    <ul>
                                        <li>Demandes sans justification</li>
                                        <li>Fournisseurs incomplets</li>
                                        <li>Abus de la priorité "Urgente"</li>
                                        <li>Oubli de mise à jour des statuts</li>
                                        <li>Commandes sans validation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div id="faq" class="guide-section">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-question-circle me-2"></i>8. Questions fréquentes (FAQ)</h4>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq1">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                            Comment modifier une demande déjà soumise ?
                                        </button>
                                    </h2>
                                    <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Une demande soumise ne peut pas être modifiée directement. Contactez votre responsable pour qu'il la rejette, puis créez une nouvelle demande avec les corrections nécessaires.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                            Puis-je créer un bon de commande sans demande d'achat ?
                                        </button>
                                    </h2>
                                    <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Oui, pour les achats récurrents ou urgents, vous pouvez créer directement un bon de commande. Cependant, il est recommandé de toujours passer par une demande d'achat pour la traçabilité.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq3">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                            Comment ajouter des articles à un bon de commande ?
                                        </button>
                                    </h2>
                                    <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Cette fonctionnalité sera disponible dans une prochaine version. Pour l'instant, précisez les articles dans les notes du bon de commande.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq4">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                            Les données sont-elles sauvegardées automatiquement ?
                                        </button>
                                    </h2>
                                    <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Oui, toutes les données sont automatiquement sauvegardées dans la base de données dès que vous cliquez sur "Enregistrer". Aucune sauvegarde manuelle n'est nécessaire.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact support -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-life-ring me-2"></i>Besoin d'aide ?</h5>
                        <p>Si vous ne trouvez pas la réponse à votre question, n'hésitez pas à contacter le support.</p>
                        <a href="mailto:support@teranganumerique.com" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Contacter le support
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
