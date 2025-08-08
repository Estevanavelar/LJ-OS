# 🚗 LJ-OS Sistema para Lava Jato - Guia de Instalação

## 📋 Pré-requisitos

Antes de instalar o sistema, certifique-se de que seu servidor atende aos seguintes requisitos:

### Requisitos do Servidor
- **PHP:** 7.4 ou superior
- **MySQL:** 5.7 ou superior / MariaDB 10.3 ou superior
- **Servidor Web:** Apache 2.4+ ou Nginx 1.18+
- **Extensões PHP obrigatórias:**
  - PDO MySQL
  - MBString
  - cURL
  - GD
  - JSON
  - OpenSSL

### Requisitos de Hardware (Recomendados)
- **RAM:** Mínimo 512MB, recomendado 1GB+
- **Espaço em disco:** Mínimo 100MB
- **Processador:** Qualquer processador moderno

## 🚀 Instalação Automática (Recomendada)

### Passo 1: Preparar os Arquivos
1. Baixe ou clone os arquivos do sistema para seu servidor web
2. Certifique-se de que todos os arquivos estão na pasta correta (ex: `/var/www/html/lava-jato/`)

### Passo 2: Configurar Permissões
```bash
# No terminal, navegue até a pasta do sistema
cd /caminho/para/lava-jato

# Definir permissões corretas
chmod 755 uploads/
chmod 755 logs/
chmod 644 config/
chmod 644 sql/
```

### Passo 3: Executar Instalação
1. Abra seu navegador e acesse: `http://seu-dominio.com/install.php`
2. O sistema verificará automaticamente os requisitos
3. Preencha as informações solicitadas:
   - **Host do Banco:** geralmente `localhost`
   - **Nome do Banco:** `lava_jato_db` (ou o nome que preferir)
   - **Usuário do Banco:** `root` (ou usuário específico)
   - **Senha do Banco:** senha do seu MySQL
   - **Email do Administrador:** email para login
   - **Senha do Administrador:** senha para login
   - **Nome da Empresa:** nome da sua empresa

4. Clique em "Instalar Sistema"
5. Aguarde a conclusão da instalação

### Passo 4: Primeiro Acesso
1. Após a instalação, você será redirecionado para o login
2. Use as credenciais configuradas durante a instalação
3. **IMPORTANTE:** Altere a senha padrão no primeiro acesso!

## 🔧 Instalação Manual

Se preferir instalar manualmente, siga estes passos:

### Passo 1: Criar Banco de Dados
```sql
CREATE DATABASE lava_jato_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Passo 2: Importar Estrutura
```bash
mysql -u root -p lava_jato_db < sql/database_structure.sql
```

### Passo 3: Configurar Conexão
Edite o arquivo `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lava_jato_db');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### Passo 4: Criar Diretórios
```bash
mkdir -p uploads/clientes
mkdir -p uploads/veiculos
mkdir -p uploads/os
mkdir -p logs
chmod 755 uploads/ logs/
```

### Passo 5: Configurar Administrador
Execute no MySQL:
```sql
UPDATE usuarios SET 
email = 'seu-email@exemplo.com',
senha = '$2y$10$...' -- Use password_hash() para gerar
WHERE nivel_acesso = 'admin';
```

## ⚙️ Configurações Pós-Instalação

### Configurações de Email
Edite `config/apis.php` para configurar notificações:
```php
define('NOTIFICATIONS_EMAIL', true);
// Configure seu servidor SMTP se necessário
```

### Configurações de WhatsApp (Opcional)
Para usar notificações WhatsApp:
1. Obtenha uma API key do WhatsApp Business
2. Configure em `config/apis.php`:
```php
define('WHATSAPP_API_KEY', 'sua_api_key');
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/...');
```

### Configurações de SMS (Opcional)
Para usar notificações SMS:
1. Contrate um provedor de SMS
2. Configure em `config/apis.php`:
```php
define('SMS_API_KEY', 'sua_api_key');
define('SMS_API_URL', 'https://api.sms.com/...');
```

## 🔒 Segurança

### Recomendações Importantes
1. **Altere a senha padrão** do administrador
2. **Configure HTTPS** para produção
3. **Faça backup regular** do banco de dados
4. **Mantenha o PHP atualizado**
5. **Configure firewall** adequadamente

### Backup Automático
O sistema inclui funcionalidades de backup. Configure um cron job:
```bash
# Backup diário às 2h da manhã
0 2 * * * /usr/bin/php /caminho/para/lava-jato/backup.php
```

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique se o MySQL está rodando
- Confirme usuário e senha
- Verifique se o banco existe

### Erro de Permissões
```bash
chmod -R 755 uploads/
chmod -R 755 logs/
chown -R www-data:www-data uploads/ logs/
```

### Erro de Extensões PHP
Instale as extensões necessárias:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-mbstring php-curl php-gd

# CentOS/RHEL
sudo yum install php-mysql php-mbstring php-curl php-gd
```

### Erro de Upload de Arquivos
Verifique o `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

## 📞 Suporte

### Logs do Sistema
Os logs estão em:
- `logs/` - Logs gerais do sistema
- `uploads/` - Arquivos enviados
- MySQL error log - Logs do banco

### Informações Úteis
- **Versão do Sistema:** 1.0
- **Última Atualização:** <?php echo date('d/m/Y'); ?>
- **Compatibilidade:** PHP 7.4+, MySQL 5.7+

### Contato
Para suporte técnico, consulte a documentação completa ou entre em contato através dos canais oficiais.

---

**🎉 Parabéns! Seu sistema LJ-OS está pronto para uso!**

Lembre-se de:
- Fazer backup regular
- Manter o sistema atualizado
- Treinar sua equipe
- Configurar as notificações
- Personalizar conforme necessário 