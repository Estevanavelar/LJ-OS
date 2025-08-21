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
                INSERT INTO servicos (categoria, nome, descricao, preco, tempo_estimado) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['categoria'] ?? 'geral',
                $_POST['nome'],
                $_POST['descricao'],
                $_POST['preco'],
                $_POST['tempo_estimado']
            ]);
            $sucesso = "Serviço adicionado com sucesso!";
            
        } elseif ($acao === 'editar' && $id_servico) {
            $stmt = $pdo->prepare("
                UPDATE servicos 
                SET categoria = ?, nome = ?, descricao = ?, preco = ?, tempo_estimado = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['categoria'] ?? 'geral',
                $_POST['nome'],
                $_POST['descricao'],
                $_POST['preco'],
                $_POST['tempo_estimado'],
                $id_servico
            ]);
            $sucesso = "Serviço atualizado com sucesso!";
            
        } elseif ($acao === 'excluir' && $id_servico) {
            $stmt = $pdo->prepare("UPDATE servicos SET ativo = 0 WHERE id = ?");
            $stmt->execute([$id_servico]);
            $sucesso = "Serviço removido com sucesso!";
        }
    } catch (Exception $e) {
        $erro = "Erro: " . $e->getMessage();
    }
}

// Buscar serviços
$servicos = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM servicos 
        WHERE ativo = 1 
        ORDER BY nome
    ");
    $servicos = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = "Erro ao carregar serviços: " . $e->getMessage();
}

// Buscar serviço para edição
$servico_editar = null;
if ($acao === 'editar' && $id_servico) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
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
                                    <label for="nome">Nome do Serviço *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($servico_editar['nome'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="categoria">Categoria</label>
                                    <select class="form-control" id="categoria" name="categoria">
                                        <option value="geral" <?php echo ($servico_editar['categoria'] ?? 'geral') == 'geral' ? 'selected' : ''; ?>>Geral</option>
                                        <option value="lavagem" <?php echo ($servico_editar['categoria'] ?? '') == 'lavagem' ? 'selected' : ''; ?>>Lavagem</option>
                                        <option value="enceramento" <?php echo ($servico_editar['categoria'] ?? '') == 'enceramento' ? 'selected' : ''; ?>>Enceramento</option>
                                        <option value="higienizacao" <?php echo ($servico_editar['categoria'] ?? '') == 'higienizacao' ? 'selected' : ''; ?>>Higienização</option>
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
                                    <label for="tempo_estimado">Tempo Estimado (minutos) *</label>
                                    <input type="number" class="form-control" id="tempo_estimado" name="tempo_estimado" 
                                           value="<?php echo htmlspecialchars($servico_editar['tempo_estimado'] ?? '30'); ?>" required>
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
                                        <th>Tempo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicos as $servico): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($servico['nome']); ?></strong>
                                                <?php if ($servico['descricao']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($servico['descricao']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo ucfirst(htmlspecialchars($servico['categoria'] ?? 'Geral')); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $servico['tempo_estimado']; ?> min
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?acao=editar&id=<?php echo $servico['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?acao=excluir&id=<?php echo $servico['id']; ?>" 
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