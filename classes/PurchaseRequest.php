<?php
/**
 * Classe PurchaseRequest - Gestion des demandes d'achat
 */

require_once __DIR__ . '/../config/database.php';

class PurchaseRequest {
    private $db;
    private $table = 'purchase_requests';
    
    public $id;
    public $request_number;
    public $requester_id;
    public $department;
    public $title;
    public $description;
    public $justification;
    public $priority;
    public $total_amount;
    public $currency;
    public $requested_date;
    public $status;
    public $approver_id;
    public $approved_at;
    public $approval_comments;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Générer un numéro de demande unique
     */
    private function generateRequestNumber() {
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE YEAR(created_at) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        
        return 'DA' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Créer une nouvelle demande d'achat
     */
    public function create() {
        $this->request_number = $this->generateRequestNumber();
        
        $query = "INSERT INTO " . $this->table . " 
                  SET request_number = :request_number,
                      requester_id = :requester_id,
                      department = :department,
                      title = :title,
                      description = :description,
                      justification = :justification,
                      priority = :priority,
                      total_amount = :total_amount,
                      currency = :currency,
                      requested_date = :requested_date,
                      status = :status";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':request_number', $this->request_number);
        $stmt->bindParam(':requester_id', $this->requester_id);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':justification', $this->justification);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':requested_date', $this->requested_date);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Lire une demande d'achat par ID
     */
    public function read($id) {
        $query = "SELECT pr.*, u.username as requester_name, a.username as approver_name
                  FROM " . $this->table . " pr
                  LEFT JOIN users u ON pr.requester_id = u.id
                  LEFT JOIN users a ON pr.approver_id = a.id
                  WHERE pr.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->request_number = $row['request_number'];
            $this->requester_id = $row['requester_id'];
            $this->department = $row['department'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->justification = $row['justification'];
            $this->priority = $row['priority'];
            $this->total_amount = $row['total_amount'];
            $this->currency = $row['currency'];
            $this->requested_date = $row['requested_date'];
            $this->status = $row['status'];
            $this->approver_id = $row['approver_id'];
            $this->approved_at = $row['approved_at'];
            $this->approval_comments = $row['approval_comments'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }
    
    /**
     * Mettre à jour une demande d'achat
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET department = :department,
                      title = :title,
                      description = :description,
                      justification = :justification,
                      priority = :priority,
                      total_amount = :total_amount,
                      currency = :currency,
                      requested_date = :requested_date,
                      status = :status
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':justification', $this->justification);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':requested_date', $this->requested_date);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }
    
    /**
     * Approuver une demande d'achat
     */
    public function approve($approver_id, $comments = '') {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'approved',
                      approver_id = :approver_id,
                      approved_at = NOW(),
                      approval_comments = :comments
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':approver_id', $approver_id);
        $stmt->bindParam(':comments', $comments);
        
        return $stmt->execute();
    }
    
    /**
     * Rejeter une demande d'achat
     */
    public function reject($approver_id, $comments) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'rejected',
                      approver_id = :approver_id,
                      approved_at = NOW(),
                      approval_comments = :comments
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':approver_id', $approver_id);
        $stmt->bindParam(':comments', $comments);
        
        return $stmt->execute();
    }
    
    /**
     * Lister les demandes d'achat
     */
    public function getAll($status = null, $requester_id = null) {
        $query = "SELECT pr.*, u.username as requester_name, a.username as approver_name
                  FROM " . $this->table . " pr
                  LEFT JOIN users u ON pr.requester_id = u.id
                  LEFT JOIN users a ON pr.approver_id = a.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            $query .= " AND pr.status = :status";
            $params[':status'] = $status;
        }
        
        if ($requester_id) {
            $query .= " AND pr.requester_id = :requester_id";
            $params[':requester_id'] = $requester_id;
        }
        
        $query .= " ORDER BY pr.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ajouter un article à la demande d'achat
     */
    public function addItem($category_id, $item_name, $description, $quantity, $unit, $unit_price) {
        $total_price = $quantity * $unit_price;
        
        $query = "INSERT INTO purchase_request_items 
                  SET request_id = :request_id,
                      category_id = :category_id,
                      item_name = :item_name,
                      description = :description,
                      quantity = :quantity,
                      unit = :unit,
                      unit_price = :unit_price,
                      total_price = :total_price";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':request_id', $this->id);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':unit_price', $unit_price);
        $stmt->bindParam(':total_price', $total_price);
        
        if ($stmt->execute()) {
            $this->updateTotalAmount();
            return true;
        }
        return false;
    }
    
    /**
     * Récupérer les articles d'une demande d'achat
     */
    public function getItems() {
        $query = "SELECT pri.*, pc.name as category_name
                  FROM purchase_request_items pri
                  LEFT JOIN purchase_categories pc ON pri.category_id = pc.id
                  WHERE pri.request_id = :request_id
                  ORDER BY pri.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':request_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mettre à jour le montant total de la demande
     */
    private function updateTotalAmount() {
        $query = "UPDATE " . $this->table . " 
                  SET total_amount = (
                      SELECT COALESCE(SUM(total_price), 0) 
                      FROM purchase_request_items 
                      WHERE request_id = :request_id
                  )
                  WHERE id = :request_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':request_id', $this->id);
        return $stmt->execute();
    }
}
?>
