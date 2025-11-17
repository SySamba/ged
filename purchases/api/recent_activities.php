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
    
    $activities = [];
    
    // Récupérer les activités récentes des différents modules
    $queries = [
        // Demandes d'achat récentes
        "SELECT 'Demande d''achat' as type, 
                CONCAT('Demande ', request_number, ' - ', title) as title,
                CONCAT('Créée par ', u.username) as description,
                u.username as user,
                pr.created_at as date
         FROM purchase_requests pr
         JOIN users u ON pr.requester_id = u.id
         ORDER BY pr.created_at DESC
         LIMIT 5",
        
        // Bons de commande récents
        "SELECT 'Bon de commande' as type,
                CONCAT('Commande ', order_number, ' - ', title) as title,
                CONCAT('Créée par ', u.username, ' - Fournisseur: ', s.name) as description,
                u.username as user,
                po.created_at as date
         FROM purchase_orders po
         JOIN users u ON po.buyer_id = u.id
         JOIN suppliers s ON po.supplier_id = s.id
         ORDER BY po.created_at DESC
         LIMIT 5"
    ];
    
    foreach ($queries as $query) {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $activities = array_merge($activities, $results);
    }
    
    // Trier par date décroissante
    usort($activities, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Limiter à 10 activités et formater les dates
    $activities = array_slice($activities, 0, 10);
    foreach ($activities as &$activity) {
        $activity['date'] = date('d/m/Y H:i', strtotime($activity['date']));
    }
    
    echo json_encode($activities);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
