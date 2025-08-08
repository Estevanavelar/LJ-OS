<?php
/**
 * Página inicial do sistema
 * LJ-OS Sistema para Lava Jato
 * Redireciona para o dashboard ou login
 */

// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Se estiver logado, redirecionar para o dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Se não estiver logado, redirecionar para o login
    header('Location: login.php');
    exit;
}
?> 