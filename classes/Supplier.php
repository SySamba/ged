<?php
/**
 * Classe Supplier - Gestion des fournisseurs
 */

require_once __DIR__ . '/../config/database.php';

class Supplier {
    private $db;
    private $table = 'suppliers';
    
    public $id;
    public $name;
    public $contact_person;
    public $email;
    public $phone;
    public $address;
    public $city;
    public $postal_code;
    public $country;
    public $tax_number;
    public $payment_terms;
    public $status;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Créer un nouveau fournisseur
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET name = :name, 
                      contact_person = :contact_person,
                      email = :email,
                      phone = :phone,
                      address = :address,
                      city = :city,
                      postal_code = :postal_code,
                      country = :country,
                      tax_number = :tax_number,
                      payment_terms = :payment_terms,
                      status = :status";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':tax_number', $this->tax_number);
        $stmt->bindParam(':payment_terms', $this->payment_terms);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Lire un fournisseur par ID
     */
    public function read($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->contact_person = $row['contact_person'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->postal_code = $row['postal_code'];
            $this->country = $row['country'];
            $this->tax_number = $row['tax_number'];
            $this->payment_terms = $row['payment_terms'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    /**
     * Mettre à jour un fournisseur
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name,
                      contact_person = :contact_person,
                      email = :email,
                      phone = :phone,
                      address = :address,
                      city = :city,
                      postal_code = :postal_code,
                      country = :country,
                      tax_number = :tax_number,
                      payment_terms = :payment_terms,
                      status = :status
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':tax_number', $this->tax_number);
        $stmt->bindParam(':payment_terms', $this->payment_terms);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }
    
    /**
     * Supprimer un fournisseur
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    /**
     * Lister tous les fournisseurs actifs
     */
    public function getAll($status = 'active') {
        $query = "SELECT * FROM " . $this->table;
        if ($status) {
            $query .= " WHERE status = :status";
        }
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Rechercher des fournisseurs
     */
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (name LIKE :keyword 
                         OR contact_person LIKE :keyword 
                         OR email LIKE :keyword)
                        AND status = 'active'
                  ORDER BY name ASC";
        
        $stmt = $this->db->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
