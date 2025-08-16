<?php
/**
 * Página inicial do sistema
 * LJ-OS Sistema para Lava Jato
 * Redireciona para o dashboard ou login
 */

require_once 'config/replit.php';
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';


// Verificar se o usuário está logado
if (estaLogado()) {
    // Se estiver logado, redirecionar para o dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Se não estiver logado, redirecionar para o login
    header('Location: login.php');
    exit;
}
?>