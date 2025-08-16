
<?php
/**
 * Configurações específicas para ambiente Replit
 */

// URL base do sistema no Replit
define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST']);

// Configurações de sessão para Replit
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Configurações de upload
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('memory_limit', '256M');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro para desenvolvimento
if (getenv('REPL_ID')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

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
?>
