<?php
/**
 * Gestão de Cupons de Desconto
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = getDB();

if (!verificarPermissao('cupons')) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/header.php';

// Processar ações
$acao = $_GET['acao'] ?? '';
$id_cupom = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    try {
        if ($acao === 'adicionar') {
            $stmt = $pdo->prepare("
                INSERT INTO cupons_desconto (codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, 
                                           data_inicio, data_fim, maximo_usos, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                strtoupper($_POST['codigo']),
                $_POST['descricao'],
                $_POST['tipo_desconto'],
                $_POST['valor_desconto'],
                $_POST['valor_minimo'],
                $_POST['data_inicio'],
                $_POST['data_fim'],
                $_POST['maximo_usos'] ?: null,
                'ativo'
            ]);
            $sucesso = "Cupom adicionado com sucesso!";
            
        } elseif ($acao === 'editar' && $id_cupom) {
            $stmt = $pdo->prepare("
                UPDATE cupons_desconto 
                SET codigo = ?, descricao = ?, tipo_desconto = ?, valor_desconto = ?, valor_minimo = ?, 
                    data_inicio = ?, data_fim = ?, maximo_usos = ?
                WHERE id_cupom = ?
            ");
            $stmt->execute([
                strtoupper($_POST['codigo']),
                $_POST['descricao'],
                $_POST['tipo_desconto'],
                $_POST['valor_desconto'],
                $_POST['valor_minimo'],
                $_POST['data_inicio'],
                $_POST['data_fim'],
                $_POST['maximo_usos'] ?: null,
                $id_cupom
            ]);
            $sucesso = "Cupom atualizado com sucesso!";
            
        } elseif ($acao === 'excluir' && $id_cupom) {
            $stmt = $pdo->prepare("UPDATE cupons_desconto SET status = 'inativo' WHERE id_cupom = ?");
            $stmt->execute([$id_cupom]);
            $sucesso = "Cupom removido com sucesso!";
        }
    } catch (Exception $e) {
        $erro = "Erro: " . $e->getMessage();
    }
}

// Buscar cupons
$cupons = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(cu.id_uso) as usos_atuais
        FROM cupons_desconto c 
        LEFT JOIN cupons_utilizados cu ON c.id_cupom = cu.id_cupom
        GROUP BY c.id_cupom
        ORDER BY c.data_cadastro DESC
    ");
    $cupons = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = "Erro ao carregar cupons: " . $e->getMessage();
}

// Buscar cupom para edição
$cupom_editar = null;
if ($acao === 'editar' && $id_cupom) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cupons_desconto WHERE id_cupom = ?");
        $stmt->execute([$id_cupom]);
        $cupom_editar = $stmt->fetch();
    } catch (Exception $e) {
        $erro = "Erro ao carregar cupom: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-ticket-alt"></i>
                    Gestão de Cupons de Desconto
                </h1>
                <p class="page-description">
                    Gerencie os cupons de desconto da empresa
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
                        <?php echo $acao === 'editar' ? 'Editar Cupom' : 'Adicionar Novo Cupom'; ?>
                    </h3>
                    <i class="fas fa-plus card-icon"></i>
                </div>
                <div class="card-body">
                    <form method="POST" action="?acao=<?php echo $acao === 'editar' ? 'editar&id=' . $id_cupom : 'adicionar'; ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo">Código do Cupom *</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" 
                                           value="<?php echo htmlspecialchars($cupom_editar['codigo'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="descricao">Descrição *</label>
                                    <input type="text" class="form-control" id="descricao" name="descricao" 
                                           value="<?php echo htmlspecialchars($cupom_editar['descricao'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="tipo_desconto">Tipo de Desconto *</label>
                                    <select class="form-control" id="tipo_desconto" name="tipo_desconto" required>
                                        <option value="percentual" <?php echo ($cupom_editar['tipo_desconto'] ?? '') == 'percentual' ? 'selected' : ''; ?>>Percentual (%)</option>
                                        <option value="valor_fixo" <?php echo ($cupom_editar['tipo_desconto'] ?? '') == 'valor_fixo' ? 'selected' : ''; ?>>Valor Fixo (R$)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="valor_desconto">Valor do Desconto *</label>
                                    <input type="number" step="0.01" class="form-control" id="valor_desconto" name="valor_desconto" 
                                           value="<?php echo htmlspecialchars($cupom_editar['valor_desconto'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valor_minimo">Valor Mínimo da Compra (R$)</label>
                                    <input type="number" step="0.01" class="form-control" id="valor_minimo" name="valor_minimo" 
                                           value="<?php echo htmlspecialchars($cupom_editar['valor_minimo'] ?? '0.00'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="data_inicio">Data de Início *</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="<?php echo htmlspecialchars($cupom_editar['data_inicio'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="data_fim">Data de Fim *</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="<?php echo htmlspecialchars($cupom_editar['data_fim'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="maximo_usos">Máximo de Usos (deixe em branco para ilimitado)</label>
                                    <input type="number" class="form-control" id="maximo_usos" name="maximo_usos" 
                                           value="<?php echo htmlspecialchars($cupom_editar['maximo_usos'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $acao === 'editar' ? 'Atualizar' : 'Adicionar'; ?>
                            </button>
                            <?php if ($acao === 'editar'): ?>
                                <a href="cupons.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Cupons -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cupons Cadastrados</h3>
                    <i class="fas fa-list card-icon"></i>
                </div>
                <div class="card-body">
                    <?php if (empty($cupons)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum cupom cadastrado ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                        <th>Desconto</th>
                                        <th>Validade</th>
                                        <th>Usos</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cupons as $cupom): ?>
                                        <tr>
                                            <td>
                                                <strong class="text-primary"><?php echo htmlspecialchars($cupom['codigo']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($cupom['descricao']); ?></td>
                                            <td>
                                                <?php if ($cupom['tipo_desconto'] === 'percentual'): ?>
                                                    <span class="badge badge-info"><?php echo $cupom['valor_desconto']; ?>%</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">R$ <?php echo number_format($cupom['valor_desconto'], 2, ',', '.'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo date('d/m/Y', strtotime($cupom['data_inicio'])); ?> a 
                                                    <?php echo date('d/m/Y', strtotime($cupom['data_fim'])); ?>
                                                </small>
                                            </td>
                                            <td><?php echo $cupom['usos_atuais']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $cupom['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($cupom['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?acao=editar&id=<?php echo $cupom['id_cupom']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?acao=excluir&id=<?php echo $cupom['id_cupom']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" title="Remover"
                                                       onclick="return confirm('Tem certeza que deseja remover este cupom?')">
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

<?php require_once 'includes/footer.php'; ?> 