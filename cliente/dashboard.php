<?php
/**
 * Dashboard do Cliente
 * LJ-OS Sistema para Lava Jato
 */

// Incluir configurações
require_once 'config.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: index.php');
    exit();
}

// Verificar se a sessão é válida
if (!verificarSessaoCliente()) {
    header('Location: index.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$cliente_nome = $_SESSION['cliente_nome'];

try {
    $db = getDB();
    
    // Buscar dados do cliente
    $stmt = $db->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
    
    // Estatísticas
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_veiculos,
            SUM(CASE WHEN v.status = 'ativo' THEN 1 ELSE 0 END) as veiculos_ativos
        FROM veiculos v 
        WHERE v.id_cliente = ?
    ");
    $stmt->execute([$cliente_id]);
    $stats_veiculos = $stmt->fetch();
    
    // Total de serviços
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_servicos, 
               SUM(CASE WHEN os.status = 'finalizada' THEN 1 ELSE 0 END) as servicos_concluidos,
               SUM(CASE WHEN os.status = 'em_andamento' THEN 1 ELSE 0 END) as servicos_andamento
        FROM ordens_servico os 
        WHERE os.id_cliente = ?
    ");
    $stmt->execute([$cliente_id]);
    $stats_servicos = $stmt->fetch();
    
    // Agendamentos futuros
    $stmt = $db->prepare("
        SELECT a.*, v.placa, v.marca, v.modelo, s.nome_servico
        FROM agendamentos a
        LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
        LEFT JOIN servicos s ON a.id_servico = s.id_servico
        WHERE a.id_cliente = ? AND a.data_agendamento >= NOW() AND a.status != 'cancelado'
        ORDER BY a.data_agendamento ASC
        LIMIT 5
    ");
    $stmt->execute([$cliente_id]);
    $agendamentos_futuros = $stmt->fetchAll();
    
    // Últimos serviços
    $stmt = $db->prepare("
        SELECT os.*, v.placa, v.marca, v.modelo
        FROM ordens_servico os
        LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
        WHERE os.id_cliente = ?
        ORDER BY os.data_abertura DESC
        LIMIT 5
    ");
    $stmt->execute([$cliente_id]);
    $ultimos_servicos = $stmt->fetchAll();
    
    // Saldo de créditos
    $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN tipo = 'ganho' THEN valor ELSE 0 END) as total_ganhos,
            SUM(CASE WHEN tipo = 'resgate' THEN valor ELSE 0 END) as total_resgates,
            SUM(CASE WHEN tipo = 'bonus' THEN valor ELSE 0 END) as total_bonus
        FROM creditos_cliente 
        WHERE id_cliente = ?
    ");
    $stmt->execute([$cliente_id]);
    $creditos = $stmt->fetch();
    
    $saldo_creditos = ($creditos['total_ganhos'] + $creditos['total_bonus']) - $creditos['total_resgates'];
    
} catch (Exception $e) {
    $erro = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Área do Cliente</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #bdbf90;
            --secondary-color: #35352b;
            --accent-color: #ec6c2b;
            --accent-light: #feae4b;
            --background-light: #e7e9c4;
            --text-dark: #35352b;
            --text-light: #ffffff;
            --border-color: #d1d3a7;
            --shadow-color: rgba(53, 53, 43, 0.1);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 24px;
        }
        
        body {
            background: var(--background-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            box-shadow: 0 2px 10px var(--shadow-color);
        }
        
        .navbar-brand {
            color: var(--text-light) !important;
            font-weight: 600;
        }
        
        .navbar-nav .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--accent-light) !important;
        }
        
        .main-content {
            padding: 30px 0;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--text-light);
            border-radius: var(--border-radius-xl);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px var(--shadow-color);
        }
        
        .stats-card {
            background: var(--text-light);
            border-radius: var(--border-radius-lg);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .credits-card {
            background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent-color) 100%);
            color: var(--text-light);
            border-radius: var(--border-radius-lg);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        
        .section-card {
            background: var(--text-light);
            border-radius: var(--border-radius-lg);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        
        .section-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .list-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        .item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .item-subtitle {
            color: var(--text-dark);
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: var(--border-radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-finalizada { background: #d4edda; color: #155724; }
        .status-andamento { background: #cce5ff; color: #004085; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        
        .btn-action {
            background: var(--accent-color);
            color: var(--text-light);
            border: none;
            border-radius: var(--border-radius-md);
            padding: 8px 16px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background: var(--accent-light);
            color: var(--text-light);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 0;
            }
            
            .welcome-card {
                padding: 20px;
            }
            
            .stats-card {
                padding: 20px;
            }
            
            .section-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-car-wash"></i>
                LJ-OS Cliente
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="veiculos.php">
                            <i class="fas fa-car"></i>
                            Veículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agendamentos.php">
                            <i class="fas fa-calendar"></i>
                            Agendamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="servicos.php">
                            <i class="fas fa-tools"></i>
                            Serviços
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="creditos.php">
                            <i class="fas fa-coins"></i>
                            Créditos
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($cliente_nome); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2>Olá, <?php echo htmlspecialchars($cliente_nome); ?>!</h2>
                        <p class="mb-0">Bem-vindo à sua área do cliente. Aqui você pode gerenciar seus veículos, agendamentos e acompanhar seus créditos.</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="fas fa-user-circle" style="font-size: 4rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stats-number"><?php echo $stats_veiculos['veiculos_ativos']; ?></div>
                        <div class="stats-label">Veículos Ativos</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="stats-number"><?php echo $stats_servicos['total_servicos']; ?></div>
                        <div class="stats-label">Total de Serviços</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="stats-card text-center">
                        <div class="stats-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stats-number"><?php echo count($agendamentos_futuros); ?></div>
                        <div class="stats-label">Agendamentos Futuros</div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="credits-card text-center">
                        <div class="stats-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stats-number">R$ <?php echo number_format($saldo_creditos, 2, ',', '.'); ?></div>
                        <div class="stats-label">Saldo de Créditos</div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <!-- Próximos Agendamentos -->
                <div class="col-lg-6">
                    <div class="section-card">
                        <h4 class="section-title">
                            <i class="fas fa-calendar-alt"></i>
                            Próximos Agendamentos
                        </h4>
                        
                        <?php if (empty($agendamentos_futuros)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <p>Nenhum agendamento futuro</p>
                                <a href="agendamentos.php" class="btn-action">
                                    <i class="fas fa-plus"></i>
                                    Agendar Serviço
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($agendamentos_futuros as $agendamento): ?>
                                <div class="list-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="item-title">
                                                <?php echo htmlspecialchars($agendamento['nome_servico']); ?>
                                            </div>
                                            <div class="item-subtitle">
                                                <i class="fas fa-car"></i>
                                                <?php echo htmlspecialchars($agendamento['placa'] . ' - ' . $agendamento['marca'] . ' ' . $agendamento['modelo']); ?>
                                            </div>
                                            <div class="item-subtitle">
                                                <i class="fas fa-clock"></i>
                                                <?php echo formatarDataHora($agendamento['data_agendamento']); ?>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <span class="status-badge status-<?php echo $agendamento['status']; ?>">
                                                <?php echo ucfirst($agendamento['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="agendamentos.php" class="btn-action">
                                    <i class="fas fa-eye"></i>
                                    Ver Todos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Últimos Serviços -->
                <div class="col-lg-6">
                    <div class="section-card">
                        <h4 class="section-title">
                            <i class="fas fa-history"></i>
                            Últimos Serviços
                        </h4>
                        
                        <?php if (empty($ultimos_servicos)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-tools fa-2x mb-2"></i>
                                <p>Nenhum serviço realizado</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($ultimos_servicos as $servico): ?>
                                <div class="list-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="item-title">
                                                OS #<?php echo str_pad($servico['id_ordem_servico'], 6, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="item-subtitle">
                                                <i class="fas fa-car"></i>
                                                <?php echo htmlspecialchars($servico['placa'] . ' - ' . $servico['marca'] . ' ' . $servico['modelo']); ?>
                                            </div>
                                                                                         <div class="item-subtitle">
                                                 <i class="fas fa-calendar"></i>
                                                 <?php echo formatarData($servico['data_abertura']); ?>
                                             </div>
                                        </div>
                                        <div class="ms-3">
                                            <span class="status-badge status-<?php echo $servico['status']; ?>">
                                                <?php echo ucfirst($servico['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="servicos.php" class="btn-action">
                                    <i class="fas fa-eye"></i>
                                    Ver Todos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="section-card">
                        <h4 class="section-title">
                            <i class="fas fa-bolt"></i>
                            Ações Rápidas
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="agendamentos.php?acao=novo" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-plus"></i>
                                    <br>
                                    Agendar Serviço
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="veiculos.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-car"></i>
                                    <br>
                                    Meus Veículos
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="creditos.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-coins"></i>
                                    <br>
                                    Meus Créditos
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="perfil.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-user-edit"></i>
                                    <br>
                                    Editar Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 