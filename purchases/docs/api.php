<?php
require_once __DIR__ . '/../../config/config.php';
requireLogin();

$page_title = "Documentation API - Module d'achat";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DigiDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-code me-2"></i><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au module
                        </a>
                    </div>
                </div>

                <!-- Introduction -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle me-2"></i>Introduction</h4>
                    </div>
                    <div class="card-body">
                        <p>Cette documentation présente les endpoints API disponibles pour le module de gestion des achats.</p>
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Note :</strong> Toutes les requêtes API nécessitent une authentification valide.
                        </div>
                    </div>
                </div>

                <!-- Endpoints Statistiques -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar me-2"></i>Statistiques</h4>
                    </div>
                    <div class="card-body">
                        <h5>GET /purchases/api/statistics.php</h5>
                        <p>Récupère les statistiques générales du module d'achat.</p>
                        
                        <h6>Réponse :</h6>
                        <pre><code class="language-json">{
  "pending_requests": 5,
  "active_orders": 3,
  "pending_invoices": 2,
  "monthly_budget": "15000.00"
}</code></pre>

                        <h6>Exemple d'utilisation :</h6>
                        <pre><code class="language-javascript">fetch('/purchases/api/statistics.php')
  .then(response => response.json())
  .then(data => {
    console.log('Statistiques:', data);
  });</code></pre>
                    </div>
                </div>

                <!-- Endpoints Activités -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-history me-2"></i>Activités récentes</h4>
                    </div>
                    <div class="card-body">
                        <h5>GET /purchases/api/recent_activities.php</h5>
                        <p>Récupère la liste des activités récentes.</p>
                        
                        <h6>Réponse :</h6>
                        <pre><code class="language-json">[
  {
    "title": "Nouvelle demande d'achat",
    "description": "Demande DA2024001 créée",
    "user": "Jean Dupont",
    "date": "2024-11-17 14:30:00"
  },
  {
    "title": "Bon de commande validé",
    "description": "BC2024001 envoyé au fournisseur",
    "user": "Marie Martin",
    "date": "2024-11-17 13:15:00"
  }
]</code></pre>
                    </div>
                </div>

                <!-- Structure des données -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-database me-2"></i>Structure des données</h4>
                    </div>
                    <div class="card-body">
                        <h5>Demande d'achat (Purchase Request)</h5>
                        <pre><code class="language-json">{
  "id": 1,
  "request_number": "DA2024001",
  "requester_id": 5,
  "department": "IT",
  "title": "Achat ordinateurs portables",
  "description": "Renouvellement du parc informatique",
  "justification": "Matériel obsolète",
  "priority": "medium",
  "status": "draft",
  "total_amount": 5000.00,
  "currency": "EUR",
  "requested_date": "2024-12-01",
  "created_at": "2024-11-17 14:30:00"
}</code></pre>

                        <h5>Fournisseur (Supplier)</h5>
                        <pre><code class="language-json">{
  "id": 1,
  "name": "TechCorp SARL",
  "contact_person": "Pierre Durand",
  "email": "contact@techcorp.fr",
  "phone": "01 23 45 67 89",
  "address": "123 Rue de la Tech",
  "city": "Paris",
  "postal_code": "75001",
  "country": "France",
  "tax_number": "FR12345678901",
  "payment_terms": 30,
  "status": "active"
}</code></pre>

                        <h5>Bon de commande (Purchase Order)</h5>
                        <pre><code class="language-json">{
  "id": 1,
  "order_number": "BC2024001",
  "supplier_id": 1,
  "request_id": 1,
  "order_date": "2024-11-17",
  "delivery_date": "2024-12-01",
  "delivery_address": "123 Avenue de l'Entreprise",
  "payment_terms": 30,
  "currency": "EUR",
  "total_amount": 5000.00,
  "status": "draft",
  "notes": "Livraison en urgence"
}</code></pre>
                    </div>
                </div>

                <!-- Codes de statut -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-tags me-2"></i>Codes de statut</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Demandes d'achat</h5>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-secondary">draft</span> Brouillon</li>
                                    <li><span class="badge bg-info">submitted</span> Soumise</li>
                                    <li><span class="badge bg-success">approved</span> Approuvée</li>
                                    <li><span class="badge bg-danger">rejected</span> Rejetée</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h5>Bons de commande</h5>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-secondary">draft</span> Brouillon</li>
                                    <li><span class="badge bg-warning">sent</span> Envoyée</li>
                                    <li><span class="badge bg-success">confirmed</span> Confirmée</li>
                                    <li><span class="badge bg-primary">completed</span> Terminée</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h5>Priorités</h5>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-success">low</span> Faible</li>
                                    <li><span class="badge bg-warning">medium</span> Moyenne</li>
                                    <li><span class="badge bg-danger">high</span> Élevée</li>
                                    <li><span class="badge bg-dark">urgent</span> Urgente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gestion des erreurs -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Gestion des erreurs</h4>
                    </div>
                    <div class="card-body">
                        <h5>Codes d'erreur HTTP</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Exemple</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">200</span></td>
                                        <td>Succès</td>
                                        <td>Données récupérées avec succès</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">400</span></td>
                                        <td>Requête incorrecte</td>
                                        <td>Paramètres manquants ou invalides</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">401</span></td>
                                        <td>Non autorisé</td>
                                        <td>Authentification requise</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">403</span></td>
                                        <td>Interdit</td>
                                        <td>Permissions insuffisantes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-danger">500</span></td>
                                        <td>Erreur serveur</td>
                                        <td>Erreur interne du serveur</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5>Format des erreurs</h5>
                        <pre><code class="language-json">{
  "error": true,
  "message": "Description de l'erreur",
  "code": "ERROR_CODE"
}</code></pre>
                    </div>
                </div>

                <!-- Exemples d'intégration -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-code me-2"></i>Exemples d'intégration</h4>
                    </div>
                    <div class="card-body">
                        <h5>JavaScript (Fetch API)</h5>
                        <pre><code class="language-javascript">// Récupérer les statistiques
async function getStatistics() {
  try {
    const response = await fetch('/purchases/api/statistics.php');
    const data = await response.json();
    
    if (response.ok) {
      console.log('Statistiques:', data);
    } else {
      console.error('Erreur:', data.message);
    }
  } catch (error) {
    console.error('Erreur réseau:', error);
  }
}</code></pre>

                        <h5>PHP (cURL)</h5>
                        <pre><code class="language-php">// Récupérer les activités récentes
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://ged.teranganumerique.com/purchases/api/recent_activities.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $activities = json_decode($response, true);
    foreach ($activities as $activity) {
        echo $activity['title'] . "\n";
    }
}</code></pre>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
