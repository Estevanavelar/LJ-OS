<?php
/**
 * Gestão de Serviços
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = getDB();

if (!verificarPermissao('servicos')) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/header.php';

// Processar ações
$acao = $_GET['acao'] ?? '';
$id_servico = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    try {
        if ($acao === 'adicionar') {
            $stmt = $pdo->prepare("
                INSERT INTO servicos (id_categoria, nome_servico, descricao, preco, duracao_estimada, tipo_veiculo, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['id_categoria'],
                $_POST['nome_servico'],
                $_POST['descricao'],
                $_POST['preco'],
                $_POST['duracao_estimada'],
                $_POST['tipo_veiculo'],
                'ativo'
            ]);
            $sucesso = "Serviço adicionado com sucesso!";
            
        } elseif ($acao === 'editar' && $id_servico) {
            $stmt = $pdo->prepare("
                UPDATE servicos 
                SET id_categoria = ?, nome_servico = ?, descricao = ?, preco = ?, duracao_estimada = ?, tipo_veiculo = ?
                WHERE id_servico = ?
            ");
            $stmt->execute([
                $_POST['id_categoria'],
                $_POST['nome_servico'],
                $_POST['descricao'],
                $_POST['preco'],
                $_POST['duracao_estimada'],
                $_POST['tipo_veiculo'],
                $id_servico
            ]);
            $sucesso = "Serviço atualizado com sucesso!";
            
        } elseif ($acao === 'excluir' && $id_servico) {
            $stmt = $pdo->prepare("UPDATE servicos SET status = 'inativo' WHERE id_servico = ?");
            $stmt->execute([$id_servico]);
            $sucesso = "Serviço removido com sucesso!";
        }
    } catch (Exception $e) {
        $erro = "Erro: " . $e->getMessage();
    }
}

// Buscar categorias
$categorias = [];
try {
    $stmt = $pdo->query("SELECT id_categoria, nome_categoria FROM categorias_servicos WHERE status = 'ativo' ORDER BY nome_categoria");
    $categorias = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = "Erro ao carregar categorias: " . $e->getMessage();
}

// Buscar serviços
$servicos = [];
try {
    $stmt = $pdo->query("
        SELECT s.*, c.nome_categoria 
        FROM servicos s 
        LEFT JOIN categorias_servicos c ON s.id_categoria = c.id_categoria 
        WHERE s.status = 'ativo' 
        ORDER BY s.nome_servico
    ");
    $servicos = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = "Erro ao carregar serviços: " . $e->getMessage();
}

// Buscar serviço para edição
$servico_editar = null;
if ($acao === 'editar' && $id_servico) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id_servico = ?");
        $stmt->execute([$id_servico]);
        $servico_editar = $stmt->fetch();
    } catch (Exception $e) {
        $erro = "Erro ao carregar serviço: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-tools"></i>
                    Gestão de Serviços
                </h1>
                <p class="page-description">
                    Gerencie os serviços oferecidos pela empresa
                </p>
            </div>

            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($sucesso); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger fade-in">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Adição/Edição -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <?php echo $acao === 'editar' ? 'Editar Serviço' : 'Adicionar Novo Serviço'; ?>
                    </h3>
                    <i class="fas fa-plus card-icon"></i>
                </div>
                <div class="card-body">
                    <form method="POST" action="?acao=<?php echo $acao === 'editar' ? 'editar&id=' . $id_servico : 'adicionar'; ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome_servico">Nome do Serviço *</label>
                                    <input type="text" class="form-control" id="nome_servico" name="nome_servico" 
                                           value="<?php echo htmlspecialchars($servico_editar['nome_servico'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="id_categoria">Categoria</label>
                                    <select class="form-control" id="id_categoria" name="id_categoria">
                                        <option value="">Selecione uma categoria</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo $categoria['id_categoria']; ?>" 
                                                    <?php echo ($servico_editar['id_categoria'] ?? '') == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="preco">Preço (R$) *</label>
                                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" 
                                           value="<?php echo htmlspecialchars($servico_editar['preco'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duracao_estimada">Duração Estimada (minutos) *</label>
                                    <input type="number" class="form-control" id="duracao_estimada" name="duracao_estimada" 
                                           value="<?php echo htmlspecialchars($servico_editar['duracao_estimada'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="tipo_veiculo">Tipo de Veículo</label>
                                    <select class="form-control" id="tipo_veiculo" name="tipo_veiculo">
                                        <option value="todos" <?php echo ($servico_editar['tipo_veiculo'] ?? '') == 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="carro" <?php echo ($servico_editar['tipo_veiculo'] ?? '') == 'carro' ? 'selected' : ''; ?>>Carro</option>
                                        <option value="moto" <?php echo ($servico_editar['tipo_veiculo'] ?? '') == 'moto' ? 'selected' : ''; ?>>Moto</option>
                                        <option value="caminhao" <?php echo ($servico_editar['tipo_veiculo'] ?? '') == 'caminhao' ? 'selected' : ''; ?>>Caminhão</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="descricao">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="4"><?php echo htmlspecialchars($servico_editar['descricao'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $acao === 'editar' ? 'Atualizar' : 'Adicionar'; ?>
                            </button>
                            <?php if ($acao === 'editar'): ?>
                                <a href="servicos.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Serviços -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Serviços Cadastrados</h3>
                    <i class="fas fa-list card-icon"></i>
                </div>
                <div class="card-body">
                    <?php if (empty($servicos)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum serviço cadastrado ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome do Serviço</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Duração</th>
                                        <th>Tipo de Veículo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicos as $servico): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($servico['nome_servico']); ?></strong>
                                                <?php if ($servico['descricao']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($servico['descricao']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($servico['nome_categoria'] ?? 'Sem categoria'); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $servico['duracao_estimada']; ?> min
                                            </td>
                                            <td>
                                                <?php
                                                $tipo_labels = [
                                                    'todos' => 'Todos',
                                                    'carro' => 'Carro',
                                                    'moto' => 'Moto',
                                                    'caminhao' => 'Caminhão'
                                                ];
                                                echo $tipo_labels[$servico['tipo_veiculo']] ?? 'Todos';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?acao=editar&id=<?php echo $servico['id_servico']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?acao=excluir&id=<?php echo $servico['id_servico']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" title="Remover"
                                                       onclick="return confirm('Tem certeza que deseja remover este serviço?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para preço
document.getElementById('preco').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = (parseFloat(value) / 100).toFixed(2);
    this.value = value;
});

// Formatação automática do preço
document.getElementById('preco').addEventListener('blur', function() {
    if (this.value) {
        this.value = parseFloat(this.value).toFixed(2);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 