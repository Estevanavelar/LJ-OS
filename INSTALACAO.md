# 📋 Guia de Instalação - LJ-OS

Este guia detalha como instalar e configurar o sistema LJ-OS em seu ambiente.

## 🎯 Visão Geral

O LJ-OS é um sistema completo de gestão para oficinas automotivas, desenvolvido em PHP com arquitetura moderna e interface responsiva.

## ⚙️ Requisitos do Sistema

### Requisitos Mínimos
- **PHP**: 8.0.0 ou superior
- **Extensões PHP**:
  - PDO (com suporte SQLite)
  - JSON
  - OpenSSL
  - MBString
- **Servidor Web**: Apache/Nginx (XAMPP recomendado)
- **Memória RAM**: 512MB mínimo
- **Espaço em Disco**: 100MB mínimo

### Requisitos Recomendados
- **PHP**: 8.2.0 ou superior
- **Memória RAM**: 2GB ou mais
- **Espaço em Disco**: 1GB ou mais
- **Servidor**: XAMPP 8.2+ ou similar

## 🚀 Instalação Automática

### Opção 1: Instalador Web (Mais Fácil)

1. **Acesse o instalador:**
   ```
   http://localhost/LJ-OS/install_web.php
   ```

2. **Siga os passos:**
   - ✅ Verificação de requisitos
   - ⚙️ Configuração automática
   - 🎉 Instalação concluída

3. **Acesse o sistema:**
   ```
   http://localhost/LJ-OS/
   ```

### Opção 2: Instalador via Linha de Comando

1. **Abra o terminal no diretório do projeto:**
   ```bash
   cd C:\xampp\htdocs\LJ-OS
   ```

2. **Execute o instalador:**
   ```bash
   php install.php
   ```

3. **Siga as instruções na tela**

## 🛠️ Instalação Manual

### Passo 1: Preparar o Ambiente

1. **Instalar XAMPP:**
   - Baixe em: https://www.apachefriends.org/
   - Instale na pasta padrão: `C:\xampp`

2. **Configurar PHP:**
   - Verificar se PHP 8.0+ está ativo
   - Habilitar extensões necessárias no `php.ini`

3. **Iniciar serviços:**
   - Apache: ✅
   - MySQL: ✅ (opcional, o sistema usa SQLite por padrão)

### Passo 2: Configurar o Projeto

1. **Colocar o projeto em:**
   ```
   C:\xampp\htdocs\LJ-OS\
   ```

2. **Instalar dependências:**
   ```bash
   composer install
   ```

3. **Configurar permissões:**
   ```bash
   # Criar diretórios necessários
   mkdir database logs cache tmp uploads
   
   # Definir permissões (Windows: não necessário)
   chmod 755 database logs cache tmp uploads
   ```

### Passo 3: Configurar Banco de Dados

1. **Arquivo de configuração:**
   ```php
   // config/config.php
   'database' => [
       'driver' => 'sqlite',
       'database' => 'lj_os',
   ]
   ```

2. **Executar schema:**
   - O sistema criará automaticamente o banco SQLite
   - Localização: `database/lj_os.db`

### Passo 4: Criar Usuário Administrador

1. **Acessar o sistema:**
   ```
   http://localhost/LJ-OS/
   ```

2. **Primeiro acesso:**
   - Email: `admin@lj-os.com`
   - Senha: `admin123`

3. **Alterar senha:**
   - Acesse: Configurações → Perfil
   - Altere a senha padrão

## 🔧 Configurações Avançadas

### Variáveis de Ambiente (.env)

```env
# Configurações da Aplicação
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo

# Configurações JWT
JWT_SECRET=sua-chave-secreta-aqui
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800

# Configurações de Banco
DB_DRIVER=sqlite
DB_DATABASE=lj_os

# Configurações de Log
LOG_LEVEL=info
LOG_MAX_FILES=30

# Configurações de Cache
CACHE_DRIVER=file
CACHE_TTL=3600
```

### Configurações do Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Segurança
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "*.db">
    Order allow,deny
    Deny from all
</Files>
```

### Configurações do PHP (php.ini)

```ini
; Configurações recomendadas
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
date.timezone = "America/Sao_Paulo"

; Extensões necessárias
extension=pdo
extension=pdo_sqlite
extension=json
extension=openssl
extension=mbstring
```

## 🧪 Verificação da Instalação

### Teste Básico

1. **Verificar PHP:**
   ```bash
   php -v
   php -m | grep -E "(pdo|sqlite|json|openssl|mbstring)"
   ```

2. **Verificar estrutura:**
   ```bash
   # Deve existir:
   - src/Database/Database.php
   - src/Models/
   - config/config.php
   - autoload.php
   ```

3. **Testar sistema:**
   ```bash
   php test_system.php
   ```

### Teste via Navegador

1. **Página inicial:**
   ```
   http://localhost/LJ-OS/
   ```

2. **API de status:**
   ```
   http://localhost/LJ-OS/api/status
   ```

3. **Página de login:**
   ```
   http://localhost/LJ-OS/public/login.php
   ```

## 🚨 Solução de Problemas

### Erro: "Class not found"

**Causa:** Problema no autoloader
**Solução:**
```bash
# Verificar se autoload.php existe
ls -la autoload.php

# Recriar autoloader
composer dump-autoload
```

### Erro: "Database connection failed"

**Causa:** Problema de permissões ou SQLite
**Solução:**
```bash
# Verificar permissões
ls -la database/

# Recriar banco
rm database/lj_os.db
php install.php
```

### Erro: "Permission denied"

**Causa:** Problema de permissões no Windows
**Solução:**
- Executar XAMPP como administrador
- Verificar permissões da pasta do projeto

### Erro: "Extension not loaded"

**Causa:** Extensão PHP não habilitada
**Solução:**
1. Abrir `C:\xampp\php\php.ini`
2. Descomentar extensões necessárias
3. Reiniciar Apache

## 📱 Primeiro Acesso

### Login Inicial

1. **Acessar:** `http://localhost/LJ-OS/`
2. **Credenciais:**
   - Email: `admin@lj-os.com`
   - Senha: `admin123`

### Configurações Recomendadas

1. **Alterar senha do administrador**
2. **Configurar dados da empresa**
3. **Criar usuários adicionais**
4. **Configurar categorias de serviços**
5. **Configurar formas de pagamento**

## 🔒 Segurança

### Configurações Importantes

1. **Alterar chave JWT:**
   ```env
   JWT_SECRET=chave-única-e-segura-aqui
   ```

2. **Configurar HTTPS** (produção)
3. **Restringir acesso a arquivos sensíveis**
4. **Configurar backup automático**
5. **Monitorar logs de acesso**

### Usuários e Permissões

1. **Níveis de acesso:**
   - `admin`: Acesso total
   - `gerente`: Gestão operacional
   - `operador`: Operações básicas
   - `visualizador`: Apenas consultas

2. **Permissões granulares:**
   - `clientes.read` / `clientes.write`
   - `veiculos.read` / `veiculos.write`
   - `agendamentos.read` / `agendamentos.write`
   - `relatorios.read`

## 📊 Monitoramento

### Logs do Sistema

- **Localização:** `logs/`
- **Rotação:** Automática (30 arquivos)
- **Níveis:** DEBUG, INFO, WARNING, ERROR

### Métricas Importantes

1. **Performance:**
   - Tempo de resposta das APIs
   - Uso de memória
   - Queries do banco

2. **Segurança:**
   - Tentativas de login
   - Acessos não autorizados
   - Alterações críticas

## 🆘 Suporte

### Antes de Pedir Ajuda

1. ✅ Verificar requisitos do sistema
2. ✅ Ler este guia completamente
3. ✅ Verificar logs de erro
4. ✅ Testar com dados mínimos

### Informações para Suporte

- Versão do PHP
- Versão do XAMPP
- Sistema operacional
- Logs de erro
- Passos para reproduzir o problema

### Canais de Suporte

- 📧 Email: suporte@lj-os.com
- 💬 Chat: Sistema integrado
- 📚 Documentação: Este guia
- 🐛 Issues: Repositório GitHub

---

**🎉 Parabéns! Seu sistema LJ-OS está instalado e funcionando.**

**Próximo passo:** Começar a usar o sistema e configurar sua oficina!
