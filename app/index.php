<?php
/**
 * LJ-OS - Sistema de Gestão para Oficinas
 * 
 * Ponto de entrada principal da aplicação
 */

// Carregar autoloader
require_once __DIR__ . '/../autoload.php';

// Carregar sistema de localização
$localization = LJOS\Utils\Localization::getInstance();

// Aplicar configurações de tema e idioma
$localization->applySettings();

// Verificar se é uma requisição para a API
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    // Processar APIs
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $apiPath = str_replace('/app', '', $path);
    
    // Redirecionar para o arquivo de API apropriado
    if (strpos($apiPath, '/api/auth') === 0) {
        require_once __DIR__ . '/api/auth.php';
        exit();
    } elseif (strpos($apiPath, '/api/clientes') === 0) {
        require_once __DIR__ . '/api/clientes.php';
        exit();
    } elseif (strpos($apiPath, '/api/status') === 0) {
        // API de status
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'online',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'environment' => 'development'
        ]);
        exit();
    }
}

// Verificar se já está logado
session_start();
$token = $_SESSION['token'] ?? $_COOKIE['token'] ?? null;

if ($token) {
    // Se já tem token, redirecionar para dashboard
    header('Location: dashboard.php');
    exit();
}

// Redirecionar para página de login
header('Location: login.php');
exit();
?>
