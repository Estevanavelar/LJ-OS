<?php
/**
 * Página inicial do sistema LJ-OS
 */

// Verificar se o sistema está instalado
if (!file_exists('config/installed.lock')) {
    header('Location: install.php');
    exit;
}

// Inicializar configuração
require_once 'config/config.php';

// Verificar se está logado
if (verificar_login()) {
    header('Location: dashboard.php');
    exit;
}

// Redirecionar para login
header('Location: login.php');
exit;