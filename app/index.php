<?php

/**
 * LJ-OS - Sistema Principal
 * Ponto de entrada da aplicação
 */

// Carregar configurações
$config = require_once __DIR__ . '/../config/config.php';

// Definir timezone
date_default_timezone_set($config['app']['timezone']);

// Habilitar exibição de erros em desenvolvimento
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Iniciar sessão
session_start();

// Carregar autoloader do Composer (quando disponível)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Função para carregar classes manualmente (fallback)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Verificar se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Roteamento básico
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remover query string da URI
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Roteamento simples
switch ($requestUri) {
    case '/':
        // Redirecionar para login
        header('Location: /login.php');
        exit();
        break;
        
    case '/api/status':
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'LJ-OS API funcionando',
            'timestamp' => date('c'),
            'version' => $config['app']['version'],
            'php_version' => PHP_VERSION
        ]);
        break;
        
    default:
        http_response_code(404);
        echo '<h1>404 - Página não encontrada</h1>';
        echo '<p>A página solicitada não existe.</p>';
        echo '<p><a href="/">Voltar ao início</a></p>';
        break;
}
