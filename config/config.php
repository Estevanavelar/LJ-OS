<?php
/**
 * Configuração Principal do Sistema LJ-OS
 * Sistema completo para gestão de lava jato
 */

// Iniciar buffer de saída para evitar problemas com headers
ob_start();

// Configurações de sessão PRIMEIRO (antes de qualquer saída)
if (session_status() === PHP_SESSION_NONE) {
    // Configurações básicas para Replit
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de segurança
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configurações do sistema
define('SISTEMA_NOME', 'LJ-OS Sistema para Lava Jato');
define('SISTEMA_VERSAO', '1.0.0');
define('SISTEMA_AMBIENTE', 'desenvolvimento');

// Diretórios
define('DIR_ROOT', dirname(__DIR__));
define('DIR_UPLOADS', DIR_ROOT . '/uploads');
define('DIR_LOGS', DIR_ROOT . '/logs');
define('DIR_TEMP', DIR_ROOT . '/temp');

// URLs
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('BASE_URL', $protocol . $_SERVER['HTTP_HOST']);
} else {
    define('BASE_URL', 'http://localhost:5000');
}

// Configurações de upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

<<<<<<< HEAD
// Configurações de email (para futuras implementações)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');

// APIs externas
define('WHATSAPP_API_TOKEN', getenv('WHATSAPP_API_TOKEN') ?: '');
define('SMS_API_TOKEN', getenv('SMS_API_TOKEN') ?: '');

// Configurações de backup
define('BACKUP_ENABLED', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly

// Configurações específicas para diferentes servidores
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false) {
    // Configurações para Apache
    define('SERVER_TYPE', 'apache');
} elseif (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false) {
    // Configurações para Nginx
    define('SERVER_TYPE', 'nginx');
} else {
    // Servidor PHP built-in ou outros
    define('SERVER_TYPE', 'php-builtin');
}

// Configurações de erro baseadas no ambiente
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

// Configurações de sessão segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

// Headers de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    
    if (!DEBUG) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Criar diretórios necessários (será feito após inicialização da sessão)
$required_dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/clientes',
    __DIR__ . '/../uploads/veiculos', 
    __DIR__ . '/../uploads/funcionarios',
    __DIR__ . '/../uploads/documentos',
    __DIR__ . '/../logs',
    __DIR__ . '/../backup',
    __DIR__ . '/../temp'
];

// Logs de erro
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . 'php_errors.log');

// Autoloader simples para classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/../src/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Incluir arquivos essenciais
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
=======
// Status de instalação
define('SISTEMA_INSTALADO', file_exists(__DIR__ . '/installed.lock'));
>>>>>>> 3f92fb9d821d8bf3d28c65e373833e3c475c5bc8
