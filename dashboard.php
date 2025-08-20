
<?php
/**
 * Dashboard Principal
 * LJ-OS Sistema para Lava Jato
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

// Verificar se está logado
verificarLogin();

$db = getDB();

// Buscar estatísticas
$stats = [
    'clientes' => $db->query("SELECT COUNT(*) FROM clientes WHERE ativo = 1")->fetchColumn(),
    'veiculos' => $db->query("SELECT COUNT(*) FROM veiculos WHERE ativo = 1")->fetchColumn(),
    'servicos' => $db->query("SELECT COUNT(*) FROM servicos WHERE ativo = 1")->fetchColumn(),
    'funcionarios' => $db->query("SELECT COUNT(*) FROM funcionarios WHERE ativo = 1")->fetchColumn()
];

// Buscar configurações da empresa
$stmt = $db->query("SELECT chave, valor FROM configuracoes WHERE categoria = 'empresa'");
$configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$nome_empresa = $configs['nome_empresa'] ?? 'LJ-OS Sistema';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $nome_empresa; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.info {
            border-left-color: var(--info-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger-color);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 0.75rem 1rem;
            margin: 0.25rem;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-car-front"></i> <?php echo $nome_empresa; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes.php">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="veiculos.php">
                            <i class="bi bi-car-front-fill"></i> Veículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="servicos.php">
                            <i class="bi bi-tools"></i> Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ordens_servico.php">
                            <i class="bi bi-clipboard-check"></i> Ordens de Serviço
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['usuario_nome']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="configuracoes.php">
                                <i class="bi bi-gear"></i> Configurações
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <h2><i class="bi bi-hand-thumbs-up"></i> Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h2>
            <p class="mb-0">Sistema instalado com sucesso! Aqui está o painel de controle do seu lava jato.</p>
        </div>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Clientes</h6>
                            <div class="stat-number"><?php echo $stats['clientes']; ?></div>
                        </div>
                        <i class="bi bi-people fs-1 text-success"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Veículos</h6>
                            <div class="stat-number"><?php echo $stats['veiculos']; ?></div>
                        </div>
                        <i class="bi bi-car-front-fill fs-1 text-info"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Serviços</h6>
                            <div class="stat-number"><?php echo $stats['servicos']; ?></div>
                        </div>
                        <i class="bi bi-tools fs-1 text-warning"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Funcionários</h6>
                            <div class="stat-number"><?php echo $stats['funcionarios']; ?></div>
                        </div>
                        <i class="bi bi-person-badge fs-1 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="row">
            <div class="col-md-12">
                <div class="quick-actions">
                    <h5 class="mb-3"><i class="bi bi-lightning"></i> Ações Rápidas</h5>
                    
                    <a href="clientes.php" class="action-btn">
                        <i class="bi bi-person-plus"></i> Novo Cliente
                    </a>
                    
                    <a href="veiculos.php" class="action-btn">
                        <i class="bi bi-car-front"></i> Novo Veículo
                    </a>
                    
                    <a href="ordens_servico.php" class="action-btn">
                        <i class="bi bi-clipboard-plus"></i> Nova OS
                    </a>
                    
                    <a href="agendamentos.php" class="action-btn">
                        <i class="bi bi-calendar-plus"></i> Novo Agendamento
                    </a>
                    
                    <a href="funcionarios.php" class="action-btn">
                        <i class="bi bi-person-badge"></i> Funcionários
                    </a>
                    
                    <a href="estoque.php" class="action-btn">
                        <i class="bi bi-box"></i> Estoque
                    </a>
                    
                    <a href="financeiro.php" class="action-btn">
                        <i class="bi bi-currency-dollar"></i> Financeiro
                    </a>
                    
                    <a href="relatorios.php" class="action-btn">
                        <i class="bi bi-graph-up"></i> Relatórios
                    </a>
                </div>
            </div>
        </div>

        <!-- Informações do Sistema -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Sistema Instalado com Sucesso!</h6>
                    <p class="mb-2">O LJ-OS está funcionando perfeitamente no Replit. Aqui estão algumas informações importantes:</p>
                    <ul class="mb-0">
                        <li><strong>Banco de dados:</strong> SQLite configurado e funcionando</li>
                        <li><strong>Usuário admin:</strong> admin@lavajato.com / admin123</li>
                        <li><strong>Funcionalidades:</strong> Todos os módulos estão disponíveis</li>
                        <li><strong>Próximo passo:</strong> Configure os dados da sua empresa em Configurações</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
