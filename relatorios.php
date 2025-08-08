<?php
/**
 * LJ-OS Sistema para Lava Jato
 * Sistema de Relatórios
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Obter conexão com o banco de dados
$pdo = getDB();

// Buscar configurações
$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'nome_empresa' LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);
$nome_empresa = $config['valor'] ?? 'LJ-OS Sistema para Lava Jato';

// Buscar estatísticas gerais
$mesAtual = date('Y-m');
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM clientes WHERE status = 'ativo') as total_clientes,
        (SELECT COUNT(*) FROM veiculos WHERE status = 'ativo') as total_veiculos,
        (SELECT COUNT(*) FROM ordens_servico WHERE DATE_FORMAT(data_abertura, '%Y-%m') = ?) as os_mes,
        (SELECT COUNT(*) FROM agendamentos WHERE DATE_FORMAT(data_agendamento, '%Y-%m') = ?) as agendamentos_mes,
        (SELECT COALESCE(SUM(valor_total), 0) FROM ordens_servico WHERE DATE_FORMAT(data_abertura, '%Y-%m') = ? AND status = 'finalizada') as faturamento_mes
");
$stmt->execute([$mesAtual, $mesAtual, $mesAtual]);
$stats_gerais = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Relatórios - <?php echo $nome_empresa; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Sistema de Relatórios
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="gerarRelatorioCompleto()">
                                <i class="fas fa-file-pdf me-1"></i>Relatório Completo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportarDados()">
                                <i class="fas fa-file-excel me-1"></i>Exportar Dados
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cards de Resumo -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total de Clientes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_gerais['total_clientes']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total de Veículos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_gerais['total_veiculos']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-car fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            OS do Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_gerais['os_mes']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Faturamento do Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($stats_gerais['faturamento_mes'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filtro_data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="filtro_data_inicio">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="filtro_data_fim">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_tipo" class="form-label">Tipo de Relatório</label>
                                <select class="form-select" id="filtro_tipo">
                                    <option value="">Selecione o tipo</option>
                                    <option value="vendas">Relatório de Vendas</option>
                                    <option value="clientes">Relatório de Clientes</option>
                                    <option value="servicos">Relatório de Serviços</option>
                                    <option value="financeiro">Relatório Financeiro</option>
                                    <option value="agendamentos">Relatório de Agendamentos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_formato" class="form-label">Formato</label>
                                <select class="form-select" id="filtro_formato">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Faturamento Mensal - Últimos 12 Meses</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="faturamentoChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Serviços Mais Vendidos</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="servicosChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Relatórios Específicos -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Relatório de Vendas
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Análise detalhada de vendas, incluindo:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Faturamento por período</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Serviços mais vendidos</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Performance por funcionário</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Análise de tendências</li>
                                </ul>
                                <button type="button" class="btn btn-primary" onclick="gerarRelatorioEspecifico('vendas')">
                                    <i class="fas fa-download me-1"></i>Gerar Relatório
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-users me-2"></i>
                                    Relatório de Clientes
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Análise completa de clientes, incluindo:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Novos clientes por período</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Clientes mais ativos</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Valor médio por cliente</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Segmentação de clientes</li>
                                </ul>
                                <button type="button" class="btn btn-primary" onclick="gerarRelatorioEspecifico('clientes')">
                                    <i class="fas fa-download me-1"></i>Gerar Relatório
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-tools me-2"></i>
                                    Relatório de Serviços
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Análise detalhada de serviços, incluindo:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Serviços mais solicitados</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Tempo médio de execução</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Lucratividade por serviço</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Satisfação dos clientes</li>
                                </ul>
                                <button type="button" class="btn btn-primary" onclick="gerarRelatorioEspecifico('servicos')">
                                    <i class="fas fa-download me-1"></i>Gerar Relatório
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Relatório de Agendamentos
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Análise de agendamentos, incluindo:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Taxa de comparecimento</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Horários mais solicitados</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Cancelamentos e reagendamentos</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Capacidade de atendimento</li>
                                </ul>
                                <button type="button" class="btn btn-primary" onclick="gerarRelatorioEspecifico('agendamentos')">
                                    <i class="fas fa-download me-1"></i>Gerar Relatório
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Relatórios Gerados -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history me-2"></i>
                            Histórico de Relatórios
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabelaRelatorios">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Período</th>
                                        <th>Formato</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados carregados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Configurações de Relatório -->
    <div class="modal fade" id="modalConfigRelatorio" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurações do Relatório</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formConfigRelatorio">
                    <div class="modal-body">
                        <input type="hidden" id="config_tipo" name="tipo">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="config_data_inicio" class="form-label">Data Início *</label>
                                <input type="date" class="form-control" id="config_data_inicio" name="data_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="config_data_fim" class="form-label">Data Fim *</label>
                                <input type="date" class="form-control" id="config_data_fim" name="data_fim" required>
                            </div>
                            <div class="col-md-6">
                                <label for="config_formato" class="form-label">Formato *</label>
                                <select class="form-select" id="config_formato" name="formato" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="config_agrupamento" class="form-label">Agrupamento</label>
                                <select class="form-select" id="config_agrupamento" name="agrupamento">
                                    <option value="diario">Diário</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="mensal">Mensal</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="config_filtros" class="form-label">Filtros Adicionais</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="config_apenas_concluidos" name="apenas_concluidos">
                                    <label class="form-check-label" for="config_apenas_concluidos">
                                        Apenas serviços concluídos
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="config_apenas_pagos" name="apenas_pagos">
                                    <label class="form-check-label" for="config_apenas_pagos">
                                        Apenas pagamentos confirmados
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="config_incluir_graficos" name="incluir_graficos">
                                    <label class="form-check-label" for="config_incluir_graficos">
                                        Incluir gráficos no relatório
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="config_observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="config_observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Carregar dados ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            carregarGraficos();
            carregarRelatorios();
            
            // Definir datas padrão (último mês)
            const hoje = new Date();
            const umMesAtras = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
            
            document.getElementById('filtro_data_inicio').value = umMesAtras.toISOString().split('T')[0];
            document.getElementById('filtro_data_fim').value = hoje.toISOString().split('T')[0];
        });

        function carregarGraficos() {
            // Gráfico de Faturamento
            fetch('api/relatorios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'dados_grafico_faturamento'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    criarGraficoFaturamento(data.dados);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar dados do gráfico:', error);
            });

            // Gráfico de Serviços
            fetch('api/relatorios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'dados_grafico_servicos'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    criarGraficoServicos(data.dados);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar dados do gráfico:', error);
            });
        }

        function criarGraficoFaturamento(dados) {
            const ctx = document.getElementById('faturamentoChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dados.labels,
                    datasets: [{
                        label: 'Faturamento (R$)',
                        data: dados.faturamento,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Faturamento: R$ ' + context.parsed.y.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        }

        function criarGraficoServicos(dados) {
            const ctx = document.getElementById('servicosChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dados.labels,
                    datasets: [{
                        data: dados.valores,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 205, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        function carregarRelatorios() {
            fetch('api/relatorios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar_relatorios'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderizarTabelaRelatorios(data.relatorios);
                } else {
                    mostrarAlerta('Erro ao carregar relatórios: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar relatórios', 'danger');
            });
        }

        function renderizarTabelaRelatorios(relatorios) {
            const tbody = document.querySelector('#tabelaRelatorios tbody');
            tbody.innerHTML = '';

            relatorios.forEach(rel => {
                const tr = document.createElement('tr');
                const statusClass = rel.status === 'concluido' ? 'success' : 
                                   rel.status === 'processando' ? 'warning' : 'secondary';
                const statusIcon = rel.status === 'concluido' ? 'fa-check' : 
                                  rel.status === 'processando' ? 'fa-clock' : 'fa-times';
                
                tr.innerHTML = `
                    <td>${formatarData(rel.data_geracao)}</td>
                    <td>
                        <span class="badge bg-primary">${rel.tipo.charAt(0).toUpperCase() + rel.tipo.slice(1)}</span>
                    </td>
                    <td>${formatarData(rel.data_inicio)} a ${formatarData(rel.data_fim)}</td>
                    <td>
                        <span class="badge bg-info">${rel.formato.toUpperCase()}</span>
                    </td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            <i class="fas ${statusIcon} me-1"></i>
                            ${rel.status.charAt(0).toUpperCase() + rel.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${rel.status === 'concluido' ? 
                              `<button type="button" class="btn btn-outline-success" onclick="baixarRelatorio(${rel.id_relatorio})">
                                   <i class="fas fa-download"></i>
                               </button>` : ''}
                            <button type="button" class="btn btn-outline-danger" onclick="excluirRelatorio(${rel.id_relatorio})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function gerarRelatorioCompleto() {
            const dataInicio = document.getElementById('filtro_data_inicio').value;
            const dataFim = document.getElementById('filtro_data_fim').value;
            const formato = document.getElementById('filtro_formato').value;
            
            if (!dataInicio || !dataFim) {
                mostrarAlerta('Selecione o período para o relatório', 'warning');
                return;
            }
            
            const params = new URLSearchParams({
                action: 'relatorio_completo',
                data_inicio: dataInicio,
                data_fim: dataFim,
                formato: formato
            });
            
            window.open(`api/relatorios.php?${params.toString()}`, '_blank');
        }

        function gerarRelatorioEspecifico(tipo) {
            document.getElementById('config_tipo').value = tipo;
            
            // Definir datas padrão
            const hoje = new Date();
            const umMesAtras = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
            
            document.getElementById('config_data_inicio').value = umMesAtras.toISOString().split('T')[0];
            document.getElementById('config_data_fim').value = hoje.toISOString().split('T')[0];
            
            const modal = new bootstrap.Modal(document.getElementById('modalConfigRelatorio'));
            modal.show();
        }

        function exportarDados() {
            const dataInicio = document.getElementById('filtro_data_inicio').value;
            const dataFim = document.getElementById('filtro_data_fim').value;
            const tipo = document.getElementById('filtro_tipo').value;
            const formato = document.getElementById('filtro_formato').value;
            
            if (!dataInicio || !dataFim) {
                mostrarAlerta('Selecione o período para exportação', 'warning');
                return;
            }
            
            const params = new URLSearchParams({
                action: 'exportar_dados',
                data_inicio: dataInicio,
                data_fim: dataFim,
                tipo: tipo,
                formato: formato
            });
            
            window.open(`api/relatorios.php?${params.toString()}`, '_blank');
        }

        function baixarRelatorio(id) {
            window.open(`api/relatorios.php?action=baixar&id=${id}`, '_blank');
        }

        function excluirRelatorio(id) {
            const confirmacao = confirm('Tem certeza que deseja excluir este relatório?');
            
            if (confirmacao) {
                fetch('api/relatorios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'excluir_relatorio',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarAlerta('Relatório excluído com sucesso!', 'success');
                        carregarRelatorios();
                    } else {
                        mostrarAlerta('Erro ao excluir relatório: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlerta('Erro ao excluir relatório', 'danger');
                });
            }
        }

        // Event listener para formulário de configuração
        document.getElementById('formConfigRelatorio').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('api/relatorios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'gerar_relatorio',
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Relatório solicitado com sucesso! Você será notificado quando estiver pronto.', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalConfigRelatorio')).hide();
                    carregarRelatorios();
                } else {
                    mostrarAlerta('Erro ao gerar relatório: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao gerar relatório', 'danger');
            });
        });
    </script>
</body>
</html> 