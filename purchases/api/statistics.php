<?php
header('Content-Type: application/json');
session_start();

require_once '../../config/database.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $statistics = [];
    
    // Demandes d'achat en attente
    $query = "SELECT COUNT(*) as count FROM purchase_requests WHERE status IN ('submitted', 'draft')";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statistics['pending_requests'] = $result['count'];
    
    // Bons de commande actifs
    $query = "SELECT COUNT(*) as count FROM purchase_orders WHERE status IN ('sent', 'confirmed', 'partially_received')";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statistics['active_orders'] = $result['count'];
    
    // Factures à traiter
    $query = "SELECT COUNT(*) as count FROM purchase_invoices WHERE status IN ('received', 'validated')";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statistics['pending_invoices'] = $result['count'];
    
    // Budget mensuel (total des commandes du mois en cours)
    $query = "SELECT COALESCE(SUM(total_with_tax), 0) as total 
              FROM purchase_orders 
              WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) 
              AND YEAR(order_date) = YEAR(CURRENT_DATE())
              AND status != 'cancelled'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statistics['monthly_budget'] = number_format($result['total'], 2, ',', ' ');
    
    echo json_encode($statistics);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
