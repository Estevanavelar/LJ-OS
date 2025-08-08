<?php
/**
 * LJ-OS Sistema para Lava Jato
 * Gestão de Usuários
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

// Buscar estatísticas de usuários
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_usuarios,
        COUNT(CASE WHEN status = 'ativo' THEN 1 END) as usuarios_ativos,
        COUNT(CASE WHEN ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as usuarios_ativos_semana
    FROM usuarios
");
$stmt->execute();
$stats_usuarios = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar perfis para filtros
$perfis = ['admin', 'gerente', 'operador', 'atendente', 'financeiro'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - <?php echo $nome_empresa; ?></title>
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
                        <i class="fas fa-users-cog text-primary me-2"></i>
                        Gestão de Usuários
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="abrirModalUsuario()">
                                <i class="fas fa-user-plus me-1"></i>Novo Usuário
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="abrirModalPerfil()">
                                <i class="fas fa-user-shield me-1"></i>Perfis de Acesso
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="abrirModalLogs()">
                                <i class="fas fa-history me-1"></i>Logs de Atividade
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
                                            Total de Usuários
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_usuarios['total_usuarios']); ?>
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
                                            Usuários Ativos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_usuarios['usuarios_ativos']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-success"></i>
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
                                            Ativos na Semana
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats_usuarios['usuarios_ativos_semana']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-clock fa-2x text-info"></i>
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
                                            Taxa de Atividade
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $taxa = $stats_usuarios['total_usuarios'] > 0 ? 
                                                   ($stats_usuarios['usuarios_ativos'] / $stats_usuarios['total_usuarios']) * 100 : 0;
                                            echo number_format($taxa, 1) . '%';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percentage fa-2x text-warning"></i>
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
                                <label for="filtro_perfil" class="form-label">Perfil</label>
                                <select class="form-select" id="filtro_perfil">
                                    <option value="">Todos os perfis</option>
                                    <?php foreach ($perfis as $perfil): ?>
                                    <option value="<?php echo $perfil; ?>">
                                        <?php echo ucfirst($perfil); ?>
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
                                    <option value="bloqueado">Bloqueado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_ultimo_acesso" class="form-label">Último Acesso</label>
                                <select class="form-select" id="filtro_ultimo_acesso">
                                    <option value="">Todos</option>
                                    <option value="hoje">Hoje</option>
                                    <option value="semana">Última Semana</option>
                                    <option value="mes">Último Mês</option>
                                    <option value="nunca">Nunca Acessou</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="filtro_busca" placeholder="Nome, email...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Usuários -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabelaUsuarios">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Perfil</th>
                                        <th>Status</th>
                                        <th>Último Acesso</th>
                                        <th>Data Cadastro</th>
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

    <!-- Modal Usuário -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioTitle">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formUsuario">
                    <div class="modal-body">
                        <input type="hidden" id="usuario_id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="usuario_nome" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="usuario_nome" name="nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="usuario_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_usuario" class="form-label">Nome de Usuário *</label>
                                <input type="text" class="form-control" id="usuario_usuario" name="usuario" required>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_perfil" class="form-label">Perfil *</label>
                                <select class="form-select" id="usuario_perfil" name="perfil" required>
                                    <option value="">Selecione um perfil</option>
                                    <?php foreach ($perfis as $perfil): ?>
                                    <option value="<?php echo $perfil; ?>">
                                        <?php echo ucfirst($perfil); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="usuario_senha" name="senha" 
                                       minlength="6" placeholder="Deixe em branco para manter a atual">
                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="usuario_confirmar_senha" name="confirmar_senha">
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="usuario_telefone" name="telefone">
                            </div>
                            <div class="col-md-6">
                                <label for="usuario_status" class="form-label">Status</label>
                                <select class="form-select" id="usuario_status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                    <option value="bloqueado">Bloqueado</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="usuario_observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="usuario_observacoes" name="observacoes" rows="3"></textarea>
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

    <!-- Modal Perfis de Acesso -->
    <div class="modal fade" id="modalPerfil" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Perfis de Acesso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Administrador</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Acesso total ao sistema</li>
                                <li><i class="fas fa-check text-success me-2"></i>Gestão de usuários</li>
                                <li><i class="fas fa-check text-success me-2"></i>Configurações do sistema</li>
                                <li><i class="fas fa-check text-success me-2"></i>Relatórios completos</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Gerente</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Gestão de clientes e veículos</li>
                                <li><i class="fas fa-check text-success me-2"></i>Ordens de serviço</li>
                                <li><i class="fas fa-check text-success me-2"></i>Agendamentos</li>
                                <li><i class="fas fa-check text-success me-2"></i>Relatórios básicos</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Operador</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Criar e editar OS</li>
                                <li><i class="fas fa-check text-success me-2"></i>Atualizar status</li>
                                <li><i class="fas fa-check text-success me-2"></i>Visualizar clientes</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Sem acesso financeiro</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Atendente</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Cadastrar clientes</li>
                                <li><i class="fas fa-check text-success me-2"></i>Agendamentos</li>
                                <li><i class="fas fa-check text-success me-2"></i>Consulta básica</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Acesso limitado</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Logs de Atividade -->
    <div class="modal fade" id="modalLogs" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logs de Atividade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="log_usuario" class="form-label">Usuário</label>
                            <select class="form-select" id="log_usuario">
                                <option value="">Todos os usuários</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="log_acao" class="form-label">Ação</label>
                            <select class="form-select" id="log_acao">
                                <option value="">Todas as ações</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="create">Criação</option>
                                <option value="update">Atualização</option>
                                <option value="delete">Exclusão</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="log_data_inicio" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="log_data_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="log_data_fim" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="log_data_fim">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaLogs">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Módulo</th>
                                    <th>Detalhes</th>
                                    <th>IP</th>
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
    
    <script>
        let paginaAtual = 1;
        const itensPorPagina = 20;

        // Carregar dados ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            carregarUsuarios();
            carregarUsuariosLogs();
            
            // Event listeners para filtros
            document.getElementById('filtro_perfil').addEventListener('change', carregarUsuarios);
            document.getElementById('filtro_status').addEventListener('change', carregarUsuarios);
            document.getElementById('filtro_ultimo_acesso').addEventListener('change', carregarUsuarios);
            document.getElementById('filtro_busca').addEventListener('input', debounce(carregarUsuarios, 300));
        });

        function carregarUsuarios() {
            const perfil = document.getElementById('filtro_perfil').value;
            const status = document.getElementById('filtro_status').value;
            const ultimoAcesso = document.getElementById('filtro_ultimo_acesso').value;
            const busca = document.getElementById('filtro_busca').value;

            fetch('api/usuarios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar',
                    pagina: paginaAtual,
                    perfil: perfil,
                    status: status,
                    ultimo_acesso: ultimoAcesso,
                    busca: busca
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderizarTabela(data.usuarios);
                    renderizarPaginacao(data.total, data.pagina);
                } else {
                    mostrarAlerta('Erro ao carregar usuários: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar usuários', 'danger');
            });
        }

        function renderizarTabela(usuarios) {
            const tbody = document.querySelector('#tabelaUsuarios tbody');
            tbody.innerHTML = '';

            usuarios.forEach(usuario => {
                const tr = document.createElement('tr');
                const statusClass = usuario.status === 'ativo' ? 'success' : 
                                   usuario.status === 'bloqueado' ? 'danger' : 'secondary';
                const perfilClass = usuario.perfil === 'admin' ? 'danger' : 
                                   usuario.perfil === 'gerente' ? 'warning' : 'info';
                
                tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <strong>${usuario.nome}</strong>
                                ${usuario.observacoes ? `<br><small class="text-muted">${usuario.observacoes}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>${usuario.email}</td>
                    <td>
                        <span class="badge bg-${perfilClass}">
                            ${usuario.perfil.charAt(0).toUpperCase() + usuario.perfil.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            ${usuario.status.charAt(0).toUpperCase() + usuario.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        ${usuario.ultimo_acesso ? 
                          `<small>${formatarData(usuario.ultimo_acesso)}</small>` : 
                          '<span class="text-muted">Nunca acessou</span>'}
                    </td>
                    <td>
                        <small>${formatarData(usuario.data_cadastro)}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="editarUsuario(${usuario.id_usuario})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="verLogsUsuario(${usuario.id_usuario})">
                                <i class="fas fa-history"></i>
                            </button>
                            <button type="button" class="btn btn-outline-${usuario.status === 'ativo' ? 'warning' : 'success'}" 
                                    onclick="toggleStatusUsuario(${usuario.id_usuario}, '${usuario.status}')">
                                <i class="fas fa-${usuario.status === 'ativo' ? 'pause' : 'play'}"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="excluirUsuario(${usuario.id_usuario})">
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
            carregarUsuarios();
        }

        function abrirModalUsuario(id = null) {
            const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
            const form = document.getElementById('formUsuario');
            const title = document.getElementById('modalUsuarioTitle');
            
            form.reset();
            document.getElementById('usuario_id').value = '';
            document.getElementById('usuario_senha').required = false;
            document.getElementById('usuario_confirmar_senha').required = false;
            
            if (id) {
                title.textContent = 'Editar Usuário';
                carregarUsuario(id);
            } else {
                title.textContent = 'Novo Usuário';
                document.getElementById('usuario_senha').required = true;
                document.getElementById('usuario_confirmar_senha').required = true;
            }
            
            modal.show();
        }

        function carregarUsuario(id) {
            fetch('api/usuarios.php', {
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
                    const usuario = data.usuario;
                    document.getElementById('usuario_id').value = usuario.id_usuario;
                    document.getElementById('usuario_nome').value = usuario.nome;
                    document.getElementById('usuario_email').value = usuario.email;
                    document.getElementById('usuario_usuario').value = usuario.usuario;
                    document.getElementById('usuario_perfil').value = usuario.perfil;
                    document.getElementById('usuario_telefone').value = usuario.telefone || '';
                    document.getElementById('usuario_status').value = usuario.status;
                    document.getElementById('usuario_observacoes').value = usuario.observacoes || '';
                } else {
                    mostrarAlerta('Erro ao carregar usuário: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar usuário', 'danger');
            });
        }

        function editarUsuario(id) {
            abrirModalUsuario(id);
        }

        function toggleStatusUsuario(id, statusAtual) {
            const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
            const confirmacao = confirm(`Deseja ${novoStatus === 'ativo' ? 'ativar' : 'desativar'} este usuário?`);
            
            if (confirmacao) {
                fetch('api/usuarios.php', {
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
                        carregarUsuarios();
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

        function excluirUsuario(id) {
            const confirmacao = confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');
            
            if (confirmacao) {
                fetch('api/usuarios.php', {
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
                        mostrarAlerta('Usuário excluído com sucesso!', 'success');
                        carregarUsuarios();
                    } else {
                        mostrarAlerta('Erro ao excluir usuário: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarAlerta('Erro ao excluir usuário', 'danger');
                });
            }
        }

        function abrirModalPerfil() {
            const modal = new bootstrap.Modal(document.getElementById('modalPerfil'));
            modal.show();
        }

        function abrirModalLogs() {
            const modal = new bootstrap.Modal(document.getElementById('modalLogs'));
            carregarLogs();
            modal.show();
        }

        function carregarUsuariosLogs() {
            fetch('api/usuarios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar_usuarios_simples'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('log_usuario');
                    select.innerHTML = '<option value="">Todos os usuários</option>';
                    
                    data.usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id_usuario;
                        option.textContent = usuario.nome;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }

        function carregarLogs() {
            const usuario = document.getElementById('log_usuario').value;
            const acao = document.getElementById('log_acao').value;
            const dataInicio = document.getElementById('log_data_inicio').value;
            const dataFim = document.getElementById('log_data_fim').value;

            fetch('api/usuarios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'listar_logs',
                    usuario: usuario,
                    acao: acao,
                    data_inicio: dataInicio,
                    data_fim: dataFim
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderizarTabelaLogs(data.logs);
                } else {
                    mostrarAlerta('Erro ao carregar logs: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao carregar logs', 'danger');
            });
        }

        function renderizarTabelaLogs(logs) {
            const tbody = document.querySelector('#tabelaLogs tbody');
            tbody.innerHTML = '';

            logs.forEach(log => {
                const tr = document.createElement('tr');
                const acaoClass = log.acao === 'login' ? 'success' : 
                                 log.acao === 'logout' ? 'info' : 
                                 log.acao === 'delete' ? 'danger' : 'primary';
                
                tr.innerHTML = `
                    <td><small>${formatarData(log.data_hora)}</small></td>
                    <td>${log.nome_usuario}</td>
                    <td>
                        <span class="badge bg-${acaoClass}">
                            ${log.acao.charAt(0).toUpperCase() + log.acao.slice(1)}
                        </span>
                    </td>
                    <td>${log.modulo || '-'}</td>
                    <td>${log.detalhes || '-'}</td>
                    <td><small>${log.ip}</small></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function verLogsUsuario(id) {
            document.getElementById('log_usuario').value = id;
            abrirModalLogs();
        }

        // Event listeners para filtros de logs
        document.getElementById('log_usuario').addEventListener('change', carregarLogs);
        document.getElementById('log_acao').addEventListener('change', carregarLogs);
        document.getElementById('log_data_inicio').addEventListener('change', carregarLogs);
        document.getElementById('log_data_fim').addEventListener('change', carregarLogs);

        // Event listener para formulário de usuário
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const senha = document.getElementById('usuario_senha').value;
            const confirmarSenha = document.getElementById('usuario_confirmar_senha').value;
            
            if (senha && senha !== confirmarSenha) {
                mostrarAlerta('As senhas não coincidem!', 'danger');
                return;
            }
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('api/usuarios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'salvar_usuario',
                    ...data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Usuário salvo com sucesso!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
                    carregarUsuarios();
                } else {
                    mostrarAlerta('Erro ao salvar usuário: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarAlerta('Erro ao salvar usuário', 'danger');
            });
        });
    </script>
</body>
</html> 