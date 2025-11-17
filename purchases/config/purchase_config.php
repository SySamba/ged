<?php
/**
 * Configuration spécifique au module d'achat
 */

// Inclure la configuration principale
require_once __DIR__ . '/../../config/config.php';

// Vérifier l'authentification
requireLogin();

// Inclure les classes du module d'achat
$purchase_classes = [
    'Supplier',
    'PurchaseRequest', 
    'PurchaseOrder',
    'PurchaseInvoice',
    'PurchaseReceipt'
];

foreach ($purchase_classes as $class) {
    $class_file = __DIR__ . '/../../classes/' . $class . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
}

// Fonctions utilitaires pour le module d'achat
function getPurchaseDatabase() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database;
}

function getCurrentUser() {
    static $user = null;
    if ($user === null && isset($_SESSION['user_id'])) {
        $user = new User();
        $user->read($_SESSION['user_id']);
    }
    return $user;
}

function formatCurrency($amount, $currency = 'EUR') {
    return number_format($amount, 2, ',', ' ') . ' ' . $currency;
}

function getPurchaseStatusLabel($status, $type = 'request') {
    $labels = [
        'request' => [
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            'cancelled' => 'Annulée'
        ],
        'order' => [
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'confirmed' => 'Confirmée',
            'partially_received' => 'Partiellement reçue',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée'
        ],
        'invoice' => [
            'received' => 'Reçue',
            'validated' => 'Validée',
            'approved' => 'Approuvée',
            'paid' => 'Payée',
            'disputed' => 'Contestée'
        ]
    ];
    
    return $labels[$type][$status] ?? $status;
}

function getPurchaseStatusColor($status, $type = 'request') {
    $colors = [
        'request' => [
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'dark'
        ],
        'order' => [
            'draft' => 'secondary',
            'sent' => 'warning',
            'confirmed' => 'success',
            'partially_received' => 'info',
            'completed' => 'primary',
            'cancelled' => 'danger'
        ],
        'invoice' => [
            'received' => 'info',
            'validated' => 'warning',
            'approved' => 'success',
            'paid' => 'primary',
            'disputed' => 'danger'
        ]
    ];
    
    return $colors[$type][$status] ?? 'secondary';
}
?>
