<?php
/**
 * Header do sistema
 * LJ-OS Sistema para Lava Jato
 */

require_once __DIR__ . '/functions.php';

// Verificar se o usuário está logado
verificarLogin();

// Obter informações do usuário
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_nivel = $_SESSION['nivel_acesso'] ?? 'funcionario';
$nome_empresa = obterConfiguracao('nome_empresa', 'Lava Jato VeltaCar');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_empresa; ?> - Sistema de Gestão</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <script>
    // Injeta token CSRF em todos os formulários POST que não possuam o campo
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var csrf = <?php echo json_encode(csrf_token()); ?>;
            var forms = document.querySelectorAll('form[method="post" i]');
            forms.forEach(function(f) {
                if (!f.querySelector('input[name="_csrf"]')) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_csrf';
                    input.value = csrf;
                    f.appendChild(input);
                }
            });
        });
    })();
    
    // Monkeypatch global de fetch para anexar X-CSRF-Token automaticamente
    (function() {
        var CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;
        var originalFetch = window.fetch;
        window.fetch = function(input, init) {
            init = init || {};
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                init.headers.set('X-CSRF-Token', CSRF_TOKEN);
            } else if (typeof init.headers === 'object') {
                init.headers['X-CSRF-Token'] = CSRF_TOKEN;
            } else {
                init.headers = { 'X-CSRF-Token': CSRF_TOKEN };
            }
            return originalFetch(input, init);
        };
    })();
    </script>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-car-wash"></i>
                    </div>
                    <span><?php echo $nome_empresa; ?></span>
                </a>
                
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo $usuario_nome; ?></div>
                        <div class="user-role"><?php echo ucfirst($usuario_nivel); ?></div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Botão flutuante para expandir sidebar -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebarDesktop()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Container principal -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <nav>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <li>
                            <a href="clientes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                Clientes
                            </a>
                        </li>
                        
                        <li>
                            <a href="veiculos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'veiculos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-car"></i>
                                Veículos
                            </a>
                        </li>
                        
                        <li>
                            <a href="agendamentos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'agendamentos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt"></i>
                                Agendamentos
                            </a>
                        </li>
                        
                        <li>
                            <a href="ordens_servico.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ordens_servico.php' ? 'active' : ''; ?>">
                                <i class="fas fa-clipboard-list"></i>
                                Ordens de Serviço
                            </a>
                        </li>
                        
                        <li>
                            <a href="servicos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'servicos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tools"></i>
                                Serviços
                            </a>
                        </li>
                        
                        <li>
                            <a href="estoque.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'estoque.php' ? 'active' : ''; ?>">
                                <i class="fas fa-boxes"></i>
                                Estoque
                            </a>
                        </li>
                        
                        <li>
                            <a href="financeiro.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'financeiro.php' ? 'active' : ''; ?>">
                                <i class="fas fa-dollar-sign"></i>
                                Financeiro
                            </a>
                        </li>
                        
                        <li>
                            <a href="orcamentos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orcamentos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-file-invoice-dollar"></i>
                                Orçamentos
                            </a>
                        </li>
                        
                        <li>
                            <a href="cupons.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cupons.php' ? 'active' : ''; ?>">
                                <i class="fas fa-ticket-alt"></i>
                                Cupons
                            </a>
                        </li>
                        
                        <li>
                            <a href="relatorios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-bar"></i>
                                Relatórios
                            </a>
                        </li>
                        
                        <?php if (in_array($_SESSION['nivel_acesso'], ['admin', 'gerente'])): ?>
                        <li>
                            <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">
                                <i class="fas fa-user-cog"></i>
                                Usuários
                            </a>
                        </li>
                        
                        <li>
                            <a href="funcionarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'funcionarios.php' ? 'active' : ''; ?>">
                                <i class="fas fa-users-cog"></i>
                                Funcionários
                            </a>
                        </li>
                        
                        <li>
                            <a href="configuracoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                                Configurações
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </aside>

            </aside>

            <!-- Conteúdo principal -->
            <main class="col-md-12 main-content">
                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success fade-in">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_GET['sucesso']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['erro']); ?>
                    </div>
                <?php endif; ?>

<script>
// Função para mobile (hambúrguer)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
    
    // Adicionar/remover overlay
    let overlay = document.getElementById('sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        `;
        overlay.onclick = function() {
            toggleSidebar();
        };
        document.body.appendChild(overlay);
    }
    
    if (sidebar.classList.contains('show')) {
        overlay.style.display = 'block';
    } else {
        overlay.style.display = 'none';
    }
}

// Função para desktop (botão flutuante)
function toggleSidebarDesktop() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    sidebar.classList.toggle('expanded');
    
    if (sidebar.classList.contains('expanded')) {
        toggleBtn.innerHTML = '<i class="fas fa-times"></i>';
        toggleBtn.style.left = '270px';
    } else {
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.style.left = '5px';
    }
}

// Detectar tamanho da tela e ajustar comportamento
function checkScreenSize() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (window.innerWidth > 768) {
        // Desktop: usar comportamento expandido
        sidebar.classList.remove('show');
        toggleBtn.classList.remove('hidden');
    } else {
        // Mobile: usar comportamento slide
        sidebar.classList.remove('expanded');
        toggleBtn.classList.add('hidden');
    }
}

// Executar na carga da página
document.addEventListener('DOMContentLoaded', checkScreenSize);

// Executar quando a janela for redimensionada
window.addEventListener('resize', checkScreenSize);
</script>