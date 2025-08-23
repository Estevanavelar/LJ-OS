<?php
/**
 * LJ-OS - Sistema de Gestão para Oficinas
 * 
 * Arquivo principal na raiz do projeto
 * Verifica se o sistema está instalado e redireciona adequadamente
 */

// Verificar se é uma requisição para a API
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    // Redirecionar para app/index.php para processar APIs
    header('Location: /app/index.php' . $_SERVER['REQUEST_URI']);
    exit();
}

// Verificar se o sistema está instalado
function isSystemInstalled(): bool
{
    // Verificar se existe o arquivo .installed
    if (file_exists(__DIR__ . '/.installed')) {
        return true;
    }
    
    // Verificar se existe o banco de dados e as tabelas principais
    try {
        if (file_exists(__DIR__ . '/autoload.php')) {
            require_once __DIR__ . '/autoload.php';
            
            $db = LJOS\Database\Database::getInstance();
            $connection = $db->getConnection();
            
            // Verificar se a tabela usuarios existe e tem dados
            $stmt = $connection->query("SELECT COUNT(*) as total FROM usuarios");
            if ($stmt && $stmt->fetch()['total'] > 0) {
                return true;
            }
        }
    } catch (Exception $e) {
        // Se houver erro, considerar como não instalado
        return false;
    }
    
    return false;
}

// Verificar instalação
if (!isSystemInstalled()) {
    // Sistema não instalado - redirecionar para instalador
    header('Location: /install_web.php');
    exit();
}

// Sistema instalado - verificar se já está logado
session_start();
$token = $_SESSION['token'] ?? $_COOKIE['token'] ?? null;

if ($token) {
    // Se já tem token, redirecionar para dashboard
    header('Location: /app/dashboard.php');
    exit();
}

// Redirecionar para página de login
header('Location: /app/login.php');
exit();
?>
