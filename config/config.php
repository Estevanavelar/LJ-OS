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

// Status de instalação
define('SISTEMA_INSTALADO', file_exists(__DIR__ . '/installed.lock'));