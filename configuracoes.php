<?php
/**
 * Configurações do Sistema
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Verificar login
verificarLogin();

// Verificar permissões ANTES de incluir o header
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = getDB();

if (!verificarPermissao('configuracoes')) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/header.php';

// Processar formulário de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    try {
        // Atualizar configurações
        $configuracoes = [
            'nome_empresa' => $_POST['nome_empresa'] ?? '',
            'endereco_empresa' => $_POST['endereco_empresa'] ?? '',
            'telefone_empresa' => $_POST['telefone_empresa'] ?? '',
            'email_empresa' => $_POST['email_empresa'] ?? '',
            'cnpj_empresa' => $_POST['cnpj_empresa'] ?? '',
            'horario_funcionamento' => $_POST['horario_funcionamento'] ?? '',
            'dias_funcionamento' => $_POST['dias_funcionamento'] ?? '',
            'taxa_entrega' => $_POST['taxa_entrega'] ?? '0.00',
            'desconto_fidelidade' => $_POST['desconto_fidelidade'] ?? '0.00',
            'estoque_minimo_padrao' => $_POST['estoque_minimo_padrao'] ?? '5',
            'cor_primaria' => $_POST['cor_primaria'] ?? '#007bff',
            'cor_secundaria' => $_POST['cor_secundaria'] ?? '#6c757d',
            'logo_empresa' => $_POST['logo_empresa'] ?? '',
            'observacoes_padrao' => $_POST['observacoes_padrao'] ?? '',
            'politica_privacidade' => $_POST['politica_privacidade'] ?? '',
            'termos_uso' => $_POST['termos_uso'] ?? ''
        ];

        foreach ($configuracoes as $chave => $valor) {
            $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmt->execute([$chave, $valor, $valor]);
        }

        $sucesso = "Configurações atualizadas com sucesso!";
    } catch (Exception $e) {
        $erro = "Erro ao atualizar configurações: " . $e->getMessage();
    }
}

// Buscar configurações atuais
try {
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes");
    $configs = [];
    while ($row = $stmt->fetch()) {
        $configs[$row['chave']] = $row['valor'];
    }
} catch (Exception $e) {
    $erro = "Erro ao carregar configurações: " . $e->getMessage();
    $configs = [];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-cog"></i>
                    Configurações do Sistema
                </h1>
                <p class="page-description">
                    Configure as principais configurações do sistema
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configurações Gerais</h3>
                    <i class="fas fa-cog card-icon"></i>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <!-- Informações da Empresa -->
                            <div class="col-md-6">
                                <h4 class="section-title">
                                    <i class="fas fa-building"></i>
                                    Informações da Empresa
                                </h4>
                                
                                <div class="form-group">
                                    <label for="nome_empresa">Nome da Empresa *</label>
                                    <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" 
                                           value="<?php echo htmlspecialchars($configs['nome_empresa'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="cnpj_empresa">CNPJ</label>
                                    <input type="text" class="form-control" id="cnpj_empresa" name="cnpj_empresa" 
                                           value="<?php echo htmlspecialchars($configs['cnpj_empresa'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="endereco_empresa">Endereço</label>
                                    <textarea class="form-control" id="endereco_empresa" name="endereco_empresa" rows="3"><?php echo htmlspecialchars($configs['endereco_empresa'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="telefone_empresa">Telefone</label>
                                    <input type="text" class="form-control" id="telefone_empresa" name="telefone_empresa" 
                                           value="<?php echo htmlspecialchars($configs['telefone_empresa'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="email_empresa">Email</label>
                                    <input type="email" class="form-control" id="email_empresa" name="email_empresa" 
                                           value="<?php echo htmlspecialchars($configs['email_empresa'] ?? ''); ?>">
                                </div>
                            </div>

                            <!-- Horário de Funcionamento -->
                            <div class="col-md-6">
                                <h4 class="section-title">
                                    <i class="fas fa-clock"></i>
                                    Horário de Funcionamento
                                </h4>
                                
                                <div class="form-group">
                                    <label for="dias_funcionamento">Dias de Funcionamento</label>
                                    <input type="text" class="form-control" id="dias_funcionamento" name="dias_funcionamento" 
                                           value="<?php echo htmlspecialchars($configs['dias_funcionamento'] ?? 'Segunda a Sábado'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="horario_funcionamento">Horário de Funcionamento</label>
                                    <input type="text" class="form-control" id="horario_funcionamento" name="horario_funcionamento" 
                                           value="<?php echo htmlspecialchars($configs['horario_funcionamento'] ?? '08:00 às 18:00'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="taxa_entrega">Taxa de Entrega (R$)</label>
                                    <input type="number" step="0.01" class="form-control" id="taxa_entrega" name="taxa_entrega" 
                                           value="<?php echo htmlspecialchars($configs['taxa_entrega'] ?? '0.00'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="desconto_fidelidade">Desconto Fidelidade (%)</label>
                                    <input type="number" step="0.01" class="form-control" id="desconto_fidelidade" name="desconto_fidelidade" 
                                           value="<?php echo htmlspecialchars($configs['desconto_fidelidade'] ?? '0.00'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="estoque_minimo_padrao">Estoque Mínimo Padrão</label>
                                    <input type="number" class="form-control" id="estoque_minimo_padrao" name="estoque_minimo_padrao" 
                                           value="<?php echo htmlspecialchars($configs['estoque_minimo_padrao'] ?? '5'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Cores do Sistema -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h4 class="section-title">
                                    <i class="fas fa-palette"></i>
                                    Cores do Sistema
                                </h4>
                                
                                <div class="form-group">
                                    <label for="cor_primaria">Cor Primária</label>
                                    <input type="color" class="form-control" id="cor_primaria" name="cor_primaria" 
                                           value="<?php echo htmlspecialchars($configs['cor_primaria'] ?? '#007bff'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="cor_secundaria">Cor Secundária</label>
                                    <input type="color" class="form-control" id="cor_secundaria" name="cor_secundaria" 
                                           value="<?php echo htmlspecialchars($configs['cor_secundaria'] ?? '#6c757d'); ?>">
                                </div>
                            </div>

                            <!-- Observações Padrão -->
                            <div class="col-md-6">
                                <h4 class="section-title">
                                    <i class="fas fa-sticky-note"></i>
                                    Observações Padrão
                                </h4>
                                
                                <div class="form-group">
                                    <label for="observacoes_padrao">Observações Padrão para OS</label>
                                    <textarea class="form-control" id="observacoes_padrao" name="observacoes_padrao" rows="4"><?php echo htmlspecialchars($configs['observacoes_padrao'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Políticas e Termos -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="section-title">
                                    <i class="fas fa-file-contract"></i>
                                    Políticas e Termos
                                </h4>
                                
                                <div class="form-group">
                                    <label for="politica_privacidade">Política de Privacidade</label>
                                    <textarea class="form-control" id="politica_privacidade" name="politica_privacidade" rows="6"><?php echo htmlspecialchars($configs['politica_privacidade'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="termos_uso">Termos de Uso</label>
                                    <textarea class="form-control" id="termos_uso" name="termos_uso" rows="6"><?php echo htmlspecialchars($configs['termos_uso'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Configurações
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Aplicar cores do sistema em tempo real
document.getElementById('cor_primaria').addEventListener('change', function() {
    document.documentElement.style.setProperty('--primary-color', this.value);
});

document.getElementById('cor_secundaria').addEventListener('change', function() {
    document.documentElement.style.setProperty('--secondary-color', this.value);
});

// Máscara para CNPJ
document.getElementById('cnpj_empresa').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
    value = value.replace(/(\d{4})(\d)/, '$1-$2');
    this.value = value;
});

// Máscara para telefone
document.getElementById('telefone_empresa').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    this.value = value;
});
</script>

<?php require_once 'includes/footer.php'; ?> 