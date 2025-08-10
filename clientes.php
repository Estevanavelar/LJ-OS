<?php
/**
 * Gestão de Clientes
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Verificar login
verificarLogin();

require_once 'includes/header.php';

$acao = $_GET['acao'] ?? 'listar';
$id_cliente = $_GET['id'] ?? null;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $dados = [
        'nome' => sanitizar($_POST['nome'] ?? ''),
        'tipo_pessoa' => sanitizar($_POST['tipo_pessoa'] ?? 'PF'),
        'cpf_cnpj' => sanitizar($_POST['cpf_cnpj'] ?? ''),
        'rg_ie' => sanitizar($_POST['rg_ie'] ?? ''),
        'telefone' => sanitizar($_POST['telefone'] ?? ''),
        'email' => sanitizar($_POST['email'] ?? ''),
        'endereco' => sanitizar($_POST['endereco'] ?? ''),
        'cep' => sanitizar($_POST['cep'] ?? ''),
        'cidade' => sanitizar($_POST['cidade'] ?? ''),
        'estado' => sanitizar($_POST['estado'] ?? ''),
        'data_nascimento' => sanitizar($_POST['data_nascimento'] ?? ''),
        'observacoes' => sanitizar($_POST['observacoes'] ?? ''),
        'programa_fidelidade' => isset($_POST['programa_fidelidade']) ? 1 : 0
    ];
    
    try {
        $db = getDB();
        
        // Validações
        if (empty($dados['nome'])) {
            throw new Exception('Nome é obrigatório');
        }
        
        if (empty($dados['cpf_cnpj'])) {
            throw new Exception('CPF/CNPJ é obrigatório');
        }
        
        if ($dados['tipo_pessoa'] === 'PF' && !validarCPF($dados['cpf_cnpj'])) {
            throw new Exception('CPF inválido');
        }
        
        if ($dados['tipo_pessoa'] === 'PJ' && !validarCNPJ($dados['cpf_cnpj'])) {
            throw new Exception('CNPJ inválido');
        }
        
        if (empty($dados['telefone'])) {
            throw new Exception('Telefone é obrigatório');
        }
        
        // Verificar se CPF/CNPJ já existe
        $sql_check = "SELECT id_cliente FROM clientes WHERE cpf_cnpj = ? AND id_cliente != ?";
        $stmt_check = $db->prepare($sql_check);
        $stmt_check->execute([$dados['cpf_cnpj'], $id_cliente ?? 0]);
        
        if ($stmt_check->fetch()) {
            throw new Exception('CPF/CNPJ já cadastrado');
        }
        
        if ($acao === 'editar' && $id_cliente) {
            // Atualizar cliente
            $sql = "UPDATE clientes SET 
                    nome = ?, tipo_pessoa = ?, cpf_cnpj = ?, rg_ie = ?, telefone = ?, 
                    email = ?, endereco = ?, cep = ?, cidade = ?, estado = ?, 
                    data_nascimento = ?, observacoes = ?, programa_fidelidade = ? 
                    WHERE id_cliente = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $dados['nome'], $dados['tipo_pessoa'], $dados['cpf_cnpj'], $dados['rg_ie'],
                $dados['telefone'], $dados['email'], $dados['endereco'], $dados['cep'],
                $dados['cidade'], $dados['estado'], $dados['data_nascimento'],
                $dados['observacoes'], $dados['programa_fidelidade'], $id_cliente
            ]);
            
            registrarLog('Cliente atualizado', 'clientes', $id_cliente);
            header('Location: clientes.php?sucesso=Cliente atualizado com sucesso!');
            exit();
            
        } else {
            // Inserir novo cliente
            $sql = "INSERT INTO clientes (nome, tipo_pessoa, cpf_cnpj, rg_ie, telefone, 
                    email, endereco, cep, cidade, estado, data_nascimento, observacoes, programa_fidelidade) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $dados['nome'], $dados['tipo_pessoa'], $dados['cpf_cnpj'], $dados['rg_ie'],
                $dados['telefone'], $dados['email'], $dados['endereco'], $dados['cep'],
                $dados['cidade'], $dados['estado'], $dados['data_nascimento'],
                $dados['observacoes'], $dados['programa_fidelidade']
            ]);
            
            $id_cliente = $db->lastInsertId();
            registrarLog('Cliente cadastrado', 'clientes', $id_cliente);
            header('Location: clientes.php?sucesso=Cliente cadastrado com sucesso!');
            exit();
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar cliente para edição
$cliente = null;
if ($acao === 'editar' && $id_cliente) {
    try {
        $db = getDB();
        $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_cliente]);
        $cliente = $stmt->fetch();
        
        if (!$cliente) {
            header('Location: clientes.php?erro=Cliente não encontrado');
            exit();
        }
    } catch (Exception $e) {
        header('Location: clientes.php?erro=Erro ao carregar cliente');
        exit();
    }
}

// Listar clientes
if ($acao === 'listar') {
    try {
        $db = getDB();
        
        // Filtros
        $busca = sanitizar($_GET['busca'] ?? '');
        $tipo_pessoa = sanitizar($_GET['tipo_pessoa'] ?? '');
        $status = sanitizar($_GET['status'] ?? 'ativo');
        
        $where_conditions = ["status = ?"];
        $params = [$status];
        
        if (!empty($busca)) {
            $where_conditions[] = "(nome LIKE ? OR cpf_cnpj LIKE ? OR telefone LIKE ?)";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
        }
        
        if (!empty($tipo_pessoa)) {
            $where_conditions[] = "tipo_pessoa = ?";
            $params[] = $tipo_pessoa;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT * FROM clientes WHERE $where_clause ORDER BY nome ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Erro ao listar clientes: " . $e->getMessage());
        $clientes = [];
    }
}
?>

<?php if ($acao === 'listar'): ?>
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        Gestão de Clientes
    </h1>
    
    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
            <i class="fas fa-filter card-icon"></i>
        </div>
        <form method="GET" class="d-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <input type="hidden" name="acao" value="listar">
            
            <div class="form-group">
                <label class="form-label">Buscar</label>
                <input type="text" name="busca" class="form-control search-input" 
                       placeholder="Nome, CPF/CNPJ, telefone..." 
                       value="<?php echo htmlspecialchars($busca ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Tipo de Pessoa</label>
                <select name="tipo_pessoa" class="form-control">
                    <option value="">Todos</option>
                    <option value="PF" <?php echo ($tipo_pessoa ?? '') === 'PF' ? 'selected' : ''; ?>>Pessoa Física</option>
                    <option value="PJ" <?php echo ($tipo_pessoa ?? '') === 'PJ' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="ativo" <?php echo ($status ?? '') === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inativo" <?php echo ($status ?? '') === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            
            <div class="form-group d-flex align-center" style="align-self: end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
                <a href="clientes.php" class="btn btn-outline ml-2">
                    <i class="fas fa-times"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Lista de Clientes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Clientes</h3>
            <div>
                <a href="clientes.php?acao=novo" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Novo Cliente
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>CPF/CNPJ</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Cidade/UF</th>
                        <th>Fidelidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Nenhum cliente encontrado</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cliente['nome']); ?></strong>
                                    <?php if ($cliente['programa_fidelidade']): ?>
                                        <br><small class="badge badge-success">Fidelidade</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $cliente['tipo_pessoa'] === 'PF' ? 'badge-info' : 'badge-warning'; ?>">
                                        <?php echo $cliente['tipo_pessoa']; ?>
                                    </span>
                                </td>
                                <td><?php echo formatarCpfCnpj($cliente['cpf_cnpj']); ?></td>
                                <td><?php echo formatarTelefone($cliente['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['email'] ?: '-'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($cliente['cidade'] ?: '-'); ?>
                                    <?php if ($cliente['estado']): ?>
                                        / <?php echo htmlspecialchars($cliente['estado']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cliente['programa_fidelidade']): ?>
                                        <span class="badge badge-success"><?php echo $cliente['pontos_fidelidade']; ?> pts</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Não participa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="clientes.php?acao=visualizar&id=<?php echo $cliente['id_cliente']; ?>" 
                                           class="btn btn-sm btn-outline" data-tooltip="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="clientes.php?acao=editar&id=<?php echo $cliente['id_cliente']; ?>" 
                                           class="btn btn-sm btn-accent" data-tooltip="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="veiculos.php?cliente_id=<?php echo $cliente['id_cliente']; ?>" 
                                           class="btn btn-sm btn-secondary" data-tooltip="Veículos">
                                            <i class="fas fa-car"></i>
                                        </a>
                                        <button onclick="toggleStatus(<?php echo $cliente['id_cliente']; ?>, '<?php echo $cliente['status']; ?>')" 
                                                class="btn btn-sm <?php echo $cliente['status'] === 'ativo' ? 'btn-danger' : 'btn-success'; ?>" 
                                                data-tooltip="<?php echo $cliente['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>">
                                            <i class="fas fa-<?php echo $cliente['status'] === 'ativo' ? 'times' : 'check'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($acao === 'novo' || $acao === 'editar'): ?>
    <h1 class="page-title">
        <i class="fas fa-<?php echo $acao === 'novo' ? 'plus' : 'edit'; ?>"></i>
        <?php echo $acao === 'novo' ? 'Novo Cliente' : 'Editar Cliente'; ?>
    </h1>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dados do Cliente</h3>
            <i class="fas fa-user card-icon"></i>
        </div>
        
        <form method="POST" data-validate>
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nome/Razão Social *</label>
                    <input type="text" name="nome" class="form-control" required 
                           value="<?php echo htmlspecialchars($cliente['nome'] ?? ''); ?>"
                           placeholder="Digite o nome completo ou razão social">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipo de Pessoa *</label>
                    <select name="tipo_pessoa" class="form-control" required onchange="toggleDocumentFields()">
                        <option value="PF" <?php echo ($cliente['tipo_pessoa'] ?? 'PF') === 'PF' ? 'selected' : ''; ?>>Pessoa Física</option>
                        <option value="PJ" <?php echo ($cliente['tipo_pessoa'] ?? '') === 'PJ' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" id="document-label">CPF *</label>
                    <div class="input-group">
                        <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control document-input" required 
                               value="<?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? ''); ?>"
                               placeholder="Digite o CPF">
                        <button type="button" class="btn btn-outline" onclick="consultarCNPJ()" id="btn-consultar-cnpj" style="display: none;">
                            <i class="fas fa-search"></i>
                            Consultar CNPJ
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" id="rg-ie-label">RG</label>
                    <input type="text" name="rg_ie" class="form-control" 
                           value="<?php echo htmlspecialchars($cliente['rg_ie'] ?? ''); ?>"
                           placeholder="Digite o RG">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Telefone *</label>
                    <input type="text" name="telefone" class="form-control phone-input" required 
                           value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>"
                           placeholder="Digite o telefone">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>"
                           placeholder="Digite o email">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CEP</label>
                    <div class="input-group">
                        <input type="text" name="cep" id="cep" class="form-control" 
                               value="<?php echo htmlspecialchars($cliente['cep'] ?? ''); ?>"
                               placeholder="Digite o CEP" maxlength="9">
                        <button type="button" class="btn btn-outline" onclick="consultarCEP()">
                            <i class="fas fa-search"></i>
                            Buscar CEP
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Data de Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" 
                           value="<?php echo htmlspecialchars($cliente['data_nascimento'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" 
                       value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>"
                       placeholder="Digite o endereço completo">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" 
                           value="<?php echo htmlspecialchars($cliente['cidade'] ?? ''); ?>"
                           placeholder="Digite a cidade">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Selecione...</option>
                        <option value="AC" <?php echo ($cliente['estado'] ?? '') === 'AC' ? 'selected' : ''; ?>>Acre</option>
                        <option value="AL" <?php echo ($cliente['estado'] ?? '') === 'AL' ? 'selected' : ''; ?>>Alagoas</option>
                        <option value="AP" <?php echo ($cliente['estado'] ?? '') === 'AP' ? 'selected' : ''; ?>>Amapá</option>
                        <option value="AM" <?php echo ($cliente['estado'] ?? '') === 'AM' ? 'selected' : ''; ?>>Amazonas</option>
                        <option value="BA" <?php echo ($cliente['estado'] ?? '') === 'BA' ? 'selected' : ''; ?>>Bahia</option>
                        <option value="CE" <?php echo ($cliente['estado'] ?? '') === 'CE' ? 'selected' : ''; ?>>Ceará</option>
                        <option value="DF" <?php echo ($cliente['estado'] ?? '') === 'DF' ? 'selected' : ''; ?>>Distrito Federal</option>
                        <option value="ES" <?php echo ($cliente['estado'] ?? '') === 'ES' ? 'selected' : ''; ?>>Espírito Santo</option>
                        <option value="GO" <?php echo ($cliente['estado'] ?? '') === 'GO' ? 'selected' : ''; ?>>Goiás</option>
                        <option value="MA" <?php echo ($cliente['estado'] ?? '') === 'MA' ? 'selected' : ''; ?>>Maranhão</option>
                        <option value="MT" <?php echo ($cliente['estado'] ?? '') === 'MT' ? 'selected' : ''; ?>>Mato Grosso</option>
                        <option value="MS" <?php echo ($cliente['estado'] ?? '') === 'MS' ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                        <option value="MG" <?php echo ($cliente['estado'] ?? '') === 'MG' ? 'selected' : ''; ?>>Minas Gerais</option>
                        <option value="PA" <?php echo ($cliente['estado'] ?? '') === 'PA' ? 'selected' : ''; ?>>Pará</option>
                        <option value="PB" <?php echo ($cliente['estado'] ?? '') === 'PB' ? 'selected' : ''; ?>>Paraíba</option>
                        <option value="PR" <?php echo ($cliente['estado'] ?? '') === 'PR' ? 'selected' : ''; ?>>Paraná</option>
                        <option value="PE" <?php echo ($cliente['estado'] ?? '') === 'PE' ? 'selected' : ''; ?>>Pernambuco</option>
                        <option value="PI" <?php echo ($cliente['estado'] ?? '') === 'PI' ? 'selected' : ''; ?>>Piauí</option>
                        <option value="RJ" <?php echo ($cliente['estado'] ?? '') === 'RJ' ? 'selected' : ''; ?>>Rio de Janeiro</option>
                        <option value="RN" <?php echo ($cliente['estado'] ?? '') === 'RN' ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                        <option value="RS" <?php echo ($cliente['estado'] ?? '') === 'RS' ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                        <option value="RO" <?php echo ($cliente['estado'] ?? '') === 'RO' ? 'selected' : ''; ?>>Rondônia</option>
                        <option value="RR" <?php echo ($cliente['estado'] ?? '') === 'RR' ? 'selected' : ''; ?>>Roraima</option>
                        <option value="SC" <?php echo ($cliente['estado'] ?? '') === 'SC' ? 'selected' : ''; ?>>Santa Catarina</option>
                        <option value="SP" <?php echo ($cliente['estado'] ?? '') === 'SP' ? 'selected' : ''; ?>>São Paulo</option>
                        <option value="SE" <?php echo ($cliente['estado'] ?? '') === 'SE' ? 'selected' : ''; ?>>Sergipe</option>
                        <option value="TO" <?php echo ($cliente['estado'] ?? '') === 'TO' ? 'selected' : ''; ?>>Tocantins</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3" 
                          placeholder="Observações adicionais"><?php echo htmlspecialchars($cliente['observacoes'] ?? ''); ?></textarea>
                </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="programa_fidelidade" value="1" 
                           <?php echo ($cliente['programa_fidelidade'] ?? 0) ? 'checked' : ''; ?>>
                    Participar do programa de fidelidade
                </label>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo $acao === 'novo' ? 'Cadastrar' : 'Atualizar'; ?>
                </button>
                <a href="clientes.php" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>

<?php elseif ($acao === 'visualizar' && $id_cliente): ?>
    <h1 class="page-title">
        <i class="fas fa-eye"></i>
        Visualizar Cliente
    </h1>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dados do Cliente</h3>
            <div>
                <a href="clientes.php?acao=editar&id=<?php echo $id_cliente; ?>" class="btn btn-accent">
                    <i class="fas fa-edit"></i>
                    Editar
                </a>
                <a href="clientes.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Dados do cliente serão carregados via AJAX -->
            <div id="cliente-detalhes">
                <div class="text-center">
                    <div class="spinner"></div>
                    <p>Carregando dados do cliente...</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para detalhes do cliente -->
<div class="modal" id="modalCliente">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Detalhes do Cliente</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalClienteBody">
            <!-- Conteúdo será carregado via AJAX -->
        </div>
    </div>
</div>

<script>
// Função para alternar campos de documento baseado no tipo de pessoa
function toggleDocumentFields() {
    const tipoPessoa = document.querySelector('select[name="tipo_pessoa"]').value;
    const documentLabel = document.getElementById('document-label');
    const rgIeLabel = document.getElementById('rg-ie-label');
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const btnConsultarCNPJ = document.getElementById('btn-consultar-cnpj');
    
    if (tipoPessoa === 'PF') {
        documentLabel.textContent = 'CPF *';
        rgIeLabel.textContent = 'RG';
        cpfCnpjInput.placeholder = 'Digite o CPF';
        btnConsultarCNPJ.style.display = 'none';
    } else {
        documentLabel.textContent = 'CNPJ *';
        rgIeLabel.textContent = 'Inscrição Estadual';
        cpfCnpjInput.placeholder = 'Digite o CNPJ';
        btnConsultarCNPJ.style.display = 'inline-block';
    }
}

// Função para consultar CEP
async function consultarCEP() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        LavaJato.showAlert('CEP deve ter 8 dígitos', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (data.erro) {
            LavaJato.showAlert('CEP não encontrado', 'warning');
            return;
        }
        
        // Preencher os campos com os dados do CEP
        document.getElementById('endereco').value = data.logradouro || '';
        document.getElementById('cidade').value = data.localidade || '';
        document.getElementById('estado').value = data.uf || '';
        
        LavaJato.showAlert('Endereço preenchido automaticamente!', 'success');
        
    } catch (error) {
        console.error('Erro ao consultar CEP:', error);
        LavaJato.showAlert('Erro ao consultar CEP. Tente novamente.', 'danger');
    }
}

// Função para consultar CNPJ
async function consultarCNPJ() {
    const cnpj = document.getElementById('cpf_cnpj').value.replace(/\D/g, '');
    
    if (cnpj.length !== 14) {
        LavaJato.showAlert('CNPJ deve ter 14 dígitos', 'warning');
        return;
    }
    
    try {
        // Usando a API pública do CNPJ
        const response = await fetch(`https://publica.cnpj.ws/cnpj/${cnpj}`);
        const data = await response.json();
        
        if (data.estabelecimento) {
            const estabelecimento = data.estabelecimento;
            const empresa = data.empresa;
            
            // Preencher os campos com os dados do CNPJ
            document.querySelector('input[name="nome"]').value = estabelecimento.razao_social || '';
            document.querySelector('input[name="rg_ie"]').value = estabelecimento.inscricao_estadual || '';
            document.querySelector('input[name="endereco"]').value = 
                `${estabelecimento.logradouro || ''}, ${estabelecimento.numero || ''}`;
            document.querySelector('input[name="cidade"]').value = estabelecimento.cidade?.nome || '';
            document.querySelector('select[name="estado"]').value = estabelecimento.estado?.sigla || '';
            document.querySelector('input[name="cep"]').value = estabelecimento.cep || '';
            
            LavaJato.showAlert('Dados do CNPJ preenchidos automaticamente!', 'success');
        } else {
            LavaJato.showAlert('CNPJ não encontrado', 'warning');
        }
        
    } catch (error) {
        console.error('Erro ao consultar CNPJ:', error);
        LavaJato.showAlert('Erro ao consultar CNPJ. Tente novamente.', 'danger');
    }
}

// Função para alternar status do cliente
function toggleStatus(idCliente, statusAtual) {
    const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
    const csrf = <?php echo json_encode(csrf_token()); ?>;

    fetch('api/clientes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify({
            acao: 'toggle_status',
            id_cliente: idCliente,
            status: novoStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            location.reload();
        } else {
            LavaJato.showAlert(data.erro || 'Erro ao alterar status', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        LavaJato.showAlert('Erro ao alterar status', 'danger');
    });
}

// Inicializar campos de documento
document.addEventListener('DOMContentLoaded', function() {
    toggleDocumentFields();
    
    // Formatar CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 5) {
                value = value.slice(0, 5) + '-' + value.slice(5);
            }
            this.value = value.slice(0, 9);
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 