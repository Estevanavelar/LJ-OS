<?php
/**
 * Dashboard Principal
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/header.php';

// Obter estatísticas do dashboard
try {
    $db = getDB();
    
    // Estatísticas gerais
    $stats = [];
    
    // Total de clientes
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE status = 'ativo'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['clientes'] = $stmt->fetch()['total'];
    
    // Total de veículos
    $sql = "SELECT COUNT(*) as total FROM veiculos WHERE status = 'ativo'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['veiculos'] = $stmt->fetch()['total'];
    
    // Ordens de serviço do dia
    $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE DATE(data_abertura) = CURDATE()";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['os_hoje'] = $stmt->fetch()['total'];
    
    // Faturamento do dia
    $sql = "SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico WHERE DATE(data_abertura) = CURDATE() AND status = 'finalizada'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['faturamento_hoje'] = $stmt->fetch()['total'];
    
    // Agendamentos de hoje
    $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE DATE(data_agendamento) = CURDATE() AND status IN ('pendente', 'confirmado')";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['agendamentos_hoje'] = $stmt->fetch()['total'];
    
    // OS em andamento
    $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE status = 'em_andamento'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['os_andamento'] = $stmt->fetch()['total'];
    
    // Produtos com estoque baixo
    $sql = "SELECT COUNT(*) as total FROM produtos WHERE estoque_atual <= estoque_minimo AND status = 'ativo'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['estoque_baixo'] = $stmt->fetch()['total'];
    
    // Últimas OS
    $sql = "SELECT os.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo 
            FROM ordens_servico os 
            JOIN clientes c ON os.id_cliente = c.id_cliente 
            JOIN veiculos v ON os.id_veiculo = v.id_veiculo 
            ORDER BY os.data_abertura DESC 
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $ultimas_os = $stmt->fetchAll();
    
    // Próximos agendamentos
    $sql = "SELECT a.*, c.nome as cliente_nome, v.placa, s.nome_servico 
            FROM agendamentos a 
            JOIN clientes c ON a.id_cliente = c.id_cliente 
            JOIN veiculos v ON a.id_veiculo = v.id_veiculo 
            JOIN servicos s ON a.id_servico = s.id_servico 
            WHERE a.data_agendamento >= NOW() 
            AND a.status IN ('pendente', 'confirmado') 
            ORDER BY a.data_agendamento ASC 
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $proximos_agendamentos = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro ao carregar dashboard: " . $e->getMessage());
    $stats = [
        'clientes' => 0,
        'veiculos' => 0,
        'os_hoje' => 0,
        'faturamento_hoje' => 0,
        'agendamentos_hoje' => 0,
        'os_andamento' => 0,
        'estoque_baixo' => 0
    ];
    $ultimas_os = [];
    $proximos_agendamentos = [];
}
?>

<h1 class="page-title">
    <i class="fas fa-tachometer-alt"></i>
    Dashboard
</h1>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['clientes']); ?></div>
        <div class="stat-label">Clientes Ativos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['veiculos']); ?></div>
        <div class="stat-label">Veículos Cadastrados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['os_hoje']); ?></div>
        <div class="stat-label">OS de Hoje</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo formatarMoeda($stats['faturamento_hoje']); ?></div>
        <div class="stat-label">Faturamento Hoje</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['agendamentos_hoje']); ?></div>
        <div class="stat-label">Agendamentos Hoje</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['os_andamento']); ?></div>
        <div class="stat-label">OS em Andamento</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($stats['estoque_baixo']); ?></div>
        <div class="stat-label">Produtos com Estoque Baixo</div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ações Rápidas</h3>
        <i class="fas fa-bolt card-icon"></i>
    </div>
    <div class="d-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <a href="ordens_servico.php?acao=nova" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nova OS
        </a>
        <a href="agendamentos.php?acao=novo" class="btn btn-secondary">
            <i class="fas fa-calendar-plus"></i>
            Novo Agendamento
        </a>
        <a href="clientes.php?acao=novo" class="btn btn-accent">
            <i class="fas fa-user-plus"></i>
            Novo Cliente
        </a>
        <a href="veiculos.php?acao=novo" class="btn btn-success">
            <i class="fas fa-car"></i>
            Novo Veículo
        </a>
    </div>
</div>

<!-- Conteúdo em duas colunas -->
<div class="d-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Últimas Ordens de Serviço -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Últimas Ordens de Serviço</h3>
            <i class="fas fa-clipboard-list card-icon"></i>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Status</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimas_os)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhuma OS encontrada</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimas_os as $os): ?>
                            <tr>
                                <td>
                                    <a href="ordens_servico.php?acao=visualizar&id=<?php echo $os['id_os']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($os['codigo_os']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($os['cliente_nome']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($os['marca'] . ' ' . $os['modelo']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($os['placa']); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($os['status']) {
                                        case 'aberta':
                                            $status_class = 'badge-warning';
                                            $status_text = 'Aberta';
                                            break;
                                        case 'em_andamento':
                                            $status_class = 'badge-info';
                                            $status_text = 'Em Andamento';
                                            break;
                                        case 'finalizada':
                                            $status_class = 'badge-success';
                                            $status_text = 'Finalizada';
                                            break;
                                        case 'cancelada':
                                            $status_class = 'badge-danger';
                                            $status_text = 'Cancelada';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo formatarMoeda($os['valor_total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            <a href="ordens_servico.php" class="btn btn-outline">Ver Todas</a>
        </div>
    </div>
    
    <!-- Próximos Agendamentos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Próximos Agendamentos</h3>
            <i class="fas fa-calendar-alt card-icon"></i>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Serviço</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proximos_agendamentos)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum agendamento encontrado</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proximos_agendamentos as $agendamento): ?>
                            <tr>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($agendamento['placa']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($agendamento['nome_servico']); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($agendamento['status']) {
                                        case 'pendente':
                                            $status_class = 'badge-warning';
                                            $status_text = 'Pendente';
                                            break;
                                        case 'confirmado':
                                            $status_class = 'badge-success';
                                            $status_text = 'Confirmado';
                                            break;
                                        case 'em_andamento':
                                            $status_class = 'badge-info';
                                            $status_text = 'Em Andamento';
                                            break;
                                        case 'concluido':
                                            $status_class = 'badge-success';
                                            $status_text = 'Concluído';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'badge-danger';
                                            $status_text = 'Cancelado';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            <a href="agendamentos.php" class="btn btn-outline">Ver Todos</a>
        </div>
    </div>
</div>

<!-- Gráficos e Relatórios -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Relatórios Rápidos</h3>
        <i class="fas fa-chart-bar card-icon"></i>
    </div>
    <div class="d-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <a href="relatorios.php?tipo=faturamento" class="btn btn-outline">
            <i class="fas fa-dollar-sign"></i>
            Relatório de Faturamento
        </a>
        <a href="relatorios.php?tipo=servicos" class="btn btn-outline">
            <i class="fas fa-tools"></i>
            Relatório de Serviços
        </a>
        <a href="relatorios.php?tipo=clientes" class="btn btn-outline">
            <i class="fas fa-users"></i>
            Relatório de Clientes
        </a>
        <a href="relatorios.php?tipo=estoque" class="btn btn-outline">
            <i class="fas fa-boxes"></i>
            Relatório de Estoque
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 