<?php
/**
 * Logout da Área do Cliente
 * LJ-OS Sistema para Lava Jato
 */

// Incluir configurações
require_once 'config.php';

session_start();

// Registrar log de logout se estiver logado
if (isset($_SESSION['cliente_id'])) {
    registrarLogAcesso($_SESSION['cliente_id'], 'logout');
}

// Destruir sessão do cliente
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nome']);
unset($_SESSION['cliente_tipo']);
unset($_SESSION['cliente_acesso']);

// Redirecionar para página de login
header('Location: index.php');
exit();
?> 