<?php
/**
 * Fichier de configuration exemple - DigiDocs
 * 
 * INSTRUCTIONS :
 * 1. Copiez ce fichier vers config/config.php
 * 2. Modifiez les valeurs selon votre environnement
 * 3. Ne versionnez JAMAIS le fichier config/config.php
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'digidocs');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_CHARSET', 'utf8mb4');

// Configuration de l'application
define('APP_NAME', 'DigiDocs');
define('APP_URL', 'http://localhost/document');
define('APP_VERSION', '1.0.0');

// Chemins
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// Sécurité
define('SECRET_KEY', 'changez_cette_cle_secrete_unique');
define('HASH_ALGO', 'sha256');

// Upload
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif']);

// Email (si utilisé)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre_email@example.com');
define('SMTP_PASS', 'votre_mot_de_passe_email');

// Environnement
define('ENVIRONMENT', 'development'); // development, production
define('DEBUG_MODE', true);

// Session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Africa/Dakar');

// Autoloader simple
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Fonctions utilitaires
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

function requireLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function hasPermission($module, $action) {
    // Implémentez votre logique de permissions ici
    return true;
}

function logActivity($action, $table, $record_id, $details = []) {
    // Implémentez votre système de logs ici
}
?>
