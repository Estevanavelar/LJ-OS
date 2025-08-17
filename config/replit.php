
<?php
/**
 * Configurações específicas para ambiente Replit
 */

// URL base do sistema no Replit
if (isset($_SERVER['HTTP_HOST'])) {
    define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    define('BASE_URL', 'https://localhost:5000');
}

// Configurações de servidor para Replit
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de sessão
ini_set('session.cookie_secure', '0'); // HTTP no ambiente de dev
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600);

// Configurações de upload
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Headers de segurança básicos para Replit
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Criar diretórios necessários se não existirem
$dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/clientes', 
    __DIR__ . '/../uploads/veiculos',
    __DIR__ . '/../uploads/os',
    __DIR__ . '/../logs'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configurar logs de erro
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configurações específicas do Replit
if (getenv('REPL_ID')) {
    // Detectar ambiente Replit
    define('IS_REPLIT', true);
    define('REPL_ID', getenv('REPL_ID'));
    define('REPL_SLUG', getenv('REPL_SLUG') ?: 'lj-os-system');
} else {
    define('IS_REPLIT', false);
}
