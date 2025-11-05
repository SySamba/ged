<?php
/**
 * Configuration générale de DigiDocs
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration générale
define('APP_NAME', 'DigiDocs');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://ged.teranganumerique.com');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('TEMP_PATH', ROOT_PATH . '/temp');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Sécurité
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_LIFETIME', 3600); // 1 heure
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Upload de fichiers
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'
]);

// Email
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@digidocs.sn');
define('FROM_NAME', 'DigiDocs System');

// Timezone
date_default_timezone_set('Africa/Dakar');

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/classes/',
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/',
        ROOT_PATH . '/config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Fonctions utilitaires
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

function hasPermission($permission, $action = 'read') {
    if (!isLoggedIn()) {
        return false;
    }
    
    if ($_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    $permissions = json_decode($_SESSION['user_permissions'] ?? '{}', true);
    return isset($permissions[$permission][$action]) && $permissions[$permission][$action] === true;
}

function logActivity($action, $table = null, $element_id = null, $details = null) {
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO logs_activite (utilisateur_id, action, table_concernee, id_element, details, adresse_ip, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $table,
            $element_id,
            $details ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Erreur lors de l'enregistrement du log : " . $e->getMessage());
    }
}

// Créer les dossiers nécessaires s'ils n'existent pas
$directories = [UPLOAD_PATH, TEMP_PATH, LOGS_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Créer les sous-dossiers d'upload par catégorie
$uploadCategories = ['documents', 'cv', 'generated', 'temp'];
foreach ($uploadCategories as $category) {
    $categoryPath = UPLOAD_PATH . '/' . $category;
    if (!is_dir($categoryPath)) {
        mkdir($categoryPath, 0755, true);
    }
}
