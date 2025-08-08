<?php
/**
 * LJ-OS Sistema para Lava Jato
 * Módulo de Controle de Estoque
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

// Buscar categorias para o filtro (categorias únicas dos produtos)
$stmt = $pdo->prepare("SELECT DISTINCT categoria as nome FROM produtos WHERE categoria IS NOT NULL AND categoria != '' AND status = 'ativo' ORDER BY categoria");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos com estoque baixo
$stmt = $pdo->prepare("
    SELECT p.*, p.categoria as categoria_nome 
    FROM produtos p 
    WHERE p.estoque_atual <= p.estoque_minimo AND p.status = 'ativo'
    ORDER BY p.estoque_atual ASC 
    LIMIT 5
");
$stmt->execute();
$produtos_estoque_baixo = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque - <?php echo $nome_empresa; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        Controle de Estoque
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="abrirModalProduto()">
                                <i class="fas fa-plus me-1"></i>Novo Produto
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="abrirModalMovimentacao()">
                                <i class="fas fa-exchange-alt me-1"></i>Movimentação
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="exportarEstoque()">
                                <i class="fas fa-download me-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Estoque Baixo -->
                <?php if (!empty($produtos_estoque_baixo)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Produtos com Estoque Baixo
                    </h6>
                    <div class="row">
                        <?php foreach ($produtos_estoque_baixo as $produto): ?>
                        <div class="col-md-6 col-lg-4 mb-2">
                            <small>
                                <strong><?php echo htmlspecialchars($produto['nome_produto']); ?></strong> - 
                                Estoque: <?php echo $produto['estoque_atual']; ?> 
                                (Mín: <?php echo $produto['estoque_minimo']; ?>)
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filtro_categoria" class="form-label">Categoria</label>
                                <select class="form-select" id="filtro_categoria">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria['nome']); ?>">
                                        <?php echo htmlspecialchars($categoria['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_status" class="form-label">Status</label>
                                <select class="form-select" id="filtro_status">
                                    <option value="">Todos</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_estoque" class="form-label">Estoque</label>
                                <select class="form-select" id="filtro_estoque">
                                    <option value="">Todos</option>
                                    <option value="baixo">Estoque Baixo</option>
                                    <option value="normal">Estoque Normal</option>
                                    <option value="zerado">Sem Estoque</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="filtro_busca" placeholder="Nome, código...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Produtos -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabelaEstoque">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Estoque</th>
                                        <th>Preço</th>
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

    <!-- Modal Produto -->
    <div class="modal fade" id="modalProduto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoTitle">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProduto">
                    <div class="modal-body">
                        <input type="hidden" id="produto_id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="produto_nome" class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" id="produto_nome" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="produto_codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="produto_codigo" name="codigo">
                            </div>
                            <div class="col-md-6">
                                <label for="produto_categoria" class="form-label">Categoria</label>
                                <select class="form-select" id="produto_categoria" name="categoria">
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria['nome']); ?>">
                                        <?php echo htmlspecialchars($categoria['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="produto_unidade" class="form-label">Unidade</label>
                                <select class="form-select" id="produto_unidade" name="unidade">
                                    <option value="unidade">Unidade</option>
                                    <option value="litro">Litro</option>
                                    <option value="kg">Quilograma</option>
                                    <option value="metro">Metro</option>
                                    <option value="pacote">Pacote</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="produto_quantidade" class="form-label">Quantidade em Estoque</label>
                                <input type="number" class="form-control" id="produto_quantidade" name="quantidade_estoque" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="produto_minimo" class="form-label">Estoque Mínimo</label>
                                <input type="number" class="form-control" id="produto_minimo" name="estoque_minimo" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="produto_preco" class="form-label">Preço Unitário</label>
                                <input type="number" class="form-control" id="produto_preco" name="preco_unitario" min="0" step="0.01">
                            </div>
                            <div class="col-12">
                                <label for="produto_descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="produto_descricao" name="descricao" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="produto_status" class="form-label">Status</label>
                                <select class="form-select" id="produto_status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
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

    <!-- Modal Movimentação -->
    <div class="modal fade" id="modalMovimentacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Movimentação de Estoque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMovimentacao">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="mov_produto" class="form-label">Produto *</label>
                                <select class="form-select" id="mov_produto" name="id_produto" required>
                                    <option value="">Selecione um produto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="mov_tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                    <option value="ajuste">Ajuste</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mov_quantidade" class="form-label">Quantidade *</label>
                                <input type="number" class="form-control" id="mov_quantidade" name="quantidade" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-12">
                                <label for="mov_motivo" class="form-label">Motivo/Observação</label>
                                <textarea class="form-control" id="mov_motivo" name="motivo" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Histórico -->
    <div class="modal fade" id="modalHistorico" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico de Movimentações</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaHistorico">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Estoque Final</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Carregado via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let paginaAtual = 1;
        const itensPorPagina = 20;

        // Funções auxiliares
        function formatarMoeda(valor) {
            return parseFloat(valor || 0).toFixed(2).replace('.', ',');
        }

        function formatarData(data) {
            return new Date(data).toLocaleDateString('pt-BR') + ' ' + 
                   new Date(data).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function mostrarAlerta(mensagem, tipo = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Carregar produtos ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            // Tentar carregar produtos, mas não bloquear se houver erro
            try {
                carregarProdutos();
                carregarProdutosMovimentacao();
            } catch (error) {
                console.error('Erro ao carregar produtos iniciais:', error);
            }
            
            // Event listeners para filtros
            document.getElementById('filtro_categoria').addEventListener('change', carregarProdutos);
            document.getElementById('filtro_status').addEventListener('change', carregarProdutos);
            document.getElementById('filtro_estoque').addEventListener('change', carregarProdutos);
            document.getElementById('filtro_busca').addEventListener('input', debounce(carregarProdutos, 300));
        });

        function carregarProdutos() {
            const categoria = document.getElementById('filtro_categoria').value;
            const status = document.getElementById('filtro_status').value;
            const estoque = document.getElementById('filtro_estoque').value;
            const busca = document.getElementById('filtro_busca').value;

            fetch('api/estoque.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar',
                    pagina: paginaAtual,
                    categoria: categoria,
                    status: status,
                    estoque: estoque,
                    busca: busca
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderizarTabela(data.produtos);
                    renderizarPaginacao(data.total, data.pagina);
                } else {
                    mostrarAlerta('Erro ao carregar produtos: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar produtos', 'danger');
            });
        }

        function renderizarTabela(produtos) {
            const tbody = document.querySelector('#tabelaEstoque tbody');
            tbody.innerHTML = '';

            produtos.forEach(produto => {
                const tr = document.createElement('tr');
                const estoqueClass = produto.estoque_atual <= produto.estoque_minimo ? 'text-danger' : 
                                   produto.estoque_atual == 0 ? 'text-warning' : 'text-success';
                
                tr.innerHTML = `
                    <td>${produto.codigo_produto || '-'}</td>
                    <td>
                        <strong>${produto.nome_produto}</strong>
                        ${produto.descricao ? `<br><small class="text-muted">${produto.descricao}</small>` : ''}
                    </td>
                    <td>${produto.categoria_nome || '-'}</td>
                    <td>
                        <span class="${estoqueClass}">
                            <i class="fas fa-box me-1"></i>
                            ${produto.estoque_atual} ${produto.unidade_medida}
                        </span>
                        ${produto.estoque_atual <= produto.estoque_minimo ? 
                          `<br><small class="text-danger">Mín: ${produto.estoque_minimo}</small>` : ''}
                    </td>
                    <td>R$ ${formatarMoeda(produto.preco_venda)}</td>
                    <td>
                        <span class="badge bg-${produto.status === 'ativo' ? 'success' : 'secondary'}">
                            ${produto.status === 'ativo' ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="editarProduto(${produto.id_produto})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="verHistorico(${produto.id_produto})">
                                <i class="fas fa-history"></i>
                            </button>
                            <button type="button" class="btn btn-outline-${produto.status === 'ativo' ? 'warning' : 'success'}" 
                                    onclick="toggleStatusProduto(${produto.id_produto}, '${produto.status}')">
                                <i class="fas fa-${produto.status === 'ativo' ? 'pause' : 'play'}"></i>
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
            carregarProdutos();
        }

        function abrirModalProduto(id = null) {
            console.log('Função abrirModalProduto chamada', id);
            
            const modal = new bootstrap.Modal(document.getElementById('modalProduto'));
            const form = document.getElementById('formProduto');
            const title = document.getElementById('modalProdutoTitle');
            
            form.reset();
            document.getElementById('produto_id').value = '';
            
            if (id) {
                title.textContent = 'Editar Produto';
                carregarProduto(id);
            } else {
                title.textContent = 'Novo Produto';
            }
            
            modal.show();
        }

        function carregarProduto(id) {
            fetch('api/estoque.php', {
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
                    const produto = data.produto;
                    document.getElementById('produto_id').value = produto.id_produto;
                    document.getElementById('produto_nome').value = produto.nome_produto;
                    document.getElementById('produto_codigo').value = produto.codigo_produto || '';
                    document.getElementById('produto_categoria').value = produto.categoria || '';
                    document.getElementById('produto_unidade').value = produto.unidade_medida;
                    document.getElementById('produto_quantidade').value = produto.estoque_atual;
                    document.getElementById('produto_minimo').value = produto.estoque_minimo;
                    document.getElementById('produto_preco').value = produto.preco_venda;
                    document.getElementById('produto_descricao').value = produto.descricao || '';
                    document.getElementById('produto_status').value = produto.status;
                } else {
                    mostrarAlerta('Erro ao carregar produto: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar produto', 'danger');
            });
        }

        function editarProduto(id) {
            abrirModalProduto(id);
        }

        function toggleStatusProduto(id, statusAtual) {
            const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
            const confirmacao = confirm(`Deseja ${novoStatus === 'ativo' ? 'ativar' : 'desativar'} este produto?`);
            
            if (confirmacao) {
                fetch('api/estoque.php', {
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
                        carregarProdutos();
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

        function abrirModalMovimentacao() {
            console.log('Função abrirModalMovimentacao chamada');
            
            const modal = new bootstrap.Modal(document.getElementById('modalMovimentacao'));
            document.getElementById('formMovimentacao').reset();
            modal.show();
        }

        function carregarProdutosMovimentacao() {
            fetch('api/estoque.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar_produtos_ativos'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('mov_produto');
                    select.innerHTML = '<option value="">Selecione um produto</option>';
                    
                    data.produtos.forEach(produto => {
                        const option = document.createElement('option');
                        option.value = produto.id_produto;
                        option.textContent = `${produto.nome_produto} (Estoque: ${produto.estoque_atual} ${produto.unidade_medida})`;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }

        function verHistorico(id) {
            fetch('api/estoque.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'historico',
                    id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.querySelector('#tabelaHistorico tbody');
                    tbody.innerHTML = '';
                    
                    data.movimentacoes.forEach(mov => {
                        const tr = document.createElement('tr');
                        const tipoClass = mov.tipo_movimentacao === 'entrada' ? 'text-success' : 
                                         mov.tipo_movimentacao === 'saida' ? 'text-danger' : 'text-warning';
                        const tipoIcon = mov.tipo_movimentacao === 'entrada' ? 'fa-arrow-up' : 
                                        mov.tipo_movimentacao === 'saida' ? 'fa-arrow-down' : 'fa-exchange-alt';
                        
                        tr.innerHTML = `
                            <td>${formatarData(mov.data_movimentacao)}</td>
                            <td>
                                <i class="fas ${tipoIcon} ${tipoClass} me-1"></i>
                                ${mov.tipo_movimentacao.charAt(0).toUpperCase() + mov.tipo_movimentacao.slice(1)}
                            </td>
                            <td>${mov.quantidade}</td>
                            <td>${mov.valor_total || '-'}</td>
                            <td>${mov.motivo || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalHistorico'));
                    modal.show();
                } else {
                    mostrarAlerta('Erro ao carregar histórico: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar histórico', 'danger');
            });
        }

        function exportarEstoque() {
            const categoria = document.getElementById('filtro_categoria').value;
            const status = document.getElementById('filtro_status').value;
            const estoque = document.getElementById('filtro_estoque').value;
            const busca = document.getElementById('filtro_busca').value;

            const params = new URLSearchParams({
                categoria: categoria,
                status: status,
                estoque: estoque,
                busca: busca
            });

            window.open(`api/estoque.php?action=exportar&${params.toString()}`, '_blank');
        }

        // Event listeners para formulários
        document.getElementById('formProduto').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('api/estoque.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'salvar_produto',
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Produto salvo com sucesso!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalProduto')).hide();
                    carregarProdutos();
                } else {
                    mostrarAlerta('Erro ao salvar produto: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao salvar produto', 'danger');
            });
        });

        document.getElementById('formMovimentacao').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('api/estoque.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'movimentar',
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Movimentação registrada com sucesso!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalMovimentacao')).hide();
                    carregarProdutos();
                } else {
                    mostrarAlerta('Erro ao registrar movimentação: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao registrar movimentação', 'danger');
            });
        });
    </script>
</body>
</html> 