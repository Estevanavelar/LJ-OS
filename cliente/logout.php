<?php
/**
 * Logout da Área do Cliente
 * LJ-OS Sistema para Lava Jato
 */

require_once __DIR__ . '/../includes/functions.php';
require_once 'config.php';

// Registrar log de logout se estiver logado
if (isset($_SESSION['cliente_id'])) {
    if (function_exists('registrarLogAcesso')) {
        registrarLogAcesso($_SESSION['cliente_id'], 'logout');
    }
}

// Destruir dados da sessão do cliente
unset($_SESSION['cliente_id'], $_SESSION['cliente_nome'], $_SESSION['cliente_tipo'], $_SESSION['cliente_acesso']);

// Redirecionar para página de login
header('Location: index.php');
exit(); 