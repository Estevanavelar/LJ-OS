# Script Detalhado para Implementação do Sistema de Lava Jato

## Introdução

Este script fornece um guia completo para a implementação de um sistema web de gerenciamento de lava jato de carros e caminhões, baseado nas funcionalidades identificadas no sistema VeltaCar e nas melhores práticas do mercado. O sistema será desenvolvido utilizando PHP, MySQL, HTML, CSS e JavaScript, garantindo uma solução robusta, escalável e fácil de manter.

## Estrutura de Diretórios Proposta

```
lava_jato_system/
├── config/
│   ├── database.php
│   └── config.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   └── functions.php
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── jquery.min.js
│   │   ├── bootstrap.min.js
│   │   ├── main.js
│   │   └── calendar.js
│   └── images/
│       └── uploads/
├── modules/
│   ├── clientes/
│   ├── veiculos/
│   ├── servicos/
│   ├── agendamentos/
│   ├── ordens_servico/
│   ├── estoque/
│   ├── financeiro/
│   ├── relatorios/
│   ├── usuarios/
│   └── configuracoes/
├── api/
│   ├── whatsapp/
│   └── sms/
├── uploads/
│   ├── checklist_avarias/
│   └── documentos/
├── sql/
│   └── database_structure.sql
├── index.php
├── login.php
├── logout.php
└── dashboard.php
```

## 1. Configuração do Banco de Dados

### 1.1 Arquivo de Configuração (config/database.php)

```php
<?php
/**
 * Configuração de conexão com o banco de dados
 * Sistema de Lava Jato - VeltaCar Clone
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'lava_jato_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe para gerenciar conexões com o banco de dados
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            // String de conexão PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opções de configuração do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Criar conexão
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log do erro e exibição de mensagem amigável
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
        }
    }
    
    /**
     * Método para obter instância única da classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Método para obter a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {}
}

/**
 * Função auxiliar para obter conexão com o banco
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
```

### 1.2 Script SQL para Criação das Tabelas (sql/database_structure.sql)

```sql
-- Script de criação do banco de dados para Sistema de Lava Jato
-- Baseado no sistema VeltaCar com funcionalidades expandidas

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS lava_jato_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lava_jato_db;

-- Tabela de usuários do sistema
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'gerente', 'atendente', 'funcionario') DEFAULT 'funcionario',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    foto_perfil VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    observacoes TEXT NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Tabela de clientes (pessoas físicas e jurídicas)
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    tipo_pessoa ENUM('PF', 'PJ') NOT NULL DEFAULT 'PF',
    cpf_cnpj VARCHAR(20) UNIQUE NOT NULL,
    rg_ie VARCHAR(20) NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    endereco TEXT NULL,
    cep VARCHAR(10) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    data_nascimento DATE NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    observacoes TEXT NULL,
    programa_fidelidade BOOLEAN DEFAULT FALSE,
    pontos_fidelidade INT DEFAULT 0,
    INDEX idx_cpf_cnpj (cpf_cnpj),
    INDEX idx_telefone (telefone),
    INDEX idx_status (status)
);

-- Tabela de veículos
CREATE TABLE veiculos (
    id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    ano_modelo INT NULL,
    cor VARCHAR(30) NOT NULL,
    combustivel ENUM('gasolina', 'etanol', 'diesel', 'flex', 'gnv', 'eletrico', 'hibrido') NULL,
    km_atual INT NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    INDEX idx_placa (placa),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de categorias de serviços
CREATE TABLE categorias_servicos (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de serviços oferecidos
CREATE TABLE servicos (
    id_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NULL,
    nome_servico VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_estimada INT NOT NULL COMMENT 'Duração em minutos',
    tipo_veiculo ENUM('carro', 'moto', 'caminhao', 'todos') DEFAULT 'todos',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias_servicos(id_categoria) ON SET NULL,
    INDEX idx_status (status),
    INDEX idx_tipo_veiculo (tipo_veiculo)
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_servico INT NOT NULL,
    data_agendamento DATETIME NOT NULL,
    hora_entrega_estimada DATETIME NULL,
    vaga INT NULL COMMENT 'Número da vaga onde será realizado o serviço',
    status ENUM('pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
    valor_estimado DECIMAL(10,2) NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_cadastro INT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_cadastro) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_data_agendamento (data_agendamento),
    INDEX idx_status (status),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de ordens de serviço
CREATE TABLE ordens_servico (
    id_os INT AUTO_INCREMENT PRIMARY KEY,
    codigo_os VARCHAR(20) UNIQUE NOT NULL,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_agendamento INT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_inicio DATETIME NULL,
    data_conclusao DATETIME NULL,
    status ENUM('aberta', 'em_andamento', 'finalizada', 'cancelada') DEFAULT 'aberta',
    valor_servicos DECIMAL(10,2) DEFAULT 0.00,
    valor_produtos DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    acrescimo DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    forma_pagamento ENUM('dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'transferencia', 'cheque') NULL,
    vaga INT NULL,
    km_veiculo INT NULL,
    observacoes TEXT NULL,
    checklist_avarias JSON NULL COMMENT 'Dados do checklist de avarias em formato JSON',
    usuario_abertura INT NULL,
    usuario_conclusao INT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE RESTRICT,
    FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id_agendamento) ON SET NULL,
    FOREIGN KEY (usuario_abertura) REFERENCES usuarios(id_usuario) ON SET NULL,
    FOREIGN KEY (usuario_conclusao) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_codigo_os (codigo_os),
    INDEX idx_status (status),
    INDEX idx_data_abertura (data_abertura),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de relacionamento entre ordens de serviço e serviços
CREATE TABLE os_servicos (
    id_os_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_os INT NOT NULL,
    id_servico INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    INDEX idx_os (id_os)
);

-- Tabela de produtos para estoque e venda
CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    codigo_produto VARCHAR(50) UNIQUE NULL,
    nome_produto VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    categoria VARCHAR(100) NULL,
    unidade_medida ENUM('unidade', 'litro', 'kg', 'metro', 'pacote') DEFAULT 'unidade',
    preco_custo DECIMAL(10,2) DEFAULT 0.00,
    preco_venda DECIMAL(10,2) NOT NULL,
    estoque_atual INT DEFAULT 0,
    estoque_minimo INT DEFAULT 0,
    estoque_maximo INT DEFAULT 0,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo_produto (codigo_produto),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria)
);

-- Tabela de movimentações de estoque
CREATE TABLE movimentacoes_estoque (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida', 'ajuste', 'perda') NOT NULL,
    quantidade INT NOT NULL,
    valor_unitario DECIMAL(10,2) NULL,
    valor_total DECIMAL(10,2) NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NULL,
    documento VARCHAR(100) NULL COMMENT 'Número da nota fiscal, ordem de serviço, etc.',
    usuario_responsavel INT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_produto (id_produto),
    INDEX idx_data_movimentacao (data_movimentacao),
    INDEX idx_tipo_movimentacao (tipo_movimentacao)
);

-- Tabela de relacionamento entre ordens de serviço e produtos vendidos
CREATE TABLE os_produtos (
    id_os_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_os INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT,
    INDEX idx_os (id_os)
);

-- Tabela de cupons de desconto
CREATE TABLE cupons_desconto (
    id_cupom INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome_cupom VARCHAR(100) NOT NULL,
    tipo_desconto ENUM('porcentagem', 'valor_fixo') NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL,
    valor_minimo_compra DECIMAL(10,2) DEFAULT 0.00,
    data_inicio DATE NOT NULL,
    data_validade DATE NOT NULL,
    usos_maximos INT DEFAULT 0 COMMENT '0 = ilimitado',
    usos_atuais INT DEFAULT 0,
    status ENUM('ativo', 'inativo', 'expirado') DEFAULT 'ativo',
    aplicavel_a ENUM('servicos', 'produtos', 'ambos') DEFAULT 'ambos',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_data_validade (data_validade)
);

-- Tabela de uso de cupons
CREATE TABLE cupons_utilizados (
    id_uso INT AUTO_INCREMENT PRIMARY KEY,
    id_cupom INT NOT NULL,
    id_os INT NOT NULL,
    valor_desconto_aplicado DECIMAL(10,2) NOT NULL,
    data_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cupom) REFERENCES cupons_desconto(id_cupom) ON DELETE CASCADE,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON DELETE CASCADE,
    INDEX idx_cupom (id_cupom),
    INDEX idx_os (id_os)
);

-- Tabela de transações financeiras
CREATE TABLE financeiro (
    id_transacao INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('receita', 'despesa') NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_transacao DATE NOT NULL,
    data_vencimento DATE NULL,
    forma_pagamento VARCHAR(50) NULL,
    status ENUM('pendente', 'pago', 'cancelado', 'vencido') DEFAULT 'pendente',
    id_os INT NULL COMMENT 'Referência à ordem de serviço se aplicável',
    documento VARCHAR(100) NULL,
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_data_transacao (data_transacao),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria)
);

-- Tabela de orçamentos
CREATE TABLE orcamentos (
    id_orcamento INT AUTO_INCREMENT PRIMARY KEY,
    numero_orcamento VARCHAR(20) UNIQUE NOT NULL,
    id_cliente INT NOT NULL,
    id_veiculo INT NULL,
    data_orcamento DATE NOT NULL,
    data_validade DATE NOT NULL,
    valor_servicos DECIMAL(10,2) DEFAULT 0.00,
    valor_produtos DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    acrescimo DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'expirado') DEFAULT 'pendente',
    observacoes TEXT NULL,
    usuario_responsavel INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON SET NULL,
    FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_numero_orcamento (numero_orcamento),
    INDEX idx_status (status),
    INDEX idx_data_orcamento (data_orcamento),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de itens do orçamento (serviços)
CREATE TABLE orcamento_servicos (
    id_orcamento_servico INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    id_servico INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento) ON DELETE CASCADE,
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT,
    INDEX idx_orcamento (id_orcamento)
);

-- Tabela de itens do orçamento (produtos)
CREATE TABLE orcamento_produtos (
    id_orcamento_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT,
    INDEX idx_orcamento (id_orcamento)
);

-- Tabela de notas fiscais
CREATE TABLE notas_fiscais (
    id_nf INT AUTO_INCREMENT PRIMARY KEY,
    numero_nf VARCHAR(50) UNIQUE NOT NULL,
    serie_nf VARCHAR(10) NOT NULL,
    id_os INT NULL,
    id_cliente INT NOT NULL,
    tipo_nf ENUM('NFe', 'NFCe', 'NFSe') NOT NULL,
    data_emissao DATETIME NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status ENUM('emitida', 'cancelada', 'inutilizada') DEFAULT 'emitida',
    chave_acesso VARCHAR(44) NULL,
    caminho_xml VARCHAR(255) NULL,
    caminho_pdf VARCHAR(255) NULL,
    observacoes TEXT NULL,
    usuario_emissao INT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) ON SET NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_emissao) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_numero_nf (numero_nf),
    INDEX idx_status (status),
    INDEX idx_data_emissao (data_emissao),
    INDEX idx_cliente (id_cliente)
);

-- Tabela de configurações do sistema
CREATE TABLE configuracoes (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NULL,
    descricao TEXT NULL,
    tipo ENUM('texto', 'numero', 'boolean', 'json') DEFAULT 'texto',
    categoria VARCHAR(50) NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de logs do sistema
CREATE TABLE logs_sistema (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    acao VARCHAR(255) NOT NULL,
    tabela_afetada VARCHAR(100) NULL,
    id_registro INT NULL,
    dados_anteriores JSON NULL,
    dados_novos JSON NULL,
    ip_usuario VARCHAR(45) NULL,
    user_agent TEXT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_data_acao (data_acao),
    INDEX idx_tabela_afetada (tabela_afetada)
);

-- Inserção de dados iniciais

-- Inserir usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES 
('Administrador', 'admin@lavajato.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir categorias de serviços padrão
INSERT INTO categorias_servicos (nome_categoria, descricao) VALUES 
('Lavagem Simples', 'Serviços básicos de lavagem externa'),
('Lavagem Completa', 'Serviços completos incluindo enceramento'),
('Higienização', 'Serviços de limpeza interna e higienização'),
('Estética Automotiva', 'Serviços especializados de estética'),
('Serviços Especiais', 'Serviços diferenciados e personalizados');

-- Inserir serviços padrão
INSERT INTO servicos (id_categoria, nome_servico, descricao, preco, duracao_estimada, tipo_veiculo) VALUES 
(1, 'Lavagem Simples Carro', 'Lavagem externa básica para carros', 15.00, 30, 'carro'),
(1, 'Lavagem Simples Moto', 'Lavagem externa básica para motos', 10.00, 20, 'moto'),
(1, 'Lavagem Simples Caminhão', 'Lavagem externa básica para caminhões', 35.00, 60, 'caminhao'),
(2, 'Lavagem Completa Carro', 'Lavagem completa com enceramento para carros', 25.00, 45, 'carro'),
(2, 'Lavagem Completa Moto', 'Lavagem completa com enceramento para motos', 18.00, 35, 'moto'),
(3, 'Higienização Interna', 'Limpeza e higienização do interior do veículo', 30.00, 60, 'todos'),
(4, 'Enceramento', 'Aplicação de cera protetora', 20.00, 30, 'todos'),
(4, 'Polimento', 'Polimento da pintura', 80.00, 120, 'todos');

-- Inserir produtos padrão
INSERT INTO produtos (nome_produto, descricao, categoria, unidade_medida, preco_custo, preco_venda, estoque_atual, estoque_minimo) VALUES 
('Shampoo Automotivo 1L', 'Shampoo concentrado para lavagem', 'Produtos de Limpeza', 'litro', 8.50, 15.00, 50, 10),
('Cera Líquida 500ml', 'Cera líquida para proteção da pintura', 'Produtos de Limpeza', 'unidade', 12.00, 22.00, 30, 5),
('Pano Microfibra', 'Pano de microfibra para secagem', 'Acessórios', 'unidade', 5.00, 12.00, 100, 20),
('Aromatizante', 'Aromatizante para veículos', 'Acessórios', 'unidade', 2.50, 8.00, 200, 50);

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES 
('nome_empresa', 'Lava Jato VeltaCar', 'Nome da empresa', 'texto', 'geral'),
('cnpj_empresa', '00.000.000/0001-00', 'CNPJ da empresa', 'texto', 'geral'),
('endereco_empresa', 'Rua Exemplo, 123 - Centro', 'Endereço da empresa', 'texto', 'geral'),
('telefone_empresa', '(11) 99999-9999', 'Telefone da empresa', 'texto', 'geral'),
('email_empresa', 'contato@lavajato.com', 'E-mail da empresa', 'texto', 'geral'),
('whatsapp_token', '', 'Token da API do WhatsApp', 'texto', 'integracoes'),
('sms_token', '', 'Token da API de SMS', 'texto', 'integracoes'),
('backup_automatico', '1', 'Ativar backup automático', 'boolean', 'sistema'),
('moeda_padrao', 'BRL', 'Moeda padrão do sistema', 'texto', 'financeiro');
```


## 2. Classes PHP Principais

### 2.1 Classe de Funções Auxiliares (includes/functions.php)

```php
<?php
/**
 * Funções auxiliares do sistema
 * Sistema de Lava Jato - VeltaCar Clone
 */

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuração do banco de dados
require_once __DIR__ . '/../config/database.php';

/**
 * Função para verificar se o usuário está logado
 */
function verificarLogin() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_logado'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Função para verificar nível de acesso do usuário
 */
function verificarNivelAcesso($niveis_permitidos = []) {
    verificarLogin();
    
    if (!empty($niveis_permitidos) && !in_array($_SESSION['nivel_acesso'], $niveis_permitidos)) {
        header('Location: dashboard.php?erro=acesso_negado');
        exit();
    }
}

/**
 * Função para sanitizar dados de entrada
 */
function sanitizar($dados) {
    if (is_array($dados)) {
        return array_map('sanitizar', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para validar CPF
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Função para validar CNPJ
 */
function validarCNPJ($cnpj) {
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    // Verifica se tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    $peso = 2;
    for ($i = 11; $i >= 0; $i--) {
        $soma += $cnpj[$i] * $peso;
        $peso = ($peso == 9) ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    $peso = 2;
    for ($i = 12; $i >= 0; $i--) {
        $soma += $cnpj[$i] * $peso;
        $peso = ($peso == 9) ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem
    return ($cnpj[12] == $dv1 && $cnpj[13] == $dv2);
}

/**
 * Função para formatar CPF/CNPJ
 */
function formatarCpfCnpj($documento) {
    $documento = preg_replace('/[^0-9]/', '', $documento);
    
    if (strlen($documento) == 11) {
        // CPF
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
    } elseif (strlen($documento) == 14) {
        // CNPJ
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
    }
    
    return $documento;
}

/**
 * Função para formatar telefone
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) == 11) {
        // Celular com 9 dígitos
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        // Telefone fixo
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    
    return $telefone;
}

/**
 * Função para formatar moeda
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Função para converter moeda para decimal
 */
function converterMoedaParaDecimal($valor) {
    // Remove símbolos de moeda e espaços
    $valor = str_replace(['R$', ' '], '', $valor);
    // Substitui vírgula por ponto
    $valor = str_replace(',', '.', $valor);
    // Remove pontos que não sejam o separador decimal
    $valor = preg_replace('/\.(?=.*\.)/', '', $valor);
    
    return floatval($valor);
}

/**
 * Função para gerar código único
 */
function gerarCodigo($prefixo = '', $tamanho = 8) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = $prefixo;
    
    for ($i = 0; $i < $tamanho; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $codigo;
}

/**
 * Função para upload de arquivos
 */
function uploadArquivo($arquivo, $diretorio, $tipos_permitidos = ['jpg', 'jpeg', 'png', 'pdf']) {
    // Verifica se o arquivo foi enviado
    if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'erro' => 'Erro no upload do arquivo'];
    }
    
    // Verifica o tipo do arquivo
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $tipos_permitidos)) {
        return ['sucesso' => false, 'erro' => 'Tipo de arquivo não permitido'];
    }
    
    // Gera nome único para o arquivo
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_completo = $diretorio . '/' . $nome_arquivo;
    
    // Cria o diretório se não existir
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }
    
    // Move o arquivo para o diretório de destino
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        return ['sucesso' => true, 'arquivo' => $nome_arquivo, 'caminho' => $caminho_completo];
    } else {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar o arquivo'];
    }
}

/**
 * Função para registrar log do sistema
 */
function registrarLog($acao, $tabela_afetada = null, $id_registro = null, $dados_anteriores = null, $dados_novos = null) {
    try {
        $db = getDB();
        
        $sql = "INSERT INTO logs_sistema (id_usuario, acao, tabela_afetada, id_registro, dados_anteriores, dados_novos, ip_usuario, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $acao,
            $tabela_afetada,
            $id_registro,
            $dados_anteriores ? json_encode($dados_anteriores) : null,
            $dados_novos ? json_encode($dados_novos) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

/**
 * Função para enviar notificação via WhatsApp (placeholder)
 */
function enviarWhatsApp($telefone, $mensagem) {
    // Implementar integração com API do WhatsApp
    // Esta é uma função placeholder que deve ser implementada conforme a API escolhida
    
    try {
        // Exemplo de implementação com API fictícia
        $dados = [
            'telefone' => $telefone,
            'mensagem' => $mensagem
        ];
        
        // Aqui seria feita a chamada para a API do WhatsApp
        // return chamarAPIWhatsApp($dados);
        
        // Por enquanto, apenas registra no log
        registrarLog("Tentativa de envio WhatsApp", null, null, null, $dados);
        
        return ['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso'];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

/**
 * Função para enviar SMS (placeholder)
 */
function enviarSMS($telefone, $mensagem) {
    // Implementar integração com API de SMS
    // Esta é uma função placeholder que deve ser implementada conforme a API escolhida
    
    try {
        $dados = [
            'telefone' => $telefone,
            'mensagem' => $mensagem
        ];
        
        // Aqui seria feita a chamada para a API de SMS
        // return chamarAPISMS($dados);
        
        // Por enquanto, apenas registra no log
        registrarLog("Tentativa de envio SMS", null, null, null, $dados);
        
        return ['sucesso' => true, 'mensagem' => 'SMS enviado com sucesso'];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

/**
 * Função para calcular pontos de fidelidade
 */
function calcularPontosFidelidade($valor_compra) {
    // 1 ponto para cada R$ 10,00 gastos
    return floor($valor_compra / 10);
}

/**
 * Função para aplicar desconto de cupom
 */
function aplicarCupomDesconto($codigo_cupom, $valor_total) {
    try {
        $db = getDB();
        
        // Buscar cupom válido
        $sql = "SELECT * FROM cupons_desconto 
                WHERE codigo = ? 
                AND status = 'ativo' 
                AND data_inicio <= CURDATE() 
                AND data_validade >= CURDATE() 
                AND (usos_maximos = 0 OR usos_atuais < usos_maximos)
                AND valor_minimo_compra <= ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$codigo_cupom, $valor_total]);
        $cupom = $stmt->fetch();
        
        if (!$cupom) {
            return ['sucesso' => false, 'erro' => 'Cupom inválido ou expirado'];
        }
        
        // Calcular desconto
        if ($cupom['tipo_desconto'] == 'porcentagem') {
            $desconto = ($valor_total * $cupom['valor_desconto']) / 100;
        } else {
            $desconto = $cupom['valor_desconto'];
        }
        
        // Garantir que o desconto não seja maior que o valor total
        $desconto = min($desconto, $valor_total);
        
        return [
            'sucesso' => true,
            'cupom' => $cupom,
            'desconto' => $desconto,
            'valor_final' => $valor_total - $desconto
        ];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao aplicar cupom: ' . $e->getMessage()];
    }
}

/**
 * Função para obter configuração do sistema
 */
function obterConfiguracao($chave, $valor_padrao = null) {
    try {
        $db = getDB();
        
        $sql = "SELECT valor FROM configuracoes WHERE chave = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$chave]);
        $resultado = $stmt->fetch();
        
        return $resultado ? $resultado['valor'] : $valor_padrao;
        
    } catch (Exception $e) {
        return $valor_padrao;
    }
}

/**
 * Função para definir configuração do sistema
 */
function definirConfiguracao($chave, $valor, $descricao = null, $tipo = 'texto', $categoria = null) {
    try {
        $db = getDB();
        
        $sql = "INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                descricao = VALUES(descricao), 
                tipo = VALUES(tipo), 
                categoria = VALUES(categoria)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([$chave, $valor, $descricao, $tipo, $categoria]);
        
    } catch (Exception $e) {
        error_log("Erro ao definir configuração: " . $e->getMessage());
        return false;
    }
}
?>
```

### 2.2 Classe de Clientes (modules/clientes/Cliente.php)

```php
<?php
/**
 * Classe para gerenciamento de clientes
 * Sistema de Lava Jato - VeltaCar Clone
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

class Cliente {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Listar todos os clientes
     */
    public function listar($filtros = []) {
        try {
            $sql = "SELECT c.*, 
                           COUNT(v.id_veiculo) as total_veiculos,
                           COUNT(os.id_os) as total_os
                    FROM clientes c
                    LEFT JOIN veiculos v ON c.id_cliente = v.id_cliente
                    LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente
                    WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros
            if (!empty($filtros['nome'])) {
                $sql .= " AND c.nome LIKE ?";
                $params[] = '%' . $filtros['nome'] . '%';
            }
            
            if (!empty($filtros['cpf_cnpj'])) {
                $sql .= " AND c.cpf_cnpj LIKE ?";
                $params[] = '%' . $filtros['cpf_cnpj'] . '%';
            }
            
            if (!empty($filtros['tipo_pessoa'])) {
                $sql .= " AND c.tipo_pessoa = ?";
                $params[] = $filtros['tipo_pessoa'];
            }
            
            if (!empty($filtros['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filtros['status'];
            }
            
            $sql .= " GROUP BY c.id_cliente ORDER BY c.nome";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao listar clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar cliente por ID
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar cliente por CPF/CNPJ
     */
    public function buscarPorCpfCnpj($cpf_cnpj) {
        try {
            $sql = "SELECT * FROM clientes WHERE cpf_cnpj = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cpf_cnpj]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar cliente por CPF/CNPJ: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar novo cliente
     */
    public function criar($dados) {
        try {
            // Validar dados obrigatórios
            if (empty($dados['nome']) || empty($dados['cpf_cnpj']) || empty($dados['telefone'])) {
                return ['sucesso' => false, 'erro' => 'Campos obrigatórios não preenchidos'];
            }
            
            // Validar CPF/CNPJ
            $cpf_cnpj = preg_replace('/[^0-9]/', '', $dados['cpf_cnpj']);
            if (strlen($cpf_cnpj) == 11) {
                if (!validarCPF($cpf_cnpj)) {
                    return ['sucesso' => false, 'erro' => 'CPF inválido'];
                }
                $dados['tipo_pessoa'] = 'PF';
            } elseif (strlen($cpf_cnpj) == 14) {
                if (!validarCNPJ($cpf_cnpj)) {
                    return ['sucesso' => false, 'erro' => 'CNPJ inválido'];
                }
                $dados['tipo_pessoa'] = 'PJ';
            } else {
                return ['sucesso' => false, 'erro' => 'CPF/CNPJ deve ter 11 ou 14 dígitos'];
            }
            
            // Verificar se CPF/CNPJ já existe
            if ($this->buscarPorCpfCnpj($cpf_cnpj)) {
                return ['sucesso' => false, 'erro' => 'CPF/CNPJ já cadastrado'];
            }
            
            // Preparar dados para inserção
            $dados['cpf_cnpj'] = $cpf_cnpj;
            
            $sql = "INSERT INTO clientes (nome, tipo_pessoa, cpf_cnpj, rg_ie, telefone, email, endereco, cep, cidade, estado, data_nascimento, observacoes, programa_fidelidade) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['nome'],
                $dados['tipo_pessoa'],
                $dados['cpf_cnpj'],
                $dados['rg_ie'] ?? null,
                $dados['telefone'],
                $dados['email'] ?? null,
                $dados['endereco'] ?? null,
                $dados['cep'] ?? null,
                $dados['cidade'] ?? null,
                $dados['estado'] ?? null,
                $dados['data_nascimento'] ?? null,
                $dados['observacoes'] ?? null,
                isset($dados['programa_fidelidade']) ? 1 : 0
            ]);
            
            if ($resultado) {
                $id_cliente = $this->db->lastInsertId();
                
                // Registrar log
                registrarLog("Cliente criado", "clientes", $id_cliente, null, $dados);
                
                return ['sucesso' => true, 'id_cliente' => $id_cliente];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao criar cliente'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao criar cliente: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Atualizar cliente
     */
    public function atualizar($id, $dados) {
        try {
            // Buscar dados anteriores para log
            $dados_anteriores = $this->buscarPorId($id);
            if (!$dados_anteriores) {
                return ['sucesso' => false, 'erro' => 'Cliente não encontrado'];
            }
            
            // Validar CPF/CNPJ se foi alterado
            if (!empty($dados['cpf_cnpj'])) {
                $cpf_cnpj = preg_replace('/[^0-9]/', '', $dados['cpf_cnpj']);
                
                if (strlen($cpf_cnpj) == 11) {
                    if (!validarCPF($cpf_cnpj)) {
                        return ['sucesso' => false, 'erro' => 'CPF inválido'];
                    }
                    $dados['tipo_pessoa'] = 'PF';
                } elseif (strlen($cpf_cnpj) == 14) {
                    if (!validarCNPJ($cpf_cnpj)) {
                        return ['sucesso' => false, 'erro' => 'CNPJ inválido'];
                    }
                    $dados['tipo_pessoa'] = 'PJ';
                } else {
                    return ['sucesso' => false, 'erro' => 'CPF/CNPJ deve ter 11 ou 14 dígitos'];
                }
                
                // Verificar se CPF/CNPJ já existe em outro cliente
                $cliente_existente = $this->buscarPorCpfCnpj($cpf_cnpj);
                if ($cliente_existente && $cliente_existente['id_cliente'] != $id) {
                    return ['sucesso' => false, 'erro' => 'CPF/CNPJ já cadastrado para outro cliente'];
                }
                
                $dados['cpf_cnpj'] = $cpf_cnpj;
            }
            
            $sql = "UPDATE clientes SET 
                    nome = ?, tipo_pessoa = ?, cpf_cnpj = ?, rg_ie = ?, telefone = ?, 
                    email = ?, endereco = ?, cep = ?, cidade = ?, estado = ?, 
                    data_nascimento = ?, observacoes = ?, programa_fidelidade = ?, status = ?
                    WHERE id_cliente = ?";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $dados['nome'] ?? $dados_anteriores['nome'],
                $dados['tipo_pessoa'] ?? $dados_anteriores['tipo_pessoa'],
                $dados['cpf_cnpj'] ?? $dados_anteriores['cpf_cnpj'],
                $dados['rg_ie'] ?? $dados_anteriores['rg_ie'],
                $dados['telefone'] ?? $dados_anteriores['telefone'],
                $dados['email'] ?? $dados_anteriores['email'],
                $dados['endereco'] ?? $dados_anteriores['endereco'],
                $dados['cep'] ?? $dados_anteriores['cep'],
                $dados['cidade'] ?? $dados_anteriores['cidade'],
                $dados['estado'] ?? $dados_anteriores['estado'],
                $dados['data_nascimento'] ?? $dados_anteriores['data_nascimento'],
                $dados['observacoes'] ?? $dados_anteriores['observacoes'],
                isset($dados['programa_fidelidade']) ? 1 : ($dados_anteriores['programa_fidelidade'] ?? 0),
                $dados['status'] ?? $dados_anteriores['status'],
                $id
            ]);
            
            if ($resultado) {
                // Registrar log
                registrarLog("Cliente atualizado", "clientes", $id, $dados_anteriores, $dados);
                
                return ['sucesso' => true];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao atualizar cliente'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar cliente: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Excluir cliente (soft delete)
     */
    public function excluir($id) {
        try {
            // Verificar se cliente tem ordens de serviço
            $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $resultado = $stmt->fetch();
            
            if ($resultado['total'] > 0) {
                // Apenas inativar se tiver ordens de serviço
                return $this->atualizar($id, ['status' => 'inativo']);
            } else {
                // Excluir permanentemente se não tiver ordens de serviço
                $dados_anteriores = $this->buscarPorId($id);
                
                $sql = "DELETE FROM clientes WHERE id_cliente = ?";
                $stmt = $this->db->prepare($sql);
                $resultado = $stmt->execute([$id]);
                
                if ($resultado) {
                    // Registrar log
                    registrarLog("Cliente excluído", "clientes", $id, $dados_anteriores, null);
                    
                    return ['sucesso' => true];
                } else {
                    return ['sucesso' => false, 'erro' => 'Erro ao excluir cliente'];
                }
            }
            
        } catch (Exception $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Obter histórico de serviços do cliente
     */
    public function obterHistoricoServicos($id_cliente) {
        try {
            $sql = "SELECT os.*, v.placa, v.marca, v.modelo, 
                           GROUP_CONCAT(s.nome_servico SEPARATOR ', ') as servicos
                    FROM ordens_servico os
                    JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                    LEFT JOIN os_servicos oss ON os.id_os = oss.id_os
                    LEFT JOIN servicos s ON oss.id_servico = s.id_servico
                    WHERE os.id_cliente = ?
                    GROUP BY os.id_os
                    ORDER BY os.data_abertura DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_cliente]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao obter histórico de serviços: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Adicionar pontos de fidelidade
     */
    public function adicionarPontosFidelidade($id_cliente, $pontos) {
        try {
            $sql = "UPDATE clientes SET pontos_fidelidade = pontos_fidelidade + ? WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$pontos, $id_cliente]);
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar pontos de fidelidade: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Usar pontos de fidelidade
     */
    public function usarPontosFidelidade($id_cliente, $pontos) {
        try {
            // Verificar se cliente tem pontos suficientes
            $cliente = $this->buscarPorId($id_cliente);
            if (!$cliente || $cliente['pontos_fidelidade'] < $pontos) {
                return ['sucesso' => false, 'erro' => 'Pontos insuficientes'];
            }
            
            $sql = "UPDATE clientes SET pontos_fidelidade = pontos_fidelidade - ? WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$pontos, $id_cliente]);
            
            if ($resultado) {
                return ['sucesso' => true];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao usar pontos'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao usar pontos de fidelidade: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
}
?>
```


### 2.3 Classe de Ordens de Serviço (modules/ordens_servico/OrdemServico.php)

```php
<?php
/**
 * Classe para gerenciamento de ordens de serviço
 * Sistema de Lava Jato - VeltaCar Clone
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

class OrdemServico {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Listar ordens de serviço
     */
    public function listar($filtros = []) {
        try {
            $sql = "SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                           v.placa, v.marca, v.modelo, v.cor,
                           u.nome as usuario_nome
                    FROM ordens_servico os
                    JOIN clientes c ON os.id_cliente = c.id_cliente
                    JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                    LEFT JOIN usuarios u ON os.usuario_abertura = u.id_usuario
                    WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $sql .= " AND os.status = ?";
                $params[] = $filtros['status'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(os.data_abertura) >= ?";
                $params[] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(os.data_abertura) <= ?";
                $params[] = $filtros['data_fim'];
            }
            
            if (!empty($filtros['cliente'])) {
                $sql .= " AND c.nome LIKE ?";
                $params[] = '%' . $filtros['cliente'] . '%';
            }
            
            if (!empty($filtros['placa'])) {
                $sql .= " AND v.placa LIKE ?";
                $params[] = '%' . $filtros['placa'] . '%';
            }
            
            $sql .= " ORDER BY os.data_abertura DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro ao listar ordens de serviço: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar ordem de serviço por ID
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                           c.email as cliente_email, c.cpf_cnpj,
                           v.placa, v.marca, v.modelo, v.cor, v.ano,
                           u.nome as usuario_nome
                    FROM ordens_servico os
                    JOIN clientes c ON os.id_cliente = c.id_cliente
                    JOIN veiculos v ON os.id_veiculo = v.id_veiculo
                    LEFT JOIN usuarios u ON os.usuario_abertura = u.id_usuario
                    WHERE os.id_os = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $os = $stmt->fetch();
            
            if ($os) {
                // Buscar serviços da OS
                $sql_servicos = "SELECT oss.*, s.nome_servico, s.descricao
                                FROM os_servicos oss
                                JOIN servicos s ON oss.id_servico = s.id_servico
                                WHERE oss.id_os = ?";
                $stmt_servicos = $this->db->prepare($sql_servicos);
                $stmt_servicos->execute([$id]);
                $os['servicos'] = $stmt_servicos->fetchAll();
                
                // Buscar produtos da OS
                $sql_produtos = "SELECT osp.*, p.nome_produto, p.descricao
                                FROM os_produtos osp
                                JOIN produtos p ON osp.id_produto = p.id_produto
                                WHERE osp.id_os = ?";
                $stmt_produtos = $this->db->prepare($sql_produtos);
                $stmt_produtos->execute([$id]);
                $os['produtos'] = $stmt_produtos->fetchAll();
            }
            
            return $os;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar ordem de serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar nova ordem de serviço
     */
    public function criar($dados) {
        try {
            $this->db->beginTransaction();
            
            // Gerar código único da OS
            do {
                $codigo_os = gerarCodigo('OS', 6);
                $sql_check = "SELECT COUNT(*) FROM ordens_servico WHERE codigo_os = ?";
                $stmt_check = $this->db->prepare($sql_check);
                $stmt_check->execute([$codigo_os]);
            } while ($stmt_check->fetchColumn() > 0);
            
            // Inserir ordem de serviço
            $sql = "INSERT INTO ordens_servico (codigo_os, id_cliente, id_veiculo, id_agendamento, 
                                               vaga, km_veiculo, observacoes, usuario_abertura) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $codigo_os,
                $dados['id_cliente'],
                $dados['id_veiculo'],
                $dados['id_agendamento'] ?? null,
                $dados['vaga'] ?? null,
                $dados['km_veiculo'] ?? null,
                $dados['observacoes'] ?? null,
                $_SESSION['usuario_id']
            ]);
            
            if (!$resultado) {
                throw new Exception("Erro ao criar ordem de serviço");
            }
            
            $id_os = $this->db->lastInsertId();
            
            // Adicionar serviços se fornecidos
            if (!empty($dados['servicos'])) {
                foreach ($dados['servicos'] as $servico) {
                    $this->adicionarServico($id_os, $servico);
                }
            }
            
            // Adicionar produtos se fornecidos
            if (!empty($dados['produtos'])) {
                foreach ($dados['produtos'] as $produto) {
                    $this->adicionarProduto($id_os, $produto);
                }
            }
            
            // Recalcular totais
            $this->recalcularTotais($id_os);
            
            $this->db->commit();
            
            // Registrar log
            registrarLog("Ordem de serviço criada", "ordens_servico", $id_os, null, $dados);
            
            return ['sucesso' => true, 'id_os' => $id_os, 'codigo_os' => $codigo_os];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar ordem de serviço: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Adicionar serviço à ordem de serviço
     */
    public function adicionarServico($id_os, $dados_servico) {
        try {
            $sql = "INSERT INTO os_servicos (id_os, id_servico, quantidade, preco_unitario, subtotal, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $subtotal = $dados_servico['quantidade'] * $dados_servico['preco_unitario'];
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $id_os,
                $dados_servico['id_servico'],
                $dados_servico['quantidade'],
                $dados_servico['preco_unitario'],
                $subtotal,
                $dados_servico['observacoes'] ?? null
            ]);
            
            if ($resultado) {
                $this->recalcularTotais($id_os);
                return ['sucesso' => true];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao adicionar serviço'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar serviço: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Adicionar produto à ordem de serviço
     */
    public function adicionarProduto($id_os, $dados_produto) {
        try {
            $this->db->beginTransaction();
            
            // Verificar estoque disponível
            $sql_estoque = "SELECT estoque_atual FROM produtos WHERE id_produto = ?";
            $stmt_estoque = $this->db->prepare($sql_estoque);
            $stmt_estoque->execute([$dados_produto['id_produto']]);
            $produto = $stmt_estoque->fetch();
            
            if (!$produto || $produto['estoque_atual'] < $dados_produto['quantidade']) {
                throw new Exception("Estoque insuficiente");
            }
            
            // Adicionar produto à OS
            $sql = "INSERT INTO os_produtos (id_os, id_produto, quantidade, preco_unitario, subtotal, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $subtotal = $dados_produto['quantidade'] * $dados_produto['preco_unitario'];
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $id_os,
                $dados_produto['id_produto'],
                $dados_produto['quantidade'],
                $dados_produto['preco_unitario'],
                $subtotal,
                $dados_produto['observacoes'] ?? null
            ]);
            
            if (!$resultado) {
                throw new Exception("Erro ao adicionar produto");
            }
            
            // Atualizar estoque
            $sql_update_estoque = "UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id_produto = ?";
            $stmt_update_estoque = $this->db->prepare($sql_update_estoque);
            $stmt_update_estoque->execute([$dados_produto['quantidade'], $dados_produto['id_produto']]);
            
            // Registrar movimentação de estoque
            $sql_movimentacao = "INSERT INTO movimentacoes_estoque (id_produto, tipo_movimentacao, quantidade, valor_unitario, valor_total, motivo, documento, usuario_responsavel) 
                                VALUES (?, 'saida', ?, ?, ?, ?, ?, ?)";
            $stmt_movimentacao = $this->db->prepare($sql_movimentacao);
            $stmt_movimentacao->execute([
                $dados_produto['id_produto'],
                $dados_produto['quantidade'],
                $dados_produto['preco_unitario'],
                $subtotal,
                'Venda - Ordem de Serviço',
                'OS-' . $id_os,
                $_SESSION['usuario_id']
            ]);
            
            $this->recalcularTotais($id_os);
            
            $this->db->commit();
            
            return ['sucesso' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao adicionar produto: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Recalcular totais da ordem de serviço
     */
    private function recalcularTotais($id_os) {
        try {
            // Calcular total de serviços
            $sql_servicos = "SELECT COALESCE(SUM(subtotal), 0) as total_servicos FROM os_servicos WHERE id_os = ?";
            $stmt_servicos = $this->db->prepare($sql_servicos);
            $stmt_servicos->execute([$id_os]);
            $total_servicos = $stmt_servicos->fetchColumn();
            
            // Calcular total de produtos
            $sql_produtos = "SELECT COALESCE(SUM(subtotal), 0) as total_produtos FROM os_produtos WHERE id_os = ?";
            $stmt_produtos = $this->db->prepare($sql_produtos);
            $stmt_produtos->execute([$id_os]);
            $total_produtos = $stmt_produtos->fetchColumn();
            
            // Buscar desconto e acréscimo atuais
            $sql_os = "SELECT desconto, acrescimo FROM ordens_servico WHERE id_os = ?";
            $stmt_os = $this->db->prepare($sql_os);
            $stmt_os->execute([$id_os]);
            $os_data = $stmt_os->fetch();
            
            $desconto = $os_data['desconto'] ?? 0;
            $acrescimo = $os_data['acrescimo'] ?? 0;
            
            // Calcular total final
            $valor_total = ($total_servicos + $total_produtos) - $desconto + $acrescimo;
            
            // Atualizar ordem de serviço
            $sql_update = "UPDATE ordens_servico SET valor_servicos = ?, valor_produtos = ?, valor_total = ? WHERE id_os = ?";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->execute([$total_servicos, $total_produtos, $valor_total, $id_os]);
            
        } catch (Exception $e) {
            error_log("Erro ao recalcular totais: " . $e->getMessage());
        }
    }
    
    /**
     * Aplicar desconto ou acréscimo
     */
    public function aplicarDescontoAcrescimo($id_os, $desconto = 0, $acrescimo = 0) {
        try {
            $sql = "UPDATE ordens_servico SET desconto = ?, acrescimo = ? WHERE id_os = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$desconto, $acrescimo, $id_os]);
            
            if ($resultado) {
                $this->recalcularTotais($id_os);
                return ['sucesso' => true];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao aplicar desconto/acréscimo'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao aplicar desconto/acréscimo: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
    
    /**
     * Finalizar ordem de serviço
     */
    public function finalizar($id_os, $forma_pagamento, $observacoes_finais = null) {
        try {
            $this->db->beginTransaction();
            
            // Buscar dados da OS
            $os = $this->buscarPorId($id_os);
            if (!$os) {
                throw new Exception("Ordem de serviço não encontrada");
            }
            
            // Atualizar status e dados de finalização
            $sql = "UPDATE ordens_servico SET 
                    status = 'finalizada', 
                    data_conclusao = NOW(), 
                    forma_pagamento = ?, 
                    observacoes = CONCAT(COALESCE(observacoes, ''), ?, ?),
                    usuario_conclusao = ?
                    WHERE id_os = ?";
            
            $observacoes_concat = $observacoes_finais ? "\n\nObservações finais: " . $observacoes_finais : '';
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $forma_pagamento,
                $observacoes_concat,
                '',
                $_SESSION['usuario_id'],
                $id_os
            ]);
            
            if (!$resultado) {
                throw new Exception("Erro ao finalizar ordem de serviço");
            }
            
            // Registrar transação financeira (receita)
            $sql_financeiro = "INSERT INTO financeiro (tipo, categoria, descricao, valor, data_transacao, forma_pagamento, status, id_os, usuario_responsavel) 
                              VALUES ('receita', 'Serviços', ?, ?, CURDATE(), ?, 'pago', ?, ?)";
            
            $descricao_financeiro = "Ordem de Serviço " . $os['codigo_os'] . " - Cliente: " . $os['cliente_nome'];
            
            $stmt_financeiro = $this->db->prepare($sql_financeiro);
            $stmt_financeiro->execute([
                $descricao_financeiro,
                $os['valor_total'],
                $forma_pagamento,
                $id_os,
                $_SESSION['usuario_id']
            ]);
            
            // Adicionar pontos de fidelidade se cliente participa do programa
            if ($os['programa_fidelidade']) {
                $pontos = calcularPontosFidelidade($os['valor_total']);
                if ($pontos > 0) {
                    $sql_pontos = "UPDATE clientes SET pontos_fidelidade = pontos_fidelidade + ? WHERE id_cliente = ?";
                    $stmt_pontos = $this->db->prepare($sql_pontos);
                    $stmt_pontos->execute([$pontos, $os['id_cliente']]);
                }
            }
            
            // Atualizar agendamento se existir
            if ($os['id_agendamento']) {
                $sql_agendamento = "UPDATE agendamentos SET status = 'concluido' WHERE id_agendamento = ?";
                $stmt_agendamento = $this->db->prepare($sql_agendamento);
                $stmt_agendamento->execute([$os['id_agendamento']]);
            }
            
            $this->db->commit();
            
            // Registrar log
            registrarLog("Ordem de serviço finalizada", "ordens_servico", $id_os, null, [
                'forma_pagamento' => $forma_pagamento,
                'observacoes_finais' => $observacoes_finais
            ]);
            
            // Enviar notificação ao cliente (opcional)
            $this->enviarNotificacaoFinalizacao($os);
            
            return ['sucesso' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao finalizar ordem de serviço: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar notificação de finalização ao cliente
     */
    private function enviarNotificacaoFinalizacao($os) {
        try {
            $mensagem = "Olá {$os['cliente_nome']}! Seu veículo {$os['marca']} {$os['modelo']} (placa {$os['placa']}) está pronto! " .
                       "Ordem de Serviço: {$os['codigo_os']} - Total: " . formatarMoeda($os['valor_total']) . ". " .
                       "Obrigado pela preferência!";
            
            // Tentar enviar WhatsApp primeiro, depois SMS como fallback
            $resultado_whatsapp = enviarWhatsApp($os['cliente_telefone'], $mensagem);
            
            if (!$resultado_whatsapp['sucesso']) {
                enviarSMS($os['cliente_telefone'], $mensagem);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao enviar notificação: " . $e->getMessage());
        }
    }
    
    /**
     * Cancelar ordem de serviço
     */
    public function cancelar($id_os, $motivo) {
        try {
            $this->db->beginTransaction();
            
            // Buscar dados da OS
            $os = $this->buscarPorId($id_os);
            if (!$os) {
                throw new Exception("Ordem de serviço não encontrada");
            }
            
            // Verificar se pode ser cancelada
            if ($os['status'] == 'finalizada') {
                throw new Exception("Não é possível cancelar uma ordem de serviço finalizada");
            }
            
            // Reverter estoque dos produtos
            foreach ($os['produtos'] as $produto) {
                $sql_estoque = "UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id_produto = ?";
                $stmt_estoque = $this->db->prepare($sql_estoque);
                $stmt_estoque->execute([$produto['quantidade'], $produto['id_produto']]);
                
                // Registrar movimentação de estoque
                $sql_movimentacao = "INSERT INTO movimentacoes_estoque (id_produto, tipo_movimentacao, quantidade, motivo, documento, usuario_responsavel) 
                                    VALUES (?, 'entrada', ?, ?, ?, ?)";
                $stmt_movimentacao = $this->db->prepare($sql_movimentacao);
                $stmt_movimentacao->execute([
                    $produto['id_produto'],
                    $produto['quantidade'],
                    'Cancelamento OS - ' . $motivo,
                    'OS-' . $id_os,
                    $_SESSION['usuario_id']
                ]);
            }
            
            // Atualizar status da OS
            $sql = "UPDATE ordens_servico SET 
                    status = 'cancelada', 
                    observacoes = CONCAT(COALESCE(observacoes, ''), ?, ?)
                    WHERE id_os = ?";
            
            $observacao_cancelamento = "\n\nCancelada em " . date('d/m/Y H:i:s') . " - Motivo: " . $motivo;
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute(['', $observacao_cancelamento, $id_os]);
            
            if (!$resultado) {
                throw new Exception("Erro ao cancelar ordem de serviço");
            }
            
            // Atualizar agendamento se existir
            if ($os['id_agendamento']) {
                $sql_agendamento = "UPDATE agendamentos SET status = 'cancelado' WHERE id_agendamento = ?";
                $stmt_agendamento = $this->db->prepare($sql_agendamento);
                $stmt_agendamento->execute([$os['id_agendamento']]);
            }
            
            $this->db->commit();
            
            // Registrar log
            registrarLog("Ordem de serviço cancelada", "ordens_servico", $id_os, null, ['motivo' => $motivo]);
            
            return ['sucesso' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao cancelar ordem de serviço: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Salvar checklist de avarias
     */
    public function salvarChecklistAvarias($id_os, $checklist_data, $imagens = []) {
        try {
            // Processar upload de imagens
            $imagens_salvas = [];
            if (!empty($imagens)) {
                $diretorio_upload = __DIR__ . '/../../uploads/checklist_avarias/' . $id_os;
                
                foreach ($imagens as $imagem) {
                    $resultado_upload = uploadArquivo($imagem, $diretorio_upload, ['jpg', 'jpeg', 'png']);
                    if ($resultado_upload['sucesso']) {
                        $imagens_salvas[] = $resultado_upload['arquivo'];
                    }
                }
            }
            
            // Preparar dados do checklist
            $checklist = [
                'data_checklist' => date('Y-m-d H:i:s'),
                'usuario_responsavel' => $_SESSION['usuario_id'],
                'avarias' => $checklist_data,
                'imagens' => $imagens_salvas
            ];
            
            // Salvar no banco
            $sql = "UPDATE ordens_servico SET checklist_avarias = ? WHERE id_os = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([json_encode($checklist), $id_os]);
            
            if ($resultado) {
                // Registrar log
                registrarLog("Checklist de avarias salvo", "ordens_servico", $id_os, null, $checklist);
                
                return ['sucesso' => true, 'imagens_salvas' => $imagens_salvas];
            } else {
                return ['sucesso' => false, 'erro' => 'Erro ao salvar checklist'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao salvar checklist de avarias: " . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno do sistema'];
        }
    }
}
?>
```

## 3. Interfaces HTML/CSS/JavaScript

### 3.1 Layout Principal (includes/header.php)

```php
<?php
// Verificar se usuário está logado
verificarLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'Sistema de Lava Jato'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS Customizado -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/responsive.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-car-wash me-2"></i>
                <?php echo obterConfiguracao('nome_empresa', 'Lava Jato VeltaCar'); ?>
            </a>
            
            <!-- Toggle para mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menu de navegação -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Dropdown Clientes -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-1"></i> Clientes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/clientes/listar.php">Listar Clientes</a></li>
                            <li><a class="dropdown-item" href="modules/clientes/cadastrar.php">Novo Cliente</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/veiculos/listar.php">Veículos</a></li>
                        </ul>
                    </li>
                    
                    <!-- Dropdown Serviços -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tools me-1"></i> Serviços
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/agendamentos/listar.php">Agendamentos</a></li>
                            <li><a class="dropdown-item" href="modules/agendamentos/calendario.php">Calendário</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/ordens_servico/listar.php">Ordens de Serviço</a></li>
                            <li><a class="dropdown-item" href="modules/ordens_servico/nova.php">Nova OS</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/servicos/listar.php">Cadastro de Serviços</a></li>
                        </ul>
                    </li>
                    
                    <!-- Dropdown Estoque -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-boxes me-1"></i> Estoque
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/estoque/produtos.php">Produtos</a></li>
                            <li><a class="dropdown-item" href="modules/estoque/movimentacoes.php">Movimentações</a></li>
                            <li><a class="dropdown-item" href="modules/estoque/relatorio.php">Relatório de Estoque</a></li>
                        </ul>
                    </li>
                    
                    <!-- Dropdown Financeiro -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-money-bill-wave me-1"></i> Financeiro
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/financeiro/fluxo_caixa.php">Fluxo de Caixa</a></li>
                            <li><a class="dropdown-item" href="modules/financeiro/receitas.php">Receitas</a></li>
                            <li><a class="dropdown-item" href="modules/financeiro/despesas.php">Despesas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/orcamentos/listar.php">Orçamentos</a></li>
                            <li><a class="dropdown-item" href="modules/cupons/listar.php">Cupons de Desconto</a></li>
                        </ul>
                    </li>
                    
                    <!-- Dropdown Relatórios -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar me-1"></i> Relatórios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="modules/relatorios/vendas.php">Vendas</a></li>
                            <li><a class="dropdown-item" href="modules/relatorios/servicos.php">Serviços</a></li>
                            <li><a class="dropdown-item" href="modules/relatorios/clientes.php">Clientes</a></li>
                            <li><a class="dropdown-item" href="modules/relatorios/financeiro.php">Financeiro</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Menu do usuário -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo $_SESSION['usuario_nome']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="modules/usuarios/perfil.php">Meu Perfil</a></li>
                            <?php if ($_SESSION['nivel_acesso'] == 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="modules/usuarios/listar.php">Usuários</a></li>
                            <li><a class="dropdown-item" href="modules/configuracoes/sistema.php">Configurações</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Container principal -->
    <div class="container-fluid mt-5 pt-3">
        <div class="row">
            <!-- Sidebar (opcional para algumas páginas) -->
            <?php if (isset($mostrar_sidebar) && $mostrar_sidebar): ?>
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include 'sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 main-content">
            <?php else: ?>
            <div class="col-12 main-content">
            <?php endif; ?>
```

### 3.2 CSS Customizado (assets/css/style.css)

```css
/* Sistema de Lava Jato - Estilos Customizados */

:root {
    --primary-color: #6f42c1;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #0dcaf0;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --border-radius: 0.375rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Layout geral */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    padding-top: 76px; /* Altura da navbar fixa */
}

.main-content {
    padding: 20px;
}

/* Cards customizados */
.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 20px;
}

.card-header {
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
    padding: 15px 20px;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
}

.card-body {
    padding: 20px;
}

/* Botões customizados */
.btn {
    border-radius: var(--border-radius);
    font-weight: 500;
    padding: 8px 16px;
    transition: all 0.2s ease-in-out;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #5a359a;
    border-color: #5a359a;
    transform: translateY(-1px);
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.875rem;
}

/* Tabelas */
.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: var(--dark-color);
    padding: 12px;
}

.table td {
    padding: 12px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}

/* Status badges */
.badge {
    font-size: 0.75rem;
    padding: 6px 10px;
    border-radius: 20px;
}

.status-pendente {
    background-color: var(--warning-color);
    color: var(--dark-color);
}

.status-confirmado {
    background-color: var(--info-color);
    color: var(--dark-color);
}

.status-em-andamento {
    background-color: var(--primary-color);
    color: white;
}

.status-concluido,
.status-finalizada {
    background-color: var(--success-color);
    color: white;
}

.status-cancelado,
.status-cancelada {
    background-color: var(--danger-color);
    color: white;
}

.status-ativo {
    background-color: var(--success-color);
    color: white;
}

.status-inativo {
    background-color: var(--secondary-color);
    color: white;
}

/* Formulários */
.form-control,
.form-select {
    border-radius: var(--border-radius);
    border: 1px solid #ced4da;
    padding: 10px 12px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.form-label {
    font-weight: 500;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.input-group-text {
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    color: var(--secondary-color);
}

/* Dashboard cards */
.dashboard-card {
    background: linear-gradient(135deg, var(--primary-color), #8b5fbf);
    color: white;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.2s ease-in-out;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.dashboard-card .card-icon {
    font-size: 2.5rem;
    opacity: 0.8;
    margin-bottom: 10px;
}

.dashboard-card .card-title {
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 5px;
    opacity: 0.9;
}

.dashboard-card .card-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

/* Calendário de agendamentos */
.calendar-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.calendar-nav {
    display: flex;
    gap: 10px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #e9ecef;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.calendar-day {
    background: white;
    padding: 10px;
    min-height: 100px;
    position: relative;
}

.calendar-day-header {
    background: #f8f9fa;
    padding: 10px;
    text-align: center;
    font-weight: 600;
    color: var(--secondary-color);
}

.calendar-day-number {
    font-weight: 600;
    margin-bottom: 5px;
}

.calendar-appointment {
    background: var(--primary-color);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.75rem;
    margin-bottom: 2px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-appointment:hover {
    background: #5a359a;
}

/* Checklist de avarias */
.checklist-container {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 20px;
}

.checklist-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #e9ecef;
    border-radius: var(--border-radius);
    margin-bottom: 10px;
    transition: background-color 0.2s;
}

.checklist-item:hover {
    background-color: #f8f9fa;
}

.checklist-item input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.checklist-item.checked {
    background-color: #fff3cd;
    border-color: var(--warning-color);
}

.image-upload-area {
    border: 2px dashed #ced4da;
    border-radius: var(--border-radius);
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    transition: border-color 0.2s;
    cursor: pointer;
}

.image-upload-area:hover {
    border-color: var(--primary-color);
    background-color: rgba(111, 66, 193, 0.05);
}

.image-upload-area.dragover {
    border-color: var(--primary-color);
    background-color: rgba(111, 66, 193, 0.1);
}

/* Sidebar */
.sidebar {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    height: fit-content;
    position: sticky;
    top: 90px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu a {
    display: block;
    padding: 10px 15px;
    color: var(--dark-color);
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: background-color 0.2s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background-color: var(--primary-color);
    color: white;
}

/* Alertas customizados */
.alert {
    border: none;
    border-radius: var(--border-radius);
    padding: 15px 20px;
    margin-bottom: 20px;
}

.alert-dismissible .btn-close {
    padding: 0.75rem 1rem;
}

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Modais customizados */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.modal-header .btn-close {
    filter: invert(1);
}

/* Paginação */
.pagination {
    margin: 0;
}

.page-link {
    color: var(--primary-color);
    border-color: #dee2e6;
    padding: 8px 12px;
}

.page-link:hover {
    color: #5a359a;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Filtros */
.filters-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    margin-bottom: 20px;
}

.filters-row {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

/* Estatísticas */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    text-align: center;
    transition: transform 0.2s ease-in-out;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--secondary-color);
    font-size: 0.9rem;
    font-weight: 500;
}

/* Utilitários */
.text-primary {
    color: var(--primary-color) !important;
}

.bg-primary {
    background-color: var(--primary-color) !important;
}

.border-primary {
    border-color: var(--primary-color) !important;
}

.shadow-sm {
    box-shadow: var(--box-shadow) !important;
}

.rounded {
    border-radius: var(--border-radius) !important;
}

/* Animações */
.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.slide-in {
    animation: slideIn 0.3s ease-in-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}
```


### 3.3 CSS Responsivo (assets/css/responsive.css)

```css
/* Sistema de Lava Jato - Estilos Responsivos */

/* Tablets (768px e acima) */
@media (min-width: 768px) {
    .main-content {
        padding: 30px;
    }
    
    .dashboard-card {
        padding: 25px;
    }
    
    .filters-row {
        flex-wrap: nowrap;
    }
    
    .calendar-day {
        min-height: 120px;
    }
}

/* Desktop pequeno (992px e acima) */
@media (min-width: 992px) {
    .stats-container {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .filters-container {
        padding: 25px;
    }
}

/* Desktop grande (1200px e acima) */
@media (min-width: 1200px) {
    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .calendar-day {
        min-height: 140px;
    }
}

/* Mobile (767px e abaixo) */
@media (max-width: 767px) {
    body {
        padding-top: 60px;
    }
    
    .main-content {
        padding: 15px;
    }
    
    /* Navbar mobile */
    .navbar-brand {
        font-size: 1rem;
    }
    
    .navbar-nav .dropdown-menu {
        position: static;
        float: none;
        width: auto;
        margin-top: 0;
        background-color: transparent;
        border: 0;
        box-shadow: none;
    }
    
    .navbar-nav .dropdown-item {
        color: rgba(255, 255, 255, 0.75);
        padding: 8px 20px;
    }
    
    .navbar-nav .dropdown-item:hover {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Cards mobile */
    .card {
        margin-bottom: 15px;
    }
    
    .card-header {
        padding: 12px 15px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    /* Dashboard mobile */
    .dashboard-card {
        padding: 20px 15px;
        text-align: center;
    }
    
    .dashboard-card .card-value {
        font-size: 1.5rem;
    }
    
    /* Tabelas mobile */
    .table-responsive {
        border: none;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 8px;
    }
    
    /* Ocultar colunas menos importantes em mobile */
    .table .d-none-mobile {
        display: none !important;
    }
    
    /* Botões mobile */
    .btn {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
    
    .btn-sm {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
    
    /* Formulários mobile */
    .form-control,
    .form-select {
        padding: 12px;
        font-size: 16px; /* Evita zoom no iOS */
    }
    
    .form-label {
        font-size: 0.9rem;
    }
    
    /* Filtros mobile */
    .filters-container {
        padding: 15px;
    }
    
    .filters-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-group {
        min-width: auto;
        width: 100%;
    }
    
    /* Calendário mobile */
    .calendar-container {
        padding: 15px;
    }
    
    .calendar-grid {
        font-size: 0.8rem;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 5px;
    }
    
    .calendar-appointment {
        font-size: 0.7rem;
        padding: 1px 4px;
    }
    
    /* Modais mobile */
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-content {
        border-radius: 10px;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .modal-footer {
        padding: 10px 15px;
    }
    
    /* Estatísticas mobile */
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    /* Checklist mobile */
    .checklist-container {
        padding: 15px;
    }
    
    .checklist-item {
        padding: 8px;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .checklist-item input[type="checkbox"] {
        margin-bottom: 5px;
    }
    
    /* Upload de imagens mobile */
    .image-upload-area {
        padding: 20px;
    }
    
    /* Paginação mobile */
    .pagination {
        justify-content: center;
    }
    
    .page-link {
        padding: 6px 10px;
        font-size: 0.875rem;
    }
    
    /* Sidebar mobile (se usado) */
    .sidebar {
        position: static;
        margin-bottom: 20px;
    }
    
    /* Alertas mobile */
    .alert {
        padding: 12px 15px;
        font-size: 0.9rem;
    }
    
    /* Utilitários mobile */
    .d-mobile-block {
        display: block !important;
    }
    
    .d-mobile-none {
        display: none !important;
    }
    
    .text-mobile-center {
        text-align: center !important;
    }
    
    .mb-mobile-3 {
        margin-bottom: 1rem !important;
    }
    
    .p-mobile-2 {
        padding: 0.5rem !important;
    }
}

/* Mobile muito pequeno (480px e abaixo) */
@media (max-width: 480px) {
    .main-content {
        padding: 10px;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .dashboard-card .card-value {
        font-size: 1.25rem;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .btn-group-mobile .btn {
        width: auto;
        margin-bottom: 0;
        margin-right: 5px;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .calendar-day {
        min-height: 60px;
        padding: 3px;
    }
    
    .calendar-day-number {
        font-size: 0.8rem;
    }
}

/* Orientação paisagem em mobile */
@media (max-width: 767px) and (orientation: landscape) {
    body {
        padding-top: 50px;
    }
    
    .navbar {
        min-height: 50px;
    }
    
    .navbar-brand {
        font-size: 0.9rem;
    }
    
    .calendar-day {
        min-height: 50px;
    }
}

/* Impressão */
@media print {
    .navbar,
    .sidebar,
    .btn,
    .pagination,
    .filters-container {
        display: none !important;
    }
    
    body {
        padding-top: 0;
        background: white;
    }
    
    .main-content {
        padding: 0;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
        page-break-inside: avoid;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .table th,
    .table td {
        padding: 6px;
    }
    
    .page-break {
        page-break-before: always;
    }
    
    .no-print {
        display: none !important;
    }
}

/* Acessibilidade */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Alto contraste */
@media (prefers-contrast: high) {
    .card {
        border: 2px solid #000;
    }
    
    .btn {
        border: 2px solid;
    }
    
    .form-control,
    .form-select {
        border: 2px solid #000;
    }
}

/* Modo escuro (se suportado pelo navegador) */
@media (prefers-color-scheme: dark) {
    :root {
        --primary-color: #8b5fbf;
        --light-color: #212529;
        --dark-color: #f8f9fa;
    }
    
    body {
        background-color: #1a1a1a;
        color: #f8f9fa;
    }
    
    .card {
        background-color: #2d2d2d;
        color: #f8f9fa;
    }
    
    .table {
        color: #f8f9fa;
    }
    
    .table th {
        background-color: #3d3d3d;
    }
    
    .form-control,
    .form-select {
        background-color: #2d2d2d;
        border-color: #495057;
        color: #f8f9fa;
    }
    
    .form-control:focus,
    .form-select:focus {
        background-color: #2d2d2d;
        border-color: var(--primary-color);
        color: #f8f9fa;
    }
}
```

### 3.4 JavaScript Principal (assets/js/main.js)

```javascript
/**
 * Sistema de Lava Jato - JavaScript Principal
 * Funcionalidades gerais do sistema
 */

// Configurações globais
const CONFIG = {
    baseUrl: window.location.origin,
    apiUrl: window.location.origin + '/api',
    dateFormat: 'dd/mm/yyyy',
    timeFormat: 'HH:mm',
    currency: 'BRL',
    locale: 'pt-BR'
};

// Utilitários gerais
const Utils = {
    /**
     * Formatar moeda brasileira
     */
    formatCurrency: function(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    /**
     * Formatar data brasileira
     */
    formatDate: function(date, includeTime = false) {
        const options = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        };
        
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }
        
        return new Intl.DateTimeFormat('pt-BR', options).format(new Date(date));
    },

    /**
     * Formatar CPF/CNPJ
     */
    formatDocument: function(value) {
        const numbers = value.replace(/\D/g, '');
        
        if (numbers.length <= 11) {
            // CPF
            return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else {
            // CNPJ
            return numbers.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
    },

    /**
     * Formatar telefone
     */
    formatPhone: function(value) {
        const numbers = value.replace(/\D/g, '');
        
        if (numbers.length === 11) {
            return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (numbers.length === 10) {
            return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        
        return value;
    },

    /**
     * Validar CPF
     */
    validateCPF: function(cpf) {
        cpf = cpf.replace(/\D/g, '');
        
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
            return false;
        }
        
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        
        let remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) return false;
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        
        return remainder === parseInt(cpf.charAt(10));
    },

    /**
     * Validar CNPJ
     */
    validateCNPJ: function(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
            return false;
        }
        
        let length = cnpj.length - 2;
        let numbers = cnpj.substring(0, length);
        let digits = cnpj.substring(length);
        let sum = 0;
        let pos = length - 7;
        
        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result !== parseInt(digits.charAt(0))) return false;
        
        length = length + 1;
        numbers = cnpj.substring(0, length);
        sum = 0;
        pos = length - 7;
        
        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        
        return result === parseInt(digits.charAt(1));
    },

    /**
     * Debounce para otimizar chamadas de função
     */
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    },

    /**
     * Mostrar loading
     */
    showLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.innerHTML = '<div class="text-center"><div class="loading-spinner"></div> Carregando...</div>';
        }
    },

    /**
     * Esconder loading
     */
    hideLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.innerHTML = '';
        }
    },

    /**
     * Mostrar notificação toast
     */
    showToast: function(message, type = 'info', duration = 5000) {
        // Criar container de toasts se não existir
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Criar toast
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Inicializar e mostrar toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        
        toast.show();
        
        // Remover toast do DOM após esconder
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    },

    /**
     * Confirmar ação
     */
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    /**
     * Fazer requisição AJAX
     */
    ajax: function(options) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const config = Object.assign({}, defaults, options);
        
        return fetch(config.url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Erro AJAX:', error);
                Utils.showToast('Erro na comunicação com o servidor', 'danger');
                throw error;
            });
    }
};

// Máscaras de input
const InputMasks = {
    init: function() {
        // Máscara de CPF/CNPJ
        document.querySelectorAll('[data-mask="cpf-cnpj"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    // CPF
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                } else {
                    // CNPJ
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                }
                
                this.value = value;
            });
        });
        
        // Máscara de telefone
        document.querySelectorAll('[data-mask="phone"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                
                this.value = value;
            });
        });
        
        // Máscara de CEP
        document.querySelectorAll('[data-mask="cep"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                this.value = value;
            });
        });
        
        // Máscara de moeda
        document.querySelectorAll('[data-mask="currency"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2);
                value = value.replace('.', ',');
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                this.value = 'R$ ' + value;
            });
        });
        
        // Máscara de placa de veículo
        document.querySelectorAll('[data-mask="plate"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                
                if (value.length <= 7) {
                    value = value.replace(/(\w{3})(\w)/, '$1-$2');
                }
                
                this.value = value;
            });
        });
    }
};

// Validação de formulários
const FormValidation = {
    init: function() {
        // Validação em tempo real
        document.querySelectorAll('form[data-validate="true"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!FormValidation.validateForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                this.classList.add('was-validated');
            });
            
            // Validação de campos individuais
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('blur', function() {
                    FormValidation.validateField(this);
                });
            });
        });
    },
    
    validateForm: function(form) {
        let isValid = true;
        
        form.querySelectorAll('input, select, textarea').forEach(field => {
            if (!FormValidation.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField: function(field) {
        let isValid = true;
        const value = field.value.trim();
        
        // Validação de campo obrigatório
        if (field.hasAttribute('required') && !value) {
            FormValidation.setFieldError(field, 'Este campo é obrigatório');
            isValid = false;
        }
        
        // Validação de CPF
        if (field.hasAttribute('data-validate-cpf') && value) {
            if (!Utils.validateCPF(value)) {
                FormValidation.setFieldError(field, 'CPF inválido');
                isValid = false;
            }
        }
        
        // Validação de CNPJ
        if (field.hasAttribute('data-validate-cnpj') && value) {
            if (!Utils.validateCNPJ(value)) {
                FormValidation.setFieldError(field, 'CNPJ inválido');
                isValid = false;
            }
        }
        
        // Validação de email
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                FormValidation.setFieldError(field, 'Email inválido');
                isValid = false;
            }
        }
        
        // Validação de telefone
        if (field.hasAttribute('data-validate-phone') && value) {
            const phoneRegex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
            if (!phoneRegex.test(value)) {
                FormValidation.setFieldError(field, 'Telefone inválido');
                isValid = false;
            }
        }
        
        if (isValid) {
            FormValidation.clearFieldError(field);
        }
        
        return isValid;
    },
    
    setFieldError: function(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = message;
    },
    
    clearFieldError: function(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
};

// Busca de CEP
const CEPSearch = {
    init: function() {
        document.querySelectorAll('[data-cep-search]').forEach(input => {
            input.addEventListener('blur', function() {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    CEPSearch.searchCEP(cep, this);
                }
            });
        });
    },
    
    searchCEP: function(cep, input) {
        const form = input.closest('form');
        
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    // Preencher campos automaticamente
                    const endereco = form.querySelector('[name="endereco"]');
                    const cidade = form.querySelector('[name="cidade"]');
                    const estado = form.querySelector('[name="estado"]');
                    const bairro = form.querySelector('[name="bairro"]');
                    
                    if (endereco) endereco.value = data.logradouro;
                    if (cidade) cidade.value = data.localidade;
                    if (estado) estado.value = data.uf;
                    if (bairro) bairro.value = data.bairro;
                } else {
                    Utils.showToast('CEP não encontrado', 'warning');
                }
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                Utils.showToast('Erro ao buscar CEP', 'danger');
            });
    }
};

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    InputMasks.init();
    FormValidation.init();
    CEPSearch.init();
    
    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers do Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts após 5 segundos
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Confirmação para botões de exclusão
    document.querySelectorAll('[data-confirm]').forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Tem certeza que deseja continuar?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Busca em tempo real em tabelas
    document.querySelectorAll('[data-table-search]').forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('input', Utils.debounce(function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }, 300));
        }
    });
    
    // Auto-refresh para páginas específicas
    if (document.body.hasAttribute('data-auto-refresh')) {
        const interval = parseInt(document.body.getAttribute('data-auto-refresh')) || 30000;
        setInterval(() => {
            location.reload();
        }, interval);
    }
});

// Exportar utilitários para uso global
window.Utils = Utils;
window.InputMasks = InputMasks;
window.FormValidation = FormValidation;
window.CEPSearch = CEPSearch;
```


### 3.5 Página de Login (login.php)

```php
<?php
session_start();

// Redirecionar se já estiver logado
if (isset($_SESSION['usuario_logado'])) {
    header('Location: dashboard.php');
    exit();
}

require_once 'config/database.php';
require_once 'includes/functions.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizar($_POST['email']);
    $senha = $_POST['senha'];
    
    if (!empty($email) && !empty($senha)) {
        try {
            $db = getDB();
            
            $sql = "SELECT * FROM usuarios WHERE email = ? AND status = 'ativo'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario_logado'] = true;
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
                
                // Atualizar último login
                $sql_update = "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->execute([$usuario['id_usuario']]);
                
                // Registrar log
                registrarLog("Login realizado", "usuarios", $usuario['id_usuario']);
                
                header('Location: dashboard.php');
                exit();
            } else {
                $erro = 'Email ou senha incorretos';
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $erro = 'Erro interno do sistema';
        }
    } else {
        $erro = 'Preencha todos os campos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Lava Jato</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6f42c1, #8b5fbf);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #6f42c1, #8b5fbf);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #6f42c1, #8b5fbf);
            border: none;
            border-radius: 25px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-car-wash"></i>
            <h3>Sistema de Lava Jato</h3>
            <p class="mb-0">Faça login para continuar</p>
        </div>
        
        <div class="login-body">
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate="true">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" name="email" 
                               placeholder="Seu email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" name="senha" 
                               placeholder="Sua senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Entrar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Acesso seguro e protegido
                </small>
            </div>
        </div>
    </div>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
```

### 3.6 Dashboard Principal (dashboard.php)

```php
<?php
$titulo_pagina = 'Dashboard - Sistema de Lava Jato';
require_once 'includes/header.php';
require_once 'modules/ordens_servico/OrdemServico.php';
require_once 'modules/clientes/Cliente.php';

// Verificar nível de acesso
verificarLogin();

// Instanciar classes
$ordemServico = new OrdemServico();
$cliente = new Cliente();

// Buscar estatísticas do dia
$hoje = date('Y-m-d');

try {
    $db = getDB();
    
    // Ordens de serviço do dia
    $sql_os_hoje = "SELECT COUNT(*) as total FROM ordens_servico WHERE DATE(data_abertura) = ?";
    $stmt_os_hoje = $db->prepare($sql_os_hoje);
    $stmt_os_hoje->execute([$hoje]);
    $os_hoje = $stmt_os_hoje->fetchColumn();
    
    // Faturamento do dia
    $sql_faturamento = "SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico 
                       WHERE DATE(data_conclusao) = ? AND status = 'finalizada'";
    $stmt_faturamento = $db->prepare($sql_faturamento);
    $stmt_faturamento->execute([$hoje]);
    $faturamento_hoje = $stmt_faturamento->fetchColumn();
    
    // Agendamentos do dia
    $sql_agendamentos = "SELECT COUNT(*) as total FROM agendamentos 
                        WHERE DATE(data_agendamento) = ? AND status IN ('confirmado', 'pendente')";
    $stmt_agendamentos = $db->prepare($sql_agendamentos);
    $stmt_agendamentos->execute([$hoje]);
    $agendamentos_hoje = $stmt_agendamentos->fetchColumn();
    
    // Total de clientes
    $sql_clientes = "SELECT COUNT(*) as total FROM clientes WHERE status = 'ativo'";
    $stmt_clientes = $db->prepare($sql_clientes);
    $stmt_clientes->execute();
    $total_clientes = $stmt_clientes->fetchColumn();
    
    // Ordens de serviço em andamento
    $sql_os_andamento = "SELECT COUNT(*) as total FROM ordens_servico WHERE status = 'em_andamento'";
    $stmt_os_andamento = $db->prepare($sql_os_andamento);
    $stmt_os_andamento->execute();
    $os_andamento = $stmt_os_andamento->fetchColumn();
    
    // Produtos com estoque baixo
    $sql_estoque_baixo = "SELECT COUNT(*) as total FROM produtos 
                         WHERE estoque_atual <= estoque_minimo AND status = 'ativo'";
    $stmt_estoque_baixo = $db->prepare($sql_estoque_baixo);
    $stmt_estoque_baixo->execute();
    $estoque_baixo = $stmt_estoque_baixo->fetchColumn();
    
    // Últimas ordens de serviço
    $ultimas_os = $ordemServico->listar(['limit' => 5]);
    
    // Agendamentos próximos
    $sql_proximos_agendamentos = "SELECT a.*, c.nome as cliente_nome, v.placa, v.marca, v.modelo
                                 FROM agendamentos a
                                 JOIN clientes c ON a.id_cliente = c.id_cliente
                                 JOIN veiculos v ON a.id_veiculo = v.id_veiculo
                                 WHERE a.data_agendamento >= NOW() AND a.status = 'confirmado'
                                 ORDER BY a.data_agendamento ASC
                                 LIMIT 5";
    $stmt_proximos = $db->prepare($sql_proximos_agendamentos);
    $stmt_proximos->execute();
    $proximos_agendamentos = $stmt_proximos->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    $os_hoje = $faturamento_hoje = $agendamentos_hoje = $total_clientes = $os_andamento = $estoque_baixo = 0;
    $ultimas_os = $proximos_agendamentos = [];
}
?>

<div class="container-fluid">
    <!-- Cabeçalho do Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </h1>
            <p class="text-muted">Visão geral do sistema - <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>
    
    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">OS Hoje</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $os_hoje; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Faturamento</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo formatarMoeda($faturamento_hoje); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Agendamentos</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $agendamentos_hoje; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Clientes</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $total_clientes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Em Andamento</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $os_andamento; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card <?php echo $estoque_baixo > 0 ? 'bg-danger' : 'bg-success'; ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Estoque Baixo</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $estoque_baixo; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="row">
        <!-- Últimas Ordens de Serviço -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Últimas Ordens de Serviço
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($ultimas_os)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>OS</th>
                                        <th>Cliente</th>
                                        <th>Veículo</th>
                                        <th>Status</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_os as $os): ?>
                                        <tr>
                                            <td><strong><?php echo $os['codigo_os']; ?></strong></td>
                                            <td><?php echo $os['cliente_nome']; ?></td>
                                            <td><?php echo $os['marca'] . ' ' . $os['modelo'] . ' (' . $os['placa'] . ')'; ?></td>
                                            <td>
                                                <span class="badge status-<?php echo $os['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $os['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatarMoeda($os['valor_total']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($os['data_abertura'])); ?></td>
                                            <td>
                                                <a href="modules/ordens_servico/visualizar.php?id=<?php echo $os['id_os']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="modules/ordens_servico/listar.php" class="btn btn-primary">
                                Ver Todas as OS
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhuma ordem de serviço encontrada</p>
                            <a href="modules/ordens_servico/nova.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Nova OS
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Próximos Agendamentos -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Próximos Agendamentos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($proximos_agendamentos)): ?>
                        <?php foreach ($proximos_agendamentos as $agendamento): ?>
                            <div class="d-flex align-items-center mb-3 p-2 border rounded">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-primary fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?php echo $agendamento['cliente_nome']; ?></h6>
                                    <p class="mb-1 text-muted small">
                                        <?php echo $agendamento['marca'] . ' ' . $agendamento['modelo']; ?>
                                        <span class="badge bg-secondary"><?php echo $agendamento['placa']; ?></span>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center">
                            <a href="modules/agendamentos/listar.php" class="btn btn-outline-primary btn-sm">
                                Ver Todos
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Nenhum agendamento próximo</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Alertas de Estoque -->
            <?php if ($estoque_baixo > 0): ?>
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Alerta de Estoque
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong><?php echo $estoque_baixo; ?></strong> produto(s) com estoque baixo
                        </p>
                        <a href="modules/estoque/produtos.php?filtro=estoque_baixo" class="btn btn-warning btn-sm">
                            <i class="fas fa-boxes me-1"></i>
                            Verificar Estoque
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="modules/ordens_servico/nova.php" class="btn btn-primary w-100 p-3">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                Nova OS
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="modules/agendamentos/novo.php" class="btn btn-info w-100 p-3">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i><br>
                                Novo Agendamento
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="modules/clientes/cadastrar.php" class="btn btn-success w-100 p-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                Novo Cliente
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="modules/orcamentos/novo.php" class="btn btn-warning w-100 p-3">
                                <i class="fas fa-file-invoice fa-2x mb-2"></i><br>
                                Novo Orçamento
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
```

## 4. Instruções de Instalação e Configuração

### 4.1 Requisitos do Sistema

**Servidor Web:**
- Apache 2.4+ ou Nginx 1.18+
- PHP 7.4+ (recomendado PHP 8.0+)
- MySQL 5.7+ ou MariaDB 10.3+

**Extensões PHP Necessárias:**
- PDO e PDO_MySQL
- mbstring
- json
- curl
- gd ou imagick (para manipulação de imagens)
- zip
- xml

**Configurações PHP Recomendadas:**
```ini
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 300
date.timezone = America/Sao_Paulo
```

### 4.2 Passos de Instalação

**1. Preparação do Ambiente:**
```bash
# Criar diretório do projeto
mkdir /var/www/html/lava_jato_system
cd /var/www/html/lava_jato_system

# Definir permissões
chmod 755 /var/www/html/lava_jato_system
chown -R www-data:www-data /var/www/html/lava_jato_system
```

**2. Configuração do Banco de Dados:**
```sql
-- Criar banco de dados
CREATE DATABASE lava_jato_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário específico (recomendado)
CREATE USER 'lava_jato_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON lava_jato_db.* TO 'lava_jato_user'@'localhost';
FLUSH PRIVILEGES;

-- Executar script de estrutura
SOURCE sql/database_structure.sql;
```

**3. Configuração dos Arquivos:**
```php
// Editar config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lava_jato_db');
define('DB_USER', 'lava_jato_user');
define('DB_PASS', 'senha_segura_aqui');
```

**4. Configuração de Permissões:**
```bash
# Criar diretórios de upload
mkdir -p uploads/checklist_avarias
mkdir -p uploads/documentos

# Definir permissões de escrita
chmod 755 uploads/
chmod 755 uploads/checklist_avarias/
chmod 755 uploads/documentos/

# Propriedade do servidor web
chown -R www-data:www-data uploads/
```

**5. Configuração do Virtual Host (Apache):**
```apache
<VirtualHost *:80>
    ServerName lavajato.local
    DocumentRoot /var/www/html/lava_jato_system
    
    <Directory /var/www/html/lava_jato_system>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/lavajato_error.log
    CustomLog ${APACHE_LOG_DIR}/lavajato_access.log combined
</VirtualHost>
```

**6. Arquivo .htaccess (Segurança):**
```apache
# Proteção de arquivos sensíveis
<Files "*.php">
    Order allow,deny
    Allow from all
</Files>

<Files "config/*">
    Order deny,allow
    Deny from all
</Files>

# Redirecionamento HTTPS (se disponível)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteção contra ataques
RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|[|%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|[|%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>|ê|"|;|\?|\*|=$).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*("|'|<|>|\|{||).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(%0|%A|%B|%C|%D|%E|%F|127\.0).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(globals|encode|localhost|loopback).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(request|select|insert|union|declare).* [NC]
RewriteRule ^(.*)$ index.php [F,L]
```

### 4.3 Configurações de Segurança

**1. Senhas Seguras:**
- Sempre usar password_hash() para senhas
- Implementar política de senhas fortes
- Forçar troca de senha padrão no primeiro login

**2. Validação de Entrada:**
- Sanitizar todos os dados de entrada
- Usar prepared statements para consultas SQL
- Validar tipos de arquivo em uploads

**3. Controle de Sessão:**
- Configurar timeout de sessão
- Regenerar ID de sessão após login
- Implementar logout automático

**4. Logs de Auditoria:**
- Registrar todas as ações importantes
- Monitorar tentativas de login falhadas
- Backup regular dos logs

### 4.4 Manutenção e Backup

**1. Backup Automático:**
```bash
#!/bin/bash
# Script de backup diário
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/lava_jato"

# Backup do banco de dados
mysqldump -u lava_jato_user -p lava_jato_db > $BACKUP_DIR/db_$DATE.sql

# Backup dos arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/lava_jato_system/uploads/

# Manter apenas últimos 30 dias
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

**2. Monitoramento:**
- Verificar logs de erro regularmente
- Monitorar espaço em disco
- Verificar performance do banco de dados
- Testar backups periodicamente

**3. Atualizações:**
- Manter PHP e MySQL atualizados
- Aplicar patches de segurança
- Testar atualizações em ambiente de desenvolvimento primeiro

Este script fornece uma base sólida e completa para implementação de um sistema de lava jato profissional, com todas as funcionalidades identificadas nas imagens do VeltaCar e melhorias baseadas nas melhores práticas do mercado.

