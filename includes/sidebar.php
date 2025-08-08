<?php
/**
 * Sidebar de Navegação
 * LJ-OS Sistema para Lava Jato
 */

$pagina_atual = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" style="display: none;">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'clientes' ? 'active' : ''; ?>" href="clientes.php">
                    <i class="fas fa-users"></i>
                    Clientes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'veiculos' ? 'active' : ''; ?>" href="veiculos.php">
                    <i class="fas fa-car"></i>
                    Veículos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'agendamentos' ? 'active' : ''; ?>" href="agendamentos.php">
                    <i class="fas fa-calendar-alt"></i>
                    Agendamentos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'ordens_servico' ? 'active' : ''; ?>" href="ordens_servico.php">
                    <i class="fas fa-clipboard-list"></i>
                    Ordens de Serviço
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'estoque' ? 'active' : ''; ?>" href="estoque.php">
                    <i class="fas fa-boxes"></i>
                    Controle de Estoque
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'financeiro' ? 'active' : ''; ?>" href="financeiro.php">
                    <i class="fas fa-chart-line"></i>
                    Módulo Financeiro
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'relatorios' ? 'active' : ''; ?>" href="relatorios.php">
                    <i class="fas fa-file-alt"></i>
                    Sistema de Relatórios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'usuarios' ? 'active' : ''; ?>" href="usuarios.php">
                    <i class="fas fa-user-cog"></i>
                    Gestão de Usuários
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'funcionarios' ? 'active' : ''; ?>" href="funcionarios.php">
                    <i class="fas fa-users-cog"></i>
                    Funcionários
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'orcamentos' ? 'active' : ''; ?>" href="orcamentos.php">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Orçamentos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $pagina_atual === 'permissoes' ? 'active' : ''; ?>" href="permissoes.php">
                    <i class="fas fa-shield-alt"></i>
                    Permissões
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Configurações</span>
        </h6>
        
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog"></i>
                    Configurações do Sistema
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-database"></i>
                    Backup
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </li>
        </ul>
    </div>
</nav> 