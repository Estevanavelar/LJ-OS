# 📋 PRD - LJ-OS Sistema para Lava Jato
## Parte 2: Requisitos Técnicos e Arquitetura

---

## 🏗️ **9. ARQUITETURA TÉCNICA DETALHADA**

### **9.1 Estrutura de Diretórios**
```
LJ-OS/
├── 📂 api/                 # APIs RESTful
│   ├── agendamentos.php    # API de agendamentos
│   ├── clientes.php        # API de clientes
│   ├── estoque.php         # API de estoque
│   ├── financeiro.php      # API financeira
│   ├── funcionarios.php    # API de funcionários
│   ├── ordens_servico.php  # API de OS
│   ├── permissoes.php      # API de permissões
│   ├── relatorios.php      # API de relatórios
│   └── veiculos.php        # API de veículos
├── 📂 assets/              # Recursos estáticos
│   ├── css/                # Estilos CSS
│   ├── js/                 # JavaScript
│   └── images/             # Imagens
├── 📂 cliente/             # Área do cliente
├── 📂 config/              # Configurações
├── 📂 database/            # Banco SQLite
├── 📂 includes/            # Arquivos PHP comuns
├── 📂 logs/                # Logs do sistema
├── 📂 sql/                 # Scripts SQL
├── 📂 src/                 # Classes PHP
├── 📂 uploads/             # Uploads de arquivos
└── 📂 vendor/              # Dependências Composer
```

### **9.2 Padrões de Desenvolvimento**
- **PSR-4**: Autoloading de classes
- **PSR-12**: Padrões de codificação
- **MVC**: Separação de responsabilidades
- **Repository Pattern**: Acesso a dados
- **Factory Pattern**: Criação de objetos

---

## 🗄️ **10. ARQUITETURA DE BANCO DE DADOS**

### **10.1 Estrutura Principal**

#### **10.1.1 Tabela de Usuários**
```sql
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'gerente', 'atendente', 'funcionario'),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    foto_perfil VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    observacoes TEXT NULL
);
```

#### **10.1.2 Tabela de Clientes**
```sql
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
    programa_fidelidade BOOLEAN DEFAULT FALSE,
    pontos_fidelidade INT DEFAULT 0
);
```

#### **10.1.3 Tabela de Veículos**
```sql
CREATE TABLE veiculos (
    id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    ano_modelo INT NULL,
    cor VARCHAR(30) NOT NULL,
    combustivel ENUM('gasolina', 'etanol', 'diesel', 'flex', 'gnv', 'eletrico', 'hibrido'),
    km_atual INT NULL,
    observacoes TEXT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);
```

#### **10.1.4 Tabela de Agendamentos**
```sql
CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_servico INT NOT NULL,
    data_agendamento DATETIME NOT NULL,
    hora_entrega_estimada DATETIME NULL,
    status ENUM('agendado', 'confirmado', 'em_andamento', 'concluido', 'cancelado'),
    observacoes TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    FOREIGN KEY (id_servico) REFERENCES servicos(id_servico)
);
```

#### **10.1.5 Tabela de Ordens de Serviço**
```sql
CREATE TABLE ordens_servico (
    id_os INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_funcionario INT NULL,
    numero_os VARCHAR(20) UNIQUE NOT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP NULL,
    status ENUM('aberta', 'em_andamento', 'concluida', 'cancelada'),
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    valor_final DECIMAL(10,2) DEFAULT 0.00,
    observacoes TEXT NULL,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo),
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario)
);
```

### **10.2 Relacionamentos e Índices**
- **Índices primários**: Todas as chaves primárias
- **Índices únicos**: CPF/CNPJ, placa, email
- **Índices compostos**: Cliente + Veículo, Data + Status
- **Foreign Keys**: Integridade referencial completa
- **Cascata**: Exclusão em cascata para dependências

---

## 🔌 **11. APIS E INTEGRAÇÕES**

### **11.1 API RESTful**

#### **11.1.1 Endpoints de Clientes**
```http
GET    /api/clientes.php              # Listar todos os clientes
GET    /api/clientes.php?id=1         # Buscar cliente específico
GET    /api/clientes.php?search=joao  # Busca por nome/CPF
POST   /api/clientes.php              # Criar novo cliente
PUT    /api/clientes.php?id=1         # Atualizar cliente
DELETE /api/clientes.php?id=1         # Excluir cliente
```

#### **11.1.2 Endpoints de Veículos**
```http
GET    /api/veiculos.php              # Listar todos os veículos
GET    /api/veiculos.php?cliente_id=1 # Veículos por cliente
GET    /api/veiculos.php?placa=ABC1234 # Buscar por placa
POST   /api/veiculos.php              # Cadastrar veículo
PUT    /api/veiculos.php?id=1         # Atualizar veículo
DELETE /api/veiculos.php?id=1         # Excluir veículo
```

#### **11.1.3 Endpoints de Agendamentos**
```http
GET    /api/agendamentos.php          # Listar agendamentos
GET    /api/agendamentos.php?data=2024-01-15 # Por data
POST   /api/agendamentos.php          # Criar agendamento
PUT    /api/agendamentos.php?id=1     # Atualizar status
DELETE /api/agendamentos.php?id=1     # Cancelar agendamento
```

#### **11.1.4 Endpoints de Ordens de Serviço**
```http
GET    /api/ordens_servico.php        # Listar todas as OS
GET    /api/ordens_servico.php?id=1   # Buscar OS específica
POST   /api/ordens_servico.php        # Criar nova OS
PUT    /api/ordens_servico.php?id=1   # Atualizar OS
GET    /api/ordens_servico.php?status=aberta # Por status
```

### **11.2 Formato de Resposta**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nome": "João Silva",
        "email": "joao@email.com"
    },
    "message": "Cliente criado com sucesso",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### **11.3 Códigos de Status HTTP**
- **200**: Sucesso
- **201**: Criado com sucesso
- **400**: Erro de validação
- **401**: Não autorizado
- **403**: Proibido
- **404**: Não encontrado
- **500**: Erro interno do servidor

---

## 🔐 **12. SISTEMA DE SEGURANÇA**

### **12.1 Autenticação e Autorização**

#### **12.1.1 JWT (JSON Web Tokens)**
```php
// Estrutura do token
{
    "header": {
        "alg": "HS256",
        "typ": "JWT"
    },
    "payload": {
        "user_id": 123,
        "email": "user@example.com",
        "nivel_acesso": "admin",
        "iat": 1642233600,
        "exp": 1642319999
    }
}
```

#### **12.1.2 Níveis de Acesso**
- **Admin**: Acesso total ao sistema
- **Gerente**: Gestão de equipe e relatórios
- **Atendente**: Clientes, agendamentos e OS
- **Funcionário**: Apenas suas atividades

#### **12.1.3 Permissões Granulares**
```php
// Exemplo de verificação de permissão
function verificarPermissao($acao, $recurso) {
    $usuario = $_SESSION['usuario'];
    $permissoes = buscarPermissoes($usuario['id']);
    
    return in_array("$acao:$recurso", $permissoes);
}
```

### **12.2 Proteções de Segurança**

#### **12.2.1 CSRF Protection**
```php
// Geração de token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Validação em formulários
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Token CSRF inválido');
}
```

#### **12.2.2 SQL Injection Prevention**
```php
// Uso de prepared statements
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch();
```

#### **12.2.3 XSS Protection**
```php
// Sanitização de saída
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
```

---

## 📱 **13. INTERFACE E EXPERIÊNCIA DO USUÁRIO**

### **13.1 Design System**

#### **13.1.1 Cores Principais**
```css
:root {
    --primary-color: #667eea;      /* Azul principal */
    --secondary-color: #764ba2;    /* Roxo secundário */
    --success-color: #28a745;      /* Verde sucesso */
    --warning-color: #ffc107;      /* Amarelo aviso */
    --danger-color: #dc3545;       /* Vermelho erro */
    --info-color: #17a2b8;         /* Azul informação */
    --light-color: #f8f9fa;        /* Cinza claro */
    --dark-color: #343a40;         /* Cinza escuro */
}
```

#### **13.1.2 Tipografia**
- **Família**: Inter, -apple-system, BlinkMacSystemFont
- **Tamanhos**: 12px, 14px, 16px, 18px, 24px, 32px
- **Pesos**: 400 (normal), 500 (medium), 600 (semibold), 700 (bold)

#### **13.1.3 Componentes**
- **Cards**: Bordas arredondadas, sombras suaves
- **Botões**: Estados hover, focus e disabled
- **Formulários**: Validação em tempo real
- **Tabelas**: Paginação, ordenação e filtros

### **13.2 Responsividade**
- **Mobile-first**: Design otimizado para dispositivos móveis
- **Breakpoints**: 576px, 768px, 992px, 1200px
- **Grid System**: Bootstrap 5 grid responsivo
- **Touch-friendly**: Botões e elementos otimizados para toque

---

## 📊 **14. SISTEMA DE RELATÓRIOS**

### **14.1 Relatórios Operacionais**

#### **14.1.1 Dashboard Executivo**
- **KPIs principais**: Receita diária, clientes atendidos, OS em andamento
- **Gráficos**: Vendas por período, serviços mais vendidos
- **Alertas**: Estoque baixo, agendamentos pendentes

#### **14.1.2 Relatórios de Clientes**
- **Fidelização**: Clientes recorrentes, pontos de fidelidade
- **Segmentação**: Por tipo de veículo, localização, valor gasto
- **Comportamento**: Frequência de visitas, serviços preferidos

#### **14.1.3 Relatórios Financeiros**
- **Fluxo de caixa**: Receitas vs despesas por período
- **Lucratividade**: Margem por serviço, custos operacionais
- **Projeções**: Previsão de receita, sazonalidade

### **14.2 Tecnologias de Relatórios**
- **Charts.js**: Gráficos interativos
- **DataTables**: Tabelas com paginação e filtros
- **TCPDF**: Geração de relatórios em PDF
- **Exportação**: Excel (CSV), PDF, impressão

---

## 🔄 **15. SISTEMA DE BACKUP E RECUPERAÇÃO**

### **15.1 Estratégia de Backup**

#### **15.1.1 Backup Automático**
- **Frequência**: Diário às 02:00
- **Retenção**: 30 dias para backups diários
- **Compressão**: Gzip para economizar espaço
- **Verificação**: Checksum para integridade

#### **15.1.2 Backup Manual**
- **Antes de atualizações**: Backup completo
- **Mudanças estruturais**: Backup do banco
- **Arquivos críticos**: Configurações e uploads

### **15.2 Recuperação de Desastres**
- **RTO**: 4 horas para recuperação total
- **RPO**: Máximo de 24 horas de perda de dados
- **Testes**: Mensais de recuperação
- **Documentação**: Procedimentos detalhados

---

**📋 Esta é a segunda parte do PRD. Continue para a próxima parte que abordará as especificações funcionais detalhadas e casos de uso.**
