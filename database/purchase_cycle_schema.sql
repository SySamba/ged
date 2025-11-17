-- Schéma de base de données pour le cycle d'achat
-- Création des tables pour la gestion complète du cycle d'achat

-- Table des fournisseurs
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    tax_number VARCHAR(50),
    payment_terms INT DEFAULT 30, -- Délai de paiement en jours
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des catégories de produits/services
CREATE TABLE IF NOT EXISTS purchase_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    budget_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des demandes d'achat
CREATE TABLE IF NOT EXISTS purchase_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    requester_id INT NOT NULL,
    department VARCHAR(100),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    justification TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'EUR',
    requested_date DATE,
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'cancelled') DEFAULT 'draft',
    approver_id INT,
    approved_at TIMESTAMP NULL,
    approval_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id),
    FOREIGN KEY (approver_id) REFERENCES users(id)
);

-- Table des articles de demande d'achat
CREATE TABLE IF NOT EXISTS purchase_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    category_id INT,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    unit_price DECIMAL(15,2),
    total_price DECIMAL(15,2),
    specifications TEXT,
    FOREIGN KEY (request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES purchase_categories(id)
);

-- Table des bons de commande
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    request_id INT,
    supplier_id INT NOT NULL,
    buyer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_with_tax DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    delivery_address TEXT,
    payment_terms INT DEFAULT 30,
    status ENUM('draft', 'sent', 'confirmed', 'partially_received', 'completed', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES purchase_requests(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
);

-- Table des articles de bon de commande
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50),
    unit_price DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    FOREIGN KEY (order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
);

-- Table des réceptions
CREATE TABLE IF NOT EXISTS purchase_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    receiver_id INT NOT NULL,
    receipt_date DATE NOT NULL,
    delivery_note_number VARCHAR(100),
    status ENUM('partial', 'complete') DEFAULT 'complete',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES purchase_orders(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Table des articles reçus
CREATE TABLE IF NOT EXISTS purchase_receipt_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    order_item_id INT NOT NULL,
    quantity_received DECIMAL(10,2) NOT NULL,
    quality_status ENUM('good', 'damaged', 'defective') DEFAULT 'good',
    notes TEXT,
    FOREIGN KEY (receipt_id) REFERENCES purchase_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES purchase_order_items(id)
);

-- Table des factures
CREATE TABLE IF NOT EXISTS purchase_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_invoice_number VARCHAR(100),
    order_id INT,
    supplier_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    status ENUM('received', 'validated', 'approved', 'paid', 'disputed') DEFAULT 'received',
    payment_date DATE NULL,
    payment_method VARCHAR(100),
    payment_reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES purchase_orders(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- Table des articles de facture
CREATE TABLE IF NOT EXISTS purchase_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    FOREIGN KEY (invoice_id) REFERENCES purchase_invoices(id) ON DELETE CASCADE
);

-- Table des écritures comptables
CREATE TABLE IF NOT EXISTS accounting_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_id INT,
    entry_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'posted', 'reversed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES purchase_invoices(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des lignes d'écriture comptable
CREATE TABLE IF NOT EXISTS accounting_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0.00,
    credit_amount DECIMAL(15,2) DEFAULT 0.00,
    description VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES accounting_entries(id) ON DELETE CASCADE
);

-- Table des workflows d'approbation
CREATE TABLE IF NOT EXISTS approval_workflows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('purchase_request', 'purchase_order', 'invoice') NOT NULL,
    document_id INT NOT NULL,
    step_number INT NOT NULL,
    approver_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approver_id) REFERENCES users(id)
);

-- Table des pièces jointes
CREATE TABLE IF NOT EXISTS purchase_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('purchase_request', 'purchase_order', 'receipt', 'invoice') NOT NULL,
    document_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Index pour améliorer les performances (avec vérification d'existence)
CREATE INDEX IF NOT EXISTS idx_purchase_requests_status ON purchase_requests(status);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_status ON purchase_orders(status);
CREATE INDEX IF NOT EXISTS idx_purchase_invoices_status ON purchase_invoices(status);
CREATE INDEX IF NOT EXISTS idx_purchase_requests_requester ON purchase_requests(requester_id);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_supplier ON purchase_orders(supplier_id);
CREATE INDEX IF NOT EXISTS idx_approval_workflows_document ON approval_workflows(document_type, document_id);

-- Insertion des catégories par défaut (avec vérification d'existence)
INSERT IGNORE INTO purchase_categories (name, description, budget_code) VALUES
('Fournitures de bureau', 'Papeterie, consommables bureau', 'FB001'),
('Matériel informatique', 'Ordinateurs, périphériques, logiciels', 'IT001'),
('Mobilier', 'Bureaux, chaises, mobilier de bureau', 'MOB001'),
('Services', 'Prestations de service, consulting', 'SRV001'),
('Maintenance', 'Maintenance équipements, réparations', 'MNT001'),
('Formation', 'Formations professionnelles', 'FOR001'),
('Déplacements', 'Frais de mission, transport', 'DEP001'),
('Télécommunications', 'Téléphonie, internet, communications', 'TEL001');
