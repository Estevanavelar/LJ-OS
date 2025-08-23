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
        echo '<!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>LJ-OS - Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #333; text-align: center; }
                .info { background: #e8f4fd; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .status { color: #28a745; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚀 LJ-OS Sistema</h1>
                <div class="info">
                    <p><strong>Status:</strong> <span class="status">✅ Funcionando!</span></p>
                    <p><strong>Versão:</strong> ' . $config['app']['version'] . '</p>
                    <p><strong>Ambiente:</strong> ' . $config['app']['environment'] . '</p>
                    <p><strong>PHP:</strong> ' . PHP_VERSION . '</p>
                    <p><strong>Servidor:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI') . '</p>
                    <p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>
                </div>
                <p>🎉 Sistema configurado com sucesso! O projeto LJ-OS está rodando.</p>
            </div>
        </body>
        </html>';
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
