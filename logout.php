
<?php
/**
 * Logout do Sistema
 * LJ-OS Sistema para Lava Jato
 */

// Iniciar sessão
session_start();

// Destruir todas as variáveis de sessão
session_unset();

// Destruir a sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
