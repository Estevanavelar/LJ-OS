<?php
/**
 * LJ-OS Sistema para Lava Jato
 * Módulo Financeiro
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

// Buscar estatísticas financeiras do mês atual
$mesAtual = date('Y-m');
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as total_receitas,
        COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as total_despesas,
        COUNT(CASE WHEN tipo = 'receita' THEN 1 END) as qtd_receitas,
        COUNT(CASE WHEN tipo = 'despesa' THEN 1 END) as qtd_despesas
    FROM movimentacoes_financeiras 
    WHERE DATE_FORMAT(data_movimentacao, '%Y-%m') = ?
");
$stmt->execute([$mesAtual]);
$stats_mes = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar categorias para filtros
$stmt = $pdo->prepare("SELECT id_categoria, nome FROM categorias_financeiras WHERE status = 'ativo' ORDER BY nome");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar formas de pagamento
$formas_pagamento = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'transferencia', 'boleto'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Financeira - <?php echo $nome_empresa; ?></title>
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
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Gestão Financeira
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="abrirModalMovimentacao('receita')">
                                <i class="fas fa-plus me-1"></i>Nova Receita
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="abrirModalMovimentacao('despesa')">
                                <i class="fas fa-minus me-1"></i>Nova Despesa
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="abrirModalRelatorio()">
                                <i class="fas fa-chart-bar me-1"></i>Relatórios
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cards de Resumo -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Receitas do Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($stats_mes['total_receitas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-arrow-up fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Despesas do Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($stats_mes['total_despesas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-arrow-down fa-2x text-danger"></i>
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
                                            Saldo do Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $saldo = $stats_mes['total_receitas'] - $stats_mes['total_despesas'];
                                            $saldoClass = $saldo >= 0 ? 'text-success' : 'text-danger';
                                            ?>
                                            <span class="<?php echo $saldoClass; ?>">
                                                R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-info"></i>
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
                                            Total Movimentações
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats_mes['qtd_receitas'] + $stats_mes['qtd_despesas']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exchange-alt fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Fluxo de Caixa - Últimos 6 Meses</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="fluxoCaixaChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Receitas vs Despesas</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="receitasDespesasChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filtro_tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="filtro_tipo">
                                    <option value="">Todos</option>
                                    <option value="receita">Receitas</option>
                                    <option value="despesa">Despesas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_categoria" class="form-label">Categoria</label>
                                <select class="form-select" id="filtro_categoria">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($categoria['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="filtro_data_inicio">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="filtro_data_fim">
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="filtro_busca" placeholder="Descrição...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Movimentações -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabelaFinanceiro">
                                <thead class="table-light">
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th>Categoria</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Forma Pagamento</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados carregados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div id="paginacao" class="d-flex justify-content-center mt-3">
                            <!-- Paginação carregada via AJAX -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Movimentação -->
    <div class="modal fade" id="modalMovimentacao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMovimentacaoTitle">Nova Movimentação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMovimentacao">
                    <div class="modal-body">
                        <input type="hidden" id="mov_id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mov_tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="mov_tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="receita">Receita</option>
                                    <option value="despesa">Despesa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_categoria" class="form-label">Categoria *</label>
                                <select class="form-select" id="mov_categoria" name="id_categoria" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($categoria['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="mov_descricao" class="form-label">Descrição *</label>
                                <input type="text" class="form-control" id="mov_descricao" name="descricao" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_valor" class="form-label">Valor *</label>
                                <input type="number" class="form-control" id="mov_valor" name="valor" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_data" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="mov_data" name="data_movimentacao" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_forma_pagamento" class="form-label">Forma de Pagamento</label>
                                <select class="form-select" id="mov_forma_pagamento" name="forma_pagamento">
                                    <option value="">Selecione</option>
                                    <?php foreach ($formas_pagamento as $forma): ?>
                                    <option value="<?php echo $forma; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $forma)); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_status" class="form-label">Status</label>
                                <select class="form-select" id="mov_status" name="status">
                                    <option value="pago">Pago</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="mov_observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="mov_observacoes" name="observacoes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Relatório -->
    <div class="modal fade" id="modalRelatorio" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Relatórios Financeiros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                    <h6>Fluxo de Caixa</h6>
                                    <p class="text-muted">Relatório detalhado de receitas e despesas</p>
                                    <button type="button" class="btn btn-primary" onclick="gerarRelatorio('fluxo_caixa')">
                                        Gerar Relatório
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-pie fa-3x text-success mb-3"></i>
                                    <h6>Receitas por Categoria</h6>
                                    <p class="text-muted">Análise de receitas por categoria</p>
                                    <button type="button" class="btn btn-success" onclick="gerarRelatorio('receitas_categoria')">
                                        Gerar Relatório
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                                    <h6>Despesas por Categoria</h6>
                                    <p class="text-muted">Análise de despesas por categoria</p>
                                    <button type="button" class="btn btn-danger" onclick="gerarRelatorio('despesas_categoria')">
                                        Gerar Relatório
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-excel fa-3x text-info mb-3"></i>
                                    <h6>Exportar Dados</h6>
                                    <p class="text-muted">Exportar movimentações para Excel</p>
                                    <button type="button" class="btn btn-info" onclick="exportarDados()">
                                        Exportar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Helpers locais ausentes
        function mostrarAlerta(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} fade-in`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`;
            const mainContent = document.querySelector('.main-content') || document.body;
            mainContent.insertBefore(alertDiv, mainContent.firstChild);
            setTimeout(() => { alertDiv.style.opacity = '0'; setTimeout(() => alertDiv.remove(), 300); }, 5000);
        }

        function formatarData(isoDate) {
            if (!isoDate) return '';
            const d = new Date(isoDate);
            if (Number.isNaN(d.getTime())) {
                // Tenta tratar como string YYYY-MM-DD
                const parts = String(isoDate).split('-');
                if (parts.length === 3) return `${parts[2].padStart(2,'0')}/${parts[1].padStart(2,'0')}/${parts[0]}`;
                return isoDate;
            }
            const dia = String(d.getDate()).padStart(2, '0');
            const mes = String(d.getMonth() + 1).padStart(2, '0');
            const ano = d.getFullYear();
            return `${dia}/${mes}/${ano}`;
        }

        function formatarMoeda(valor) {
            const num = typeof valor === 'number' ? valor : parseFloat(valor);
            if (Number.isNaN(num)) return '0,00';
            return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function debounce(fn, delay = 300) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        let paginaAtual = 1;
        const itensPorPagina = 20;

        // Carregar dados ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            carregarMovimentacoes();
            carregarGraficos();
            
            // Event listeners para filtros
            document.getElementById('filtro_tipo').addEventListener('change', carregarMovimentacoes);
            document.getElementById('filtro_categoria').addEventListener('change', carregarMovimentacoes);
            document.getElementById('filtro_data_inicio').addEventListener('change', carregarMovimentacoes);
            document.getElementById('filtro_data_fim').addEventListener('change', carregarMovimentacoes);
            document.getElementById('filtro_busca').addEventListener('input', debounce(carregarMovimentacoes, 300));
        });

        function carregarMovimentacoes() {
            const tipo = document.getElementById('filtro_tipo').value;
            const categoria = document.getElementById('filtro_categoria').value;
            const dataInicio = document.getElementById('filtro_data_inicio').value;
            const dataFim = document.getElementById('filtro_data_fim').value;
            const busca = document.getElementById('filtro_busca').value;

            fetch('api/financeiro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar',
                    pagina: paginaAtual,
                    tipo: tipo,
                    categoria: categoria,
                    data_inicio: dataInicio,
                    data_fim: dataFim,
                    busca: busca
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderizarTabela(data.movimentacoes);
                    renderizarPaginacao(data.total, data.pagina);
                } else {
                    mostrarAlerta('Erro ao carregar movimentações: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar movimentações', 'danger');
            });
        }

        function renderizarTabela(movimentacoes) {
            const tbody = document.querySelector('#tabelaFinanceiro tbody');
            tbody.innerHTML = '';

            movimentacoes.forEach(mov => {
                const tr = document.createElement('tr');
                const tipoClass = mov.tipo === 'receita' ? 'text-success' : 'text-danger';
                const tipoIcon = mov.tipo === 'receita' ? 'fa-arrow-up' : 'fa-arrow-down';
                const statusClass = mov.status === 'pago' ? 'success' : 
                                   mov.status === 'pendente' ? 'warning' : 'secondary';
                
                tr.innerHTML = `
                    <td>${formatarData(mov.data_movimentacao)}</td>
                    <td>
                        <strong>${mov.descricao}</strong>
                        ${mov.observacoes ? `<br><small class="text-muted">${mov.observacoes}</small>` : ''}
                    </td>
                    <td>${mov.categoria_nome || '-'}</td>
                    <td>
                        <span class="${tipoClass}">
                            <i class="fas ${tipoIcon} me-1"></i>
                            ${mov.tipo.charAt(0).toUpperCase() + mov.tipo.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="${tipoClass}">
                            <strong>R$ ${formatarMoeda(mov.valor)}</strong>
                        </span>
                    </td>
                    <td>${mov.forma_pagamento ? mov.forma_pagamento.replace('_', ' ').toUpperCase() : '-'}</td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            ${mov.status.charAt(0).toUpperCase() + mov.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="editarMovimentacao(${mov.id_movimentacao})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-${mov.status === 'pago' ? 'warning' : 'success'}" 
                                    onclick="toggleStatusMovimentacao(${mov.id_movimentacao}, '${mov.status}')">
                                <i class="fas fa-${mov.status === 'pago' ? 'clock' : 'check'}"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="excluirMovimentacao(${mov.id_movimentacao})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderizarPaginacao(total, pagina) {
            const paginacao = document.getElementById('paginacao');
            const totalPaginas = Math.ceil(total / itensPorPagina);
            
            if (totalPaginas <= 1) {
                paginacao.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination">';
            
            // Botão anterior
            if (pagina > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPagina(${pagina - 1})">Anterior</a></li>`;
            }
            
            // Páginas
            for (let i = Math.max(1, pagina - 2); i <= Math.min(totalPaginas, pagina + 2); i++) {
                html += `<li class="page-item ${i === pagina ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="irParaPagina(${i})">${i}</a>
                         </li>`;
            }
            
            // Botão próximo
            if (pagina < totalPaginas) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="irParaPagina(${pagina + 1})">Próximo</a></li>`;
            }
            
            html += '</ul>';
            paginacao.innerHTML = html;
        }

        function irParaPagina(pagina) {
            paginaAtual = pagina;
            carregarMovimentacoes();
        }

        function abrirModalMovimentacao(tipo = null) {
            const modal = new bootstrap.Modal(document.getElementById('modalMovimentacao'));
            const form = document.getElementById('formMovimentacao');
            const title = document.getElementById('modalMovimentacaoTitle');
            
            form.reset();
            document.getElementById('mov_id').value = '';
            document.getElementById('mov_data').value = new Date().toISOString().split('T')[0];
            
            if (tipo) {
                document.getElementById('mov_tipo').value = tipo;
                title.textContent = tipo === 'receita' ? 'Nova Receita' : 'Nova Despesa';
            } else {
                title.textContent = 'Nova Movimentação';
            }
            
            modal.show();
        }

        function editarMovimentacao(id) {
            fetch('api/financeiro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'buscar',
                    id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const mov = data.movimentacao;
                    document.getElementById('mov_id').value = mov.id_movimentacao;
                    document.getElementById('mov_tipo').value = mov.tipo;
                    document.getElementById('mov_categoria').value = mov.id_categoria || '';
                    document.getElementById('mov_descricao').value = mov.descricao;
                    document.getElementById('mov_valor').value = mov.valor;
                    document.getElementById('mov_data').value = mov.data_movimentacao;
                    document.getElementById('mov_forma_pagamento').value = mov.forma_pagamento || '';
                    document.getElementById('mov_status').value = mov.status;
                    document.getElementById('mov_observacoes').value = mov.observacoes || '';
                    
                    document.getElementById('modalMovimentacaoTitle').textContent = 'Editar Movimentação';
                    const modal = new bootstrap.Modal(document.getElementById('modalMovimentacao'));
                    modal.show();
                } else {
                    mostrarAlerta('Erro ao carregar movimentação: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar movimentação', 'danger');
            });
        }

        function toggleStatusMovimentacao(id, statusAtual) {
            const novoStatus = statusAtual === 'pago' ? 'pendente' : 'pago';
            const confirmacao = confirm(`Deseja alterar o status para ${novoStatus}?`);
            
            if (confirmacao) {
                fetch('api/financeiro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'toggle_status',
                        id: id,
                        status: novoStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarAlerta('Status alterado com sucesso!', 'success');
                        carregarMovimentacoes();
                    } else {
                        mostrarAlerta('Erro ao alterar status: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlerta('Erro ao alterar status', 'danger');
                });
            }
        }

        function excluirMovimentacao(id) {
            const confirmacao = confirm('Tem certeza que deseja excluir esta movimentação?');
            
            if (confirmacao) {
                fetch('api/financeiro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'excluir',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarAlerta('Movimentação excluída com sucesso!', 'success');
                        carregarMovimentacoes();
                    } else {
                        mostrarAlerta('Erro ao excluir movimentação: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlerta('Erro ao excluir movimentação', 'danger');
                });
            }
        }

        function abrirModalRelatorio() {
            const modal = new bootstrap.Modal(document.getElementById('modalRelatorio'));
            modal.show();
        }

        function gerarRelatorio(tipo) {
            const dataInicio = document.getElementById('filtro_data_inicio').value;
            const dataFim = document.getElementById('filtro_data_fim').value;
            
            const params = new URLSearchParams({
                tipo: tipo,
                data_inicio: dataInicio,
                data_fim: dataFim
            });
            
            window.open(`api/financeiro.php?action=relatorio&${params.toString()}`, '_blank');
        }

        function exportarDados() {
            const tipo = document.getElementById('filtro_tipo').value;
            const categoria = document.getElementById('filtro_categoria').value;
            const dataInicio = document.getElementById('filtro_data_inicio').value;
            const dataFim = document.getElementById('filtro_data_fim').value;
            const busca = document.getElementById('filtro_busca').value;

            const params = new URLSearchParams({
                action: 'exportar',
                tipo: tipo,
                categoria: categoria,
                data_inicio: dataInicio,
                data_fim: dataFim,
                busca: busca
            });

            window.open(`api/financeiro.php?${params.toString()}`, '_blank');
        }

        function carregarGraficos() {
            // Gráfico de Fluxo de Caixa
            fetch('api/financeiro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'dados_grafico_fluxo'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    criarGraficoFluxoCaixa(data.dados);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar dados do gráfico:', error);
            });

            // Gráfico de Receitas vs Despesas
            fetch('api/financeiro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'dados_grafico_receitas_despesas'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    criarGraficoReceitasDespesas(data.dados);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar dados do gráfico:', error);
            });
        }

        function criarGraficoFluxoCaixa(dados) {
            const ctx = document.getElementById('fluxoCaixaChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dados.labels,
                    datasets: [{
                        label: 'Receitas',
                        data: dados.receitas,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Despesas',
                        data: dados.despesas,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function criarGraficoReceitasDespesas(dados) {
            const ctx = document.getElementById('receitasDespesasChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Receitas', 'Despesas'],
                    datasets: [{
                        data: [dados.receitas, dados.despesas],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgb(75, 192, 192)',
                            'rgb(255, 99, 132)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Event listeners para formulários
        document.getElementById('formMovimentacao').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('api/financeiro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'salvar_movimentacao',
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Movimentação salva com sucesso!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalMovimentacao')).hide();
                    carregarMovimentacoes();
                    carregarGraficos();
                } else {
                    mostrarAlerta('Erro ao salvar movimentação: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao salvar movimentação', 'danger');
            });
        });
    </script>
</body>
</html> 