<?php
/**
 * Classe PurchaseOrder - Gestion des bons de commande
 */

require_once __DIR__ . '/../config/database.php';

class PurchaseOrder {
    private $db;
    private $table = 'purchase_orders';
    
    public $id;
    public $order_number;
    public $request_id;
    public $supplier_id;
    public $buyer_id;
    public $title;
    public $total_amount;
    public $tax_amount;
    public $total_with_tax;
    public $currency;
    public $order_date;
    public $expected_delivery_date;
    public $delivery_address;
    public $payment_terms;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Générer un numéro de commande unique
     */
    private function generateOrderNumber() {
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE YEAR(created_at) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        
        return 'BC' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Créer un nouveau bon de commande
     */
    public function create() {
        $this->order_number = $this->generateOrderNumber();
        
        $query = "INSERT INTO " . $this->table . " 
                  SET order_number = :order_number,
                      request_id = :request_id,
                      supplier_id = :supplier_id,
                      buyer_id = :buyer_id,
                      title = :title,
                      total_amount = :total_amount,
                      tax_amount = :tax_amount,
                      total_with_tax = :total_with_tax,
                      currency = :currency,
                      order_date = :order_date,
                      expected_delivery_date = :expected_delivery_date,
                      delivery_address = :delivery_address,
                      payment_terms = :payment_terms,
                      status = :status,
                      notes = :notes";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':order_number', $this->order_number);
        $stmt->bindParam(':request_id', $this->request_id);
        $stmt->bindParam(':supplier_id', $this->supplier_id);
        $stmt->bindParam(':buyer_id', $this->buyer_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':tax_amount', $this->tax_amount);
        $stmt->bindParam(':total_with_tax', $this->total_with_tax);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':order_date', $this->order_date);
        $stmt->bindParam(':expected_delivery_date', $this->expected_delivery_date);
        $stmt->bindParam(':delivery_address', $this->delivery_address);
        $stmt->bindParam(':payment_terms', $this->payment_terms);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Lire un bon de commande par ID
     */
    public function read($id) {
        $query = "SELECT po.*, s.name as supplier_name, u.username as buyer_name,
                         pr.request_number
                  FROM " . $this->table . " po
                  LEFT JOIN suppliers s ON po.supplier_id = s.id
                  LEFT JOIN users u ON po.buyer_id = u.id
                  LEFT JOIN purchase_requests pr ON po.request_id = pr.id
                  WHERE po.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->order_number = $row['order_number'];
            $this->request_id = $row['request_id'];
            $this->supplier_id = $row['supplier_id'];
            $this->buyer_id = $row['buyer_id'];
            $this->title = $row['title'];
            $this->total_amount = $row['total_amount'];
            $this->tax_amount = $row['tax_amount'];
            $this->total_with_tax = $row['total_with_tax'];
            $this->currency = $row['currency'];
            $this->order_date = $row['order_date'];
            $this->expected_delivery_date = $row['expected_delivery_date'];
            $this->delivery_address = $row['delivery_address'];
            $this->payment_terms = $row['payment_terms'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }
    
    /**
     * Mettre à jour un bon de commande
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET supplier_id = :supplier_id,
                      title = :title,
                      total_amount = :total_amount,
                      tax_amount = :tax_amount,
                      total_with_tax = :total_with_tax,
                      currency = :currency,
                      order_date = :order_date,
                      expected_delivery_date = :expected_delivery_date,
                      delivery_address = :delivery_address,
                      payment_terms = :payment_terms,
                      status = :status,
                      notes = :notes
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':supplier_id', $this->supplier_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':tax_amount', $this->tax_amount);
        $stmt->bindParam(':total_with_tax', $this->total_with_tax);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':order_date', $this->order_date);
        $stmt->bindParam(':expected_delivery_date', $this->expected_delivery_date);
        $stmt->bindParam(':delivery_address', $this->delivery_address);
        $stmt->bindParam(':payment_terms', $this->payment_terms);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        
        return $stmt->execute();
    }
    
    /**
     * Lister les bons de commande
     */
    public function getAll($status = null, $supplier_id = null) {
        $query = "SELECT po.*, s.name as supplier_name, u.username as buyer_name
                  FROM " . $this->table . " po
                  LEFT JOIN suppliers s ON po.supplier_id = s.id
                  LEFT JOIN users u ON po.buyer_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            $query .= " AND po.status = :status";
            $params[':status'] = $status;
        }
        
        if ($supplier_id) {
            $query .= " AND po.supplier_id = :supplier_id";
            $params[':supplier_id'] = $supplier_id;
        }
        
        $query .= " ORDER BY po.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ajouter un article au bon de commande
     */
    public function addItem($item_name, $description, $quantity, $unit, $unit_price, $tax_rate = 0) {
        $total_price = $quantity * $unit_price;
        
        $query = "INSERT INTO purchase_order_items 
                  SET order_id = :order_id,
                      item_name = :item_name,
                      description = :description,
                      quantity = :quantity,
                      unit = :unit,
                      unit_price = :unit_price,
                      total_price = :total_price,
                      tax_rate = :tax_rate";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $this->id);
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':unit_price', $unit_price);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':tax_rate', $tax_rate);
        
        if ($stmt->execute()) {
            $this->updateTotalAmounts();
            return true;
        }
        return false;
    }
    
    /**
     * Récupérer les articles d'un bon de commande
     */
    public function getItems() {
        $query = "SELECT * FROM purchase_order_items 
                  WHERE order_id = :order_id 
                  ORDER BY id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mettre à jour les montants totaux du bon de commande
     */
    private function updateTotalAmounts() {
        $query = "SELECT 
                    COALESCE(SUM(total_price), 0) as subtotal,
                    COALESCE(SUM(total_price * tax_rate / 100), 0) as tax_total
                  FROM purchase_order_items 
                  WHERE order_id = :order_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $subtotal = $result['subtotal'];
        $tax_total = $result['tax_total'];
        $total_with_tax = $subtotal + $tax_total;
        
        $update_query = "UPDATE " . $this->table . " 
                        SET total_amount = :subtotal,
                            tax_amount = :tax_total,
                            total_with_tax = :total_with_tax
                        WHERE id = :order_id";
        
        $update_stmt = $this->db->prepare($update_query);
        $update_stmt->bindParam(':subtotal', $subtotal);
        $update_stmt->bindParam(':tax_total', $tax_total);
        $update_stmt->bindParam(':total_with_tax', $total_with_tax);
        $update_stmt->bindParam(':order_id', $this->id);
        
        return $update_stmt->execute();
    }
    
    /**
     * Créer un bon de commande à partir d'une demande d'achat
     */
    public function createFromRequest($request_id, $supplier_id, $buyer_id) {
        // Récupérer les informations de la demande d'achat
        $request_query = "SELECT * FROM purchase_requests WHERE id = :request_id";
        $request_stmt = $this->db->prepare($request_query);
        $request_stmt->bindParam(':request_id', $request_id);
        $request_stmt->execute();
        $request = $request_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            return false;
        }
        
        // Créer le bon de commande
        $this->request_id = $request_id;
        $this->supplier_id = $supplier_id;
        $this->buyer_id = $buyer_id;
        $this->title = $request['title'];
        $this->total_amount = 0;
        $this->tax_amount = 0;
        $this->total_with_tax = 0;
        $this->currency = $request['currency'];
        $this->order_date = date('Y-m-d');
        $this->status = 'draft';
        
        if ($this->create()) {
            // Copier les articles de la demande d'achat
            $items_query = "SELECT * FROM purchase_request_items WHERE request_id = :request_id";
            $items_stmt = $this->db->prepare($items_query);
            $items_stmt->bindParam(':request_id', $request_id);
            $items_stmt->execute();
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items as $item) {
                $this->addItem(
                    $item['item_name'],
                    $item['description'],
                    $item['quantity'],
                    $item['unit'],
                    $item['unit_price']
                );
            }
            
            return true;
        }
        
        return false;
    }
}
?>
