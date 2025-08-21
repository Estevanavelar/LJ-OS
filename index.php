<?php
/**
 * LJ-OS Sistema para Lava Jato
 * Página Principal
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar se o sistema está instalado
if (!file_exists('config/installed.lock')) {
    // Sistema não instalado - mostrar página de boas-vindas
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LJ-OS - Sistema para Lava Jato</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 500px;
            }
            h1 { color: #333; margin-bottom: 1rem; }
            p { color: #666; margin-bottom: 1.5rem; }
            .btn {
                background: #667eea;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 0.5rem;
                cursor: pointer;
            }
            .btn:hover { background: #5a6fd8; }
            .status {
                background: #f8f9fa;
                padding: 1rem;
                border-radius: 5px;
                margin: 1rem 0;
                border-left: 4px solid #28a745;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚗 LJ-OS Sistema para Lava Jato</h1>
            <p>Sistema de gestão completo para lava jatos</p>

            <div class="status">
                <strong>✅ Sistema Pronto</strong><br>
                O ambiente está preparado para instalação
            </div>

            <a href="setup_database.php" class="btn">
                🚀 Configurar Sistema
            </a>

            <a href="install.php" class="btn">
                ⚙️ Instalação Avançada
            </a>

            <p><small>Versão 1.0.0 - Ambiente Replit</small></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Sistema instalado - verificar login
session_start();
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Redirecionar para login
header('Location: login.php');
exit;