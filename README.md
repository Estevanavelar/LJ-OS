# LJ-OS Sistema para Lava Jato

Sistema completo de gestão para lava jatos, desenvolvido em PHP com MySQL.

## 🚀 Funcionalidades

- **Gestão de Clientes**: Cadastro completo de clientes (PF/PJ) com histórico
- **Gestão de Veículos**: Controle de veículos por cliente
- **Agendamentos**: Sistema de agendamento de serviços
- **Ordens de Serviço**: Controle completo de OS com produtos e serviços
- **Controle de Estoque**: Gestão de produtos com alertas de estoque baixo
- **Módulo Financeiro**: Controle de receitas e despesas com categorização
- **Gestão de Funcionários**: Controle de funcionários e presença
- **Sistema de Permissões**: Controle granular de acesso por usuário
- **Relatórios**: Relatórios completos de todas as operações
- **Orçamentos**: Sistema de orçamentos com validade
- **Cupons de Desconto**: Sistema de cupons promocionais

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache/Nginx
- XAMPP (recomendado para desenvolvimento)

## 🛠️ Instalação

### 1. Clone o repositório
```bash
git clone [url-do-repositorio]
cd LJ
```

### 2. Configure o banco de dados
- Crie um banco de dados MySQL chamado `lava_jato_db`
- Ou use o instalador automático

### 3. Instalação automática (Recomendado)
1. Acesse `http://localhost/LJ/install.php`
2. Preencha as informações solicitadas:
   - Configurações do banco de dados
   - Nome da empresa                
   - Email e senha do administrador
3. Clique em "Instalar Sistema"

### 4. Instalação manual
Se preferir instalar manualmente:

1. Execute o script SQL consolidado:
```bash
mysql -u root -p < sql/database_completo.sql
```

2. Configure o arquivo `config/database.php` com suas credenciais

3. Acesse o sistema em `http://localhost/LJ/`

## 🌐 Instalação da Área do Cliente (Subdomínio)

A área do cliente pode ser instalada em um subdomínio separado (ex: `cliente.seudominio.com`) mas utilizando o mesmo banco de dados do sistema principal.

### Opção 1: Subdomínio no mesmo servidor

1. **Crie o subdomínio**:
   - Acesse o painel de controle do seu provedor de hospedagem
   - Crie um subdomínio (ex: `cliente.seudominio.com`)
   - Aponte para uma pasta separada (ex: `public_html/cliente/`)

2. **Copie os arquivos da área do cliente**:
```bash
# Copie a pasta cliente para o diretório do subdomínio
cp -r LJ/cliente/* /caminho/para/subdominio/
```

3. **Configure o banco de dados**:
   - O arquivo `config.php` já está criado e configurado na pasta `cliente/`
   - Para produção, edite o arquivo e altere as configurações:
     - Credenciais do banco de dados
     - URLs do sistema
     - Configurações de segurança

4. **Arquivos já atualizados**:
   - Os arquivos `index.php`, `dashboard.php` e `logout.php` já foram atualizados
   - Todos incluem automaticamente o arquivo `config.php`
   - Sistema de logs e validação de sessão implementados

### Opção 2: Subdomínio em servidor diferente

1. **Configure o banco de dados remoto**:
   - Certifique-se de que o MySQL permite conexões remotas
   - Configure as credenciais no `config.php`

2. **Copie apenas os arquivos necessários**:
```bash
# Estrutura mínima para o subdomínio
cliente/
├── config.php
├── index.php
├── dashboard.php
├── logout.php
└── assets/ (se houver arquivos específicos)
```

3. **Configure o DNS**:
   - Aponte o subdomínio para o novo servidor
   - Configure o virtual host no Apache/Nginx

### Configurações de Segurança

1. **HTTPS obrigatório**:
   - Configure SSL para o subdomínio
   - Force redirecionamento HTTPS

2. **Controle de acesso**:
   - A área do cliente usa apenas CPF/CNPJ para login
   - Não requer senha (baseado na confiança do documento)

3. **Logs de acesso**:
   - O sistema registra todos os acessos na tabela `logs_acesso_cliente`
   - Monitore acessos suspeitos

### Teste da Instalação

1. **Acesse o subdomínio**: `https://cliente.seudominio.com`
2. **Teste o login** com um CPF/CNPJ cadastrado no sistema principal
3. **Verifique as funcionalidades**:
   - Dashboard do cliente
   - Histórico de serviços
   - Agendamentos
   - Veículos cadastrados

### Solução de Problemas

**Erro de conexão com banco**:
- Verifique as credenciais no `config.php`
- Certifique-se de que o banco permite conexões remotas
- Teste a conexão manualmente

**Erro "Cliente não encontrado"**:
- Verifique se o cliente está ativo no sistema principal
- Confirme se o CPF/CNPJ está cadastrado corretamente

**Redirecionamentos quebrados**:
- Atualize a constante `SISTEMA_URL` no `config.php`
- Verifique se os links apontam para o domínio correto

## 📁 Estrutura de Arquivos

```
LJ/
├── api/                    # APIs REST
├── assets/                 # CSS, JS e imagens
├── cliente/                # Área do cliente
├── config/                 # Configurações
├── includes/               # Arquivos incluídos
├── logs/                   # Logs do sistema
├── sql/                    # Scripts SQL
│   ├── database_completo.sql  # Script consolidado (RECOMENDADO)
│   ├── database_structure.sql # Estrutura básica
│   ├── novas_tabelas.sql      # Tabelas adicionais
│   └── tabelas_financeiro.sql # Tabelas financeiras
├── uploads/                # Uploads de arquivos
├── index.php               # Página inicial (redireciona)
├── dashboard.php           # Dashboard principal
├── login.php               # Página de login
└── install.php             # Instalador
```

## 🔧 Configuração

### Arquivo index.php
O arquivo `index.php` na raiz do sistema redireciona automaticamente:
- Se o usuário estiver logado → `dashboard.php`
- Se não estiver logado → `login.php`

### Banco de Dados
O arquivo `sql/database_completo.sql` contém:
- Todas as tabelas do sistema
- Dados iniciais (usuário admin, categorias, etc.)
- Índices para otimização
- Triggers para automação
- Views para relatórios

## 👤 Usuário Padrão

Após a instalação, você pode fazer login com:
- **Email**: admin@lava-jato.com
- **Senha**: password (será alterada durante a instalação)

## 🔐 Permissões

O sistema possui 4 níveis de acesso:
- **Admin**: Acesso total ao sistema
- **Gerente**: Acesso gerencial
- **Atendente**: Acesso operacional
- **Funcionário**: Acesso limitado

## 📊 Módulos Principais

### Dashboard
- Visão geral do negócio
- Estatísticas em tempo real
- Últimas ordens de serviço
- Próximos agendamentos

### Clientes
- Cadastro completo (PF/PJ)
- Histórico de serviços
- Programa de fidelidade
- Documentos e contatos

### Veículos
- Cadastro por cliente
- Histórico de serviços
- Informações técnicas
- Controle de quilometragem

### Agendamentos
- Calendário de agendamentos
- Confirmação automática
- Lembretes por email
- Controle de horários

### Ordens de Serviço
- Numeração automática
- Produtos e serviços
- Controle de pagamento
- Status em tempo real

### Estoque
- Controle de produtos
- Alertas de estoque baixo
- Movimentações
- Relatórios

### Financeiro
- Receitas e despesas
- Categorização
- Relatórios mensais
- Controle de pagamentos

### Funcionários
- Cadastro completo
- Controle de presença
- Comissões
- Histórico

## 🐛 Solução de Problemas

### Erro de conexão com banco
1. Verifique as credenciais em `config/database.php`
2. Certifique-se de que o MySQL está rodando
3. Verifique se o banco `lava_jato_db` existe

### Erro "headers already sent"
- Verifique se não há espaços ou caracteres antes de `<?php`
- Certifique-se de que `session_start()` está no início dos arquivos

### Menu não aparece
- Verifique as permissões do usuário
- Execute o script de configuração de permissões

## 📝 Logs

O sistema mantém logs de:
- Acessos de usuários
- Operações críticas
- Erros do sistema
- Movimentações financeiras

## 🔄 Atualizações

Para atualizar o sistema:
1. Faça backup do banco de dados
2. Execute o script SQL de atualização
3. Verifique as permissões

## 📞 Suporte

Para suporte técnico:
- Email: suporte@lava-jato.com
- Documentação: [link-da-documentacao]

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---

**Desenvolvido com ❤️ para lava jatos**

