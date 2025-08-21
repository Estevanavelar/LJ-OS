
<?php
/**
 * Gestão de Clientes
 * LJ-OS Sistema para Lava Jato
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

verificarLogin();

$db = getDB();
$mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cpf_cnpj = $_POST['cpf_cnpj'] ?? '';
    
    if (!empty($nome)) {
        try {
            $stmt = $db->prepare("INSERT INTO clientes (nome, email, telefone, cpf_cnpj) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $cpf_cnpj]);
            $mensagem = 'Cliente cadastrado com sucesso!';
        } catch (Exception $e) {
            $mensagem = 'Erro ao cadastrar cliente: ' . $e->getMessage();
        }
    }
}

// Buscar clientes
$clientes = $db->query("SELECT * FROM clientes WHERE ativo = 1 ORDER BY nome")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - LJ-OS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-car-front"></i> LJ-OS
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-person-plus"></i> Novo Cliente</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($mensagem): ?>
                            <div class="alert alert-info"><?php echo $mensagem; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> Salvar Cliente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-people"></i> Lista de Clientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>CPF/CNPJ</th>
                                        <th>Cadastro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['cpf_cnpj']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($clientes)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="bi bi-info-circle"></i> Nenhum cliente cadastrado ainda.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
