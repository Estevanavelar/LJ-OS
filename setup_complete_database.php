
<?php
/**
 * Script Completo de Instalação do Banco de Dados
 * Suporte para múltiplos SGBDs e todas as funcionalidades
 */

require_once 'config/config.php';

echo "🚀 Instalação Completa do Sistema LJ-OS\n";
echo "========================================\n\n";

try {
    $db = getDBManager();
    $connection = $db->getConnection();
    $db_type = $db->getType();
    
    echo "✅ Conectado ao banco: " . strtoupper($db_type) . "\n\n";
    
    // SQL adaptado para cada SGBD
    $sql_tables = $db->adaptSQL("
    -- Tabela de usuários
    CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        telefone VARCHAR(20),
        cpf VARCHAR(14),
        id_perfil INTEGER DEFAULT 1,
        foto VARCHAR(255),
        ativo BOOLEAN DEFAULT 1,
        status VARCHAR(20) DEFAULT 'ativo',
        ultimo_login DATETIME,
        tentativas_login INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Perfis de acesso
    CREATE TABLE IF NOT EXISTS perfis_acesso (
        id_perfil INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(50) NOT NULL,
        descricao TEXT,
        nivel INTEGER NOT NULL,
        cor VARCHAR(7) DEFAULT '#007bff',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Permissões do sistema
    CREATE TABLE IF NOT EXISTS permissoes (
        id_permissao INTEGER PRIMARY KEY AUTOINCREMENT,
        modulo VARCHAR(50) NOT NULL,
        acao VARCHAR(50) NOT NULL,
        descricao TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(modulo, acao)
    );
    
    -- Permissões por perfil
    CREATE TABLE IF NOT EXISTS permissoes_perfil (
        id_perfil INTEGER,
        id_permissao INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_perfil, id_permissao)
    );
    
    -- Permissões específicas por usuário
    CREATE TABLE IF NOT EXISTS permissoes_usuario (
        id_usuario INTEGER,
        id_permissao INTEGER,
        tipo VARCHAR(10) DEFAULT 'grant',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_usuario, id_permissao)
    );
    
    -- Clientes
    CREATE TABLE IF NOT EXISTS clientes (
        id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        telefone VARCHAR(20),
        telefone_2 VARCHAR(20),
        cpf_cnpj VARCHAR(20),
        rg VARCHAR(20),
        data_nascimento DATE,
        endereco TEXT,
        cep VARCHAR(10),
        cidade VARCHAR(100),
        estado VARCHAR(2),
        observacoes TEXT,
        nivel_fidelidade VARCHAR(20) DEFAULT 'bronze',
        pontos_fidelidade INTEGER DEFAULT 0,
        desconto_especial DECIMAL(5,2) DEFAULT 0,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Veículos
    CREATE TABLE IF NOT EXISTS veiculos (
        id_veiculo INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        placa VARCHAR(8) NOT NULL,
        marca VARCHAR(50),
        modelo VARCHAR(50),
        ano INTEGER,
        cor VARCHAR(30),
        tipo VARCHAR(30),
        combustivel VARCHAR(20),
        km_atual INTEGER,
        chassi VARCHAR(17),
        renavam VARCHAR(11),
        observacoes TEXT,
        foto VARCHAR(255),
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Funcionários
    CREATE TABLE IF NOT EXISTS funcionarios (
        id_funcionario INTEGER PRIMARY KEY AUTOINCREMENT,
        id_usuario INTEGER,
        codigo VARCHAR(20) UNIQUE,
        cargo VARCHAR(50),
        salario DECIMAL(10,2),
        comissao DECIMAL(5,2) DEFAULT 0,
        data_admissao DATE,
        data_demissao DATE,
        meta_mensal DECIMAL(10,2) DEFAULT 0,
        pis VARCHAR(11),
        ctps VARCHAR(20),
        endereco TEXT,
        telefone_emergencia VARCHAR(20),
        contato_emergencia VARCHAR(100),
        observacoes TEXT,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Serviços oferecidos
    CREATE TABLE IF NOT EXISTS servicos (
        id_servico INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL,
        preco_promocional DECIMAL(10,2),
        tempo_estimado INTEGER DEFAULT 30,
        categoria VARCHAR(50),
        comissao_funcionario DECIMAL(5,2) DEFAULT 0,
        pontos_fidelidade INTEGER DEFAULT 10,
        ativo BOOLEAN DEFAULT 1,
        destaque BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Produtos/Estoque
    CREATE TABLE IF NOT EXISTS produtos (
        id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(50) UNIQUE,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        categoria VARCHAR(50),
        unidade_medida VARCHAR(10) DEFAULT 'un',
        preco_custo DECIMAL(10,2),
        preco_venda DECIMAL(10,2),
        margem_lucro DECIMAL(5,2),
        estoque_atual INTEGER DEFAULT 0,
        estoque_minimo INTEGER DEFAULT 1,
        estoque_maximo INTEGER DEFAULT 100,
        localizacao VARCHAR(50),
        codigo_barras VARCHAR(50),
        fornecedor VARCHAR(100),
        data_validade DATE,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Movimentações de estoque
    CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
        id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
        id_produto INTEGER NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        quantidade INTEGER NOT NULL,
        valor_unitario DECIMAL(10,2),
        valor_total DECIMAL(10,2),
        motivo VARCHAR(100),
        documento VARCHAR(50),
        id_usuario INTEGER,
        id_funcionario INTEGER,
        data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Agendamentos
    CREATE TABLE IF NOT EXISTS agendamentos (
        id_agendamento INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        data_agendamento DATETIME NOT NULL,
        data_fim_estimada DATETIME,
        servicos_solicitados TEXT,
        valor_estimado DECIMAL(10,2),
        observacoes TEXT,
        status VARCHAR(20) DEFAULT 'agendado',
        id_funcionario INTEGER,
        confirmado BOOLEAN DEFAULT 0,
        lembrete_enviado BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Ordens de serviço
    CREATE TABLE IF NOT EXISTS ordens_servico (
        id_os INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_os VARCHAR(20) UNIQUE NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        id_agendamento INTEGER,
        data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_inicio DATETIME,
        data_conclusao DATETIME,
        km_veiculo INTEGER,
        combustivel_nivel VARCHAR(20),
        observacoes_iniciais TEXT,
        observacoes_finais TEXT,
        valor_servicos DECIMAL(10,2) DEFAULT 0,
        valor_produtos DECIMAL(10,2) DEFAULT 0,
        desconto DECIMAL(10,2) DEFAULT 0,
        acrescimo DECIMAL(10,2) DEFAULT 0,
        valor_total DECIMAL(10,2) DEFAULT 0,
        forma_pagamento VARCHAR(50),
        status VARCHAR(20) DEFAULT 'aberta',
        id_funcionario_responsavel INTEGER,
        id_funcionario_finalizacao INTEGER,
        prioridade VARCHAR(20) DEFAULT 'normal',
        tempo_execucao INTEGER,
        avaliacao_cliente INTEGER,
        comentario_cliente TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Serviços da OS
    CREATE TABLE IF NOT EXISTS os_servicos (
        id_os_servico INTEGER PRIMARY KEY AUTOINCREMENT,
        id_os INTEGER NOT NULL,
        id_servico INTEGER NOT NULL,
        id_funcionario INTEGER,
        quantidade INTEGER DEFAULT 1,
        valor_unitario DECIMAL(10,2),
        valor_total DECIMAL(10,2),
        tempo_execucao INTEGER,
        observacoes TEXT,
        status VARCHAR(20) DEFAULT 'pendente',
        data_inicio DATETIME,
        data_fim DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Produtos da OS
    CREATE TABLE IF NOT EXISTS os_produtos (
        id_os_produto INTEGER PRIMARY KEY AUTOINCREMENT,
        id_os INTEGER NOT NULL,
        id_produto INTEGER NOT NULL,
        quantidade INTEGER NOT NULL,
        valor_unitario DECIMAL(10,2),
        valor_total DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Orçamentos
    CREATE TABLE IF NOT EXISTS orcamentos (
        id_orcamento INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_orcamento VARCHAR(20) UNIQUE NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        data_orcamento DATETIME DEFAULT CURRENT_TIMESTAMP,
        validade_dias INTEGER DEFAULT 15,
        data_validade DATETIME,
        observacoes TEXT,
        valor_servicos DECIMAL(10,2) DEFAULT 0,
        valor_produtos DECIMAL(10,2) DEFAULT 0,
        desconto DECIMAL(10,2) DEFAULT 0,
        valor_total DECIMAL(10,2) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pendente',
        id_funcionario INTEGER,
        convertido_os BOOLEAN DEFAULT 0,
        id_os_convertida INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Itens do orçamento
    CREATE TABLE IF NOT EXISTS orcamento_itens (
        id_item INTEGER PRIMARY KEY AUTOINCREMENT,
        id_orcamento INTEGER NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        id_referencia INTEGER NOT NULL,
        quantidade INTEGER DEFAULT 1,
        valor_unitario DECIMAL(10,2),
        valor_total DECIMAL(10,2),
        observacoes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Controle financeiro - Receitas
    CREATE TABLE IF NOT EXISTS receitas (
        id_receita INTEGER PRIMARY KEY AUTOINCREMENT,
        descricao VARCHAR(255) NOT NULL,
        categoria VARCHAR(50),
        valor DECIMAL(10,2) NOT NULL,
        data_recebimento DATE NOT NULL,
        data_vencimento DATE,
        forma_pagamento VARCHAR(50),
        observacoes TEXT,
        id_os INTEGER,
        id_cliente INTEGER,
        id_funcionario INTEGER,
        status VARCHAR(20) DEFAULT 'recebida',
        numero_documento VARCHAR(50),
        taxa_cartao DECIMAL(10,2) DEFAULT 0,
        valor_liquido DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Controle financeiro - Despesas
    CREATE TABLE IF NOT EXISTS despesas (
        id_despesa INTEGER PRIMARY KEY AUTOINCREMENT,
        descricao VARCHAR(255) NOT NULL,
        categoria VARCHAR(50),
        valor DECIMAL(10,2) NOT NULL,
        data_pagamento DATE,
        data_vencimento DATE NOT NULL,
        forma_pagamento VARCHAR(50),
        observacoes TEXT,
        fornecedor VARCHAR(100),
        numero_documento VARCHAR(50),
        status VARCHAR(20) DEFAULT 'pendente',
        id_funcionario INTEGER,
        recorrente BOOLEAN DEFAULT 0,
        frequencia_recorrencia VARCHAR(20),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Presença de funcionários
    CREATE TABLE IF NOT EXISTS presenca_funcionarios (
        id_presenca INTEGER PRIMARY KEY AUTOINCREMENT,
        id_funcionario INTEGER NOT NULL,
        data_presenca DATE NOT NULL,
        hora_entrada TIME,
        hora_saida TIME,
        horas_trabalhadas DECIMAL(4,2),
        observacoes TEXT,
        justificativa_falta TEXT,
        status VARCHAR(20) DEFAULT 'presente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(id_funcionario, data_presenca)
    );
    
    -- Vendas por funcionário
    CREATE TABLE IF NOT EXISTS vendas_funcionario (
        id_venda INTEGER PRIMARY KEY AUTOINCREMENT,
        id_funcionario INTEGER NOT NULL,
        id_os INTEGER NOT NULL,
        valor_venda DECIMAL(10,2) NOT NULL,
        comissao_percentual DECIMAL(5,2),
        valor_comissao DECIMAL(10,2),
        data_venda DATE NOT NULL,
        mes_referencia VARCHAR(7),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Cupons de desconto
    CREATE TABLE IF NOT EXISTS cupons (
        id_cupom INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(20) UNIQUE NOT NULL,
        descricao VARCHAR(255),
        tipo VARCHAR(20) NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        valor_minimo DECIMAL(10,2) DEFAULT 0,
        data_inicio DATE NOT NULL,
        data_fim DATE NOT NULL,
        limite_uso INTEGER DEFAULT 1,
        usado INTEGER DEFAULT 0,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Uso de cupons
    CREATE TABLE IF NOT EXISTS cupons_uso (
        id_uso INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cupom INTEGER NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_os INTEGER,
        data_uso DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Logs de auditoria
    CREATE TABLE IF NOT EXISTS logs_auditoria (
        id_log INTEGER PRIMARY KEY AUTOINCREMENT,
        id_usuario INTEGER,
        acao VARCHAR(50) NOT NULL,
        tabela VARCHAR(50),
        id_registro INTEGER,
        dados_anteriores TEXT,
        dados_novos TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Logs de segurança
    CREATE TABLE IF NOT EXISTS security_logs (
        id_log INTEGER PRIMARY KEY AUTOINCREMENT,
        event VARCHAR(100) NOT NULL,
        severity VARCHAR(20) DEFAULT 'INFO',
        ip_address VARCHAR(45),
        user_agent TEXT,
        user_id INTEGER,
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Rate limiting
    CREATE TABLE IF NOT EXISTS rate_limits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        action_key VARCHAR(255) NOT NULL,
        identifier VARCHAR(255) NOT NULL,
        created_at INTEGER NOT NULL
    );
    
    -- Blacklist de IPs
    CREATE TABLE IF NOT EXISTS ip_blacklist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        reason TEXT,
        expires_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Tokens de \"lembrar-me\"
    CREATE TABLE IF NOT EXISTS remember_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Configurações do sistema
    CREATE TABLE IF NOT EXISTS configuracoes (
        id_config INTEGER PRIMARY KEY AUTOINCREMENT,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        tipo VARCHAR(20) DEFAULT 'string',
        categoria VARCHAR(50) DEFAULT 'geral',
        descricao TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Notificações
    CREATE TABLE IF NOT EXISTS notificacoes (
        id_notificacao INTEGER PRIMARY KEY AUTOINCREMENT,
        id_usuario INTEGER,
        titulo VARCHAR(255) NOT NULL,
        mensagem TEXT NOT NULL,
        tipo VARCHAR(50) DEFAULT 'info',
        lida BOOLEAN DEFAULT 0,
        url VARCHAR(255),
        data_expiracao DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Backup logs
    CREATE TABLE IF NOT EXISTS backup_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo VARCHAR(50) NOT NULL,
        arquivo VARCHAR(255),
        tamanho INTEGER,
        status VARCHAR(20) DEFAULT 'sucesso',
        mensagem TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ");
    
    // Executar criação das tabelas
    $connection->exec($sql_tables);
    echo "✅ Tabelas criadas com sucesso\n\n";
    
    // Inserir dados básicos
    echo "📝 Inserindo dados básicos...\n";
    
    // Perfis de acesso
    $perfis = [
        ['Admin', 'Administrador do sistema', 5, '#dc3545'],
        ['Gerente', 'Gerente geral', 4, '#fd7e14'],
        ['Atendente', 'Atendente/Recepcionista', 3, '#28a745'],
        ['Lavador', 'Funcionário lavador', 2, '#007bff'],
        ['Cliente', 'Cliente do sistema', 1, '#6c757d']
    ];
    
    foreach ($perfis as $perfil) {
        $db->query(
            "INSERT OR IGNORE INTO perfis_acesso (nome, descricao, nivel, cor) VALUES (?, ?, ?, ?)",
            $perfil
        );
    }
    
    // Permissões básicas
    $modulos = [
        'dashboard' => ['visualizar'],
        'usuarios' => ['criar', 'editar', 'excluir', 'visualizar'],
        'clientes' => ['criar', 'editar', 'excluir', 'visualizar'],
        'veiculos' => ['criar', 'editar', 'excluir', 'visualizar'],
        'funcionarios' => ['criar', 'editar', 'excluir', 'visualizar'],
        'servicos' => ['criar', 'editar', 'excluir', 'visualizar'],
        'produtos' => ['criar', 'editar', 'excluir', 'visualizar'],
        'estoque' => ['criar', 'editar', 'excluir', 'visualizar', 'movimentar'],
        'agendamentos' => ['criar', 'editar', 'excluir', 'visualizar'],
        'ordens_servico' => ['criar', 'editar', 'excluir', 'visualizar', 'finalizar'],
        'orcamentos' => ['criar', 'editar', 'excluir', 'visualizar', 'converter'],
        'financeiro' => ['criar', 'editar', 'excluir', 'visualizar'],
        'relatorios' => ['gerar', 'exportar', 'visualizar'],
        'configuracoes' => ['editar', 'visualizar'],
        'permissoes' => ['gerenciar']
    ];
    
    foreach ($modulos as $modulo => $acoes) {
        foreach ($acoes as $acao) {
            $db->query(
                "INSERT OR IGNORE INTO permissoes (modulo, acao, descricao) VALUES (?, ?, ?)",
                [$modulo, $acao, ucfirst($acao) . ' ' . $modulo]
            );
        }
    }
    
    // Configurar permissões para perfil Admin (todas)
    $stmt = $db->query("SELECT id_permissao FROM permissoes");
    $permissoes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($permissoes as $id_permissao) {
        $db->query(
            "INSERT OR IGNORE INTO permissoes_perfil (id_perfil, id_permissao) VALUES (1, ?)",
            [$id_permissao]
        );
    }
    
    // Usuário administrador
    $admin_exists = $db->query("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@lavajato.com'")->fetchColumn();
    
    if ($admin_exists == 0) {
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO usuarios (nome, email, senha, id_perfil, status) VALUES (?, ?, ?, 1, 'ativo')",
            ['Administrador', 'admin@lavajato.com', $senha_hash]
        );
        echo "✅ Usuário administrador criado\n";
    }
    
    // Configurações básicas
    $configs = [
        ['nome_empresa', 'LJ-OS Sistema', 'string', 'empresa', 'Nome da empresa'],
        ['cnpj_empresa', '', 'string', 'empresa', 'CNPJ da empresa'],
        ['telefone_empresa', '(11) 99999-9999', 'string', 'empresa', 'Telefone da empresa'],
        ['email_empresa', 'contato@lavajato.com', 'string', 'empresa', 'Email da empresa'],
        ['endereco_empresa', 'Rua Exemplo, 123', 'string', 'empresa', 'Endereço da empresa'],
        ['horario_funcionamento', '08:00 às 18:00', 'string', 'operacao', 'Horário de funcionamento'],
        ['dias_funcionamento', 'Segunda a Sábado', 'string', 'operacao', 'Dias de funcionamento'],
        ['moeda_padrao', 'BRL', 'string', 'sistema', 'Moeda padrão do sistema'],
        ['fuso_horario', 'America/Sao_Paulo', 'string', 'sistema', 'Fuso horário'],
        ['backup_automatico', 'true', 'boolean', 'sistema', 'Backup automático ativado'],
        ['notificacoes_whatsapp', 'false', 'boolean', 'notificacoes', 'Notificações via WhatsApp'],
        ['notificacoes_sms', 'false', 'boolean', 'notificacoes', 'Notificações via SMS'],
        ['pontos_por_real', '1', 'number', 'fidelidade', 'Pontos por real gasto'],
        ['desconto_bronze', '0', 'number', 'fidelidade', 'Desconto nível Bronze (%)'],
        ['desconto_prata', '5', 'number', 'fidelidade', 'Desconto nível Prata (%)'],
        ['desconto_ouro', '10', 'number', 'fidelidade', 'Desconto nível Ouro (%)'],
        ['desconto_platinum', '15', 'number', 'fidelidade', 'Desconto nível Platinum (%)']
    ];
    
    foreach ($configs as $config) {
        $db->query(
            "INSERT OR REPLACE INTO configuracoes (chave, valor, tipo, categoria, descricao) VALUES (?, ?, ?, ?, ?)",
            $config
        );
    }
    
    // Serviços básicos
    $servicos_basicos = [
        ['Lavagem Simples', 'Lavagem externa básica', 25.00, 30, 'Lavagem', 5.00, 25],
        ['Lavagem Completa', 'Lavagem externa + interna', 45.00, 60, 'Lavagem', 10.00, 45],
        ['Enceramento', 'Aplicação de cera protetora', 35.00, 45, 'Estética', 8.00, 35],
        ['Aspiração', 'Limpeza interna completa', 20.00, 20, 'Limpeza', 3.00, 20],
        ['Lavagem de Motor', 'Limpeza do compartimento do motor', 40.00, 30, 'Especializada', 8.00, 40]
    ];
    
    foreach ($servicos_basicos as $servico) {
        $db->query(
            "INSERT OR IGNORE INTO servicos (nome, descricao, preco, tempo_estimado, categoria, comissao_funcionario, pontos_fidelidade) VALUES (?, ?, ?, ?, ?, ?, ?)",
            $servico
        );
    }
    
    // Produtos básicos
    $produtos_basicos = [
        ['SHAMPOO-001', 'Shampoo Neutro 1L', 'Produto de limpeza', 'Limpeza', 'L', 15.00, 25.00, 40.00, 10, 5, 50],
        ['CERA-001', 'Cera Carnaúba 500ml', 'Cera de proteção', 'Enceramento', 'un', 45.00, 80.00, 44.44, 5, 2, 20],
        ['PANO-001', 'Pano Microfibra', 'Pano para secagem', 'Acessórios', 'un', 8.00, 15.00, 46.67, 20, 10, 50],
        ['DETERGENTE-001', 'Detergente Desengraxante', 'Limpeza pesada', 'Limpeza', 'L', 12.00, 22.00, 45.45, 15, 5, 30]
    ];
    
    foreach ($produtos_basicos as $produto) {
        $db->query(
            "INSERT OR IGNORE INTO produtos (codigo, nome, descricao, categoria, unidade_medida, preco_custo, preco_venda, margem_lucro, estoque_atual, estoque_minimo, estoque_maximo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $produto
        );
    }
    
    echo "✅ Dados básicos inseridos\n\n";
    
    // Criar índices para performance
    echo "⚡ Criando índices de performance...\n";
    
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email)",
        "CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON usuarios(ativo)",
        "CREATE INDEX IF NOT EXISTS idx_clientes_nome ON clientes(nome)",
        "CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON clientes(cpf_cnpj)",
        "CREATE INDEX IF NOT EXISTS idx_veiculos_placa ON veiculos(placa)",
        "CREATE INDEX IF NOT EXISTS idx_veiculos_cliente ON veiculos(id_cliente)",
        "CREATE INDEX IF NOT EXISTS idx_os_numero ON ordens_servico(numero_os)",
        "CREATE INDEX IF NOT EXISTS idx_os_status ON ordens_servico(status)",
        "CREATE INDEX IF NOT EXISTS idx_os_data ON ordens_servico(data_abertura)",
        "CREATE INDEX IF NOT EXISTS idx_agendamentos_data ON agendamentos(data_agendamento)",
        "CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status)",
        "CREATE INDEX IF NOT EXISTS idx_produtos_codigo ON produtos(codigo)",
        "CREATE INDEX IF NOT EXISTS idx_movimentacoes_produto ON movimentacoes_estoque(id_produto)",
        "CREATE INDEX IF NOT EXISTS idx_movimentacoes_data ON movimentacoes_estoque(data_movimentacao)",
        "CREATE INDEX IF NOT EXISTS idx_receitas_data ON receitas(data_recebimento)",
        "CREATE INDEX IF NOT EXISTS idx_despesas_data ON despesas(data_vencimento)",
        "CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_auditoria(id_usuario)",
        "CREATE INDEX IF NOT EXISTS idx_logs_data ON logs_auditoria(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_security_logs_event ON security_logs(event)",
        "CREATE INDEX IF NOT EXISTS idx_security_logs_ip ON security_logs(ip_address)"
    ];
    
    foreach ($indices as $index) {
        try {
            $connection->exec($index);
        } catch (Exception $e) {
            // Índice já existe ou erro menor
            continue;
        }
    }
    
    echo "✅ Índices criados\n\n";
    
    echo "🎉 Instalação concluída com sucesso!\n\n";
    echo "📊 Resumo da instalação:\n";
    echo "• Banco de dados: " . strtoupper($db_type) . "\n";
    echo "• Tabelas criadas: 25+\n";
    echo "• Usuário admin: admin@lavajato.com / admin123\n";
    echo "• Perfis de acesso: 5\n";
    echo "• Permissões: " . count($permissoes) . "\n";
    echo "• Serviços básicos: 5\n";
    echo "• Produtos básicos: 4\n\n";
    
    echo "🚀 Próximos passos:\n";
    echo "1. Acesse o sistema pelo navegador\n";
    echo "2. Faça login com as credenciais do admin\n";
    echo "3. Configure os dados da empresa\n";
    echo "4. Cadastre funcionários e permissões\n";
    echo "5. Comece a usar o sistema!\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a instalação: " . $e->getMessage() . "\n";
    echo "📋 Detalhes: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    exit(1);
}
