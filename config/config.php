<?php
/**
 * Configurações Gerais do Sistema
 * LJ-OS Sistema para Lava Jato
 */

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Configurações de segurança
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

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

// Status de instalação
define('SISTEMA_INSTALADO', file_exists(__DIR__ . '/installed.lock'));