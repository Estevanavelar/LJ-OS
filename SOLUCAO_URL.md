# 🔧 Solução para URL sendo interceptada pelo XAMPP

## ❌ Problema Identificado
Quando você acessa `http://localhost/LJ-OS`, o XAMPP está interceptando a URL e redirecionando para o index padrão do XAMPP em vez de ir para o sistema LJ-OS.

## 🎯 Soluções

### Solução 1: Verificar Módulo mod_rewrite (Recomendada)

1. **Abra o arquivo de configuração do Apache:**
   ```
   C:\xampp\apache\conf\httpd.conf
   ```

2. **Procure e descomente a linha:**
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
   (Remova o # do início da linha se estiver comentada)

3. **Procure a seção do diretório htdocs e verifique se AllowOverride está como All:**
   ```apache
   <Directory "C:/xampp/htdocs">
       Options Indexes FollowSymLinks Includes ExecCGI
       AllowOverride All
       Require all granted
   </Directory>
   ```

4. **Reinicie o Apache no painel de controle do XAMPP**

### Solução 2: Configuração Específica do Diretório

1. **Adicione ao final do arquivo `httpd.conf`:**
   ```apache
   # Configuração específica para LJ-OS
   Include conf/extra/lj-os.conf
   ```

2. **Ou adicione diretamente no `httpd.conf`:**
   ```apache
   <Directory "C:/xampp/htdocs/LJ-OS">
       Options Indexes FollowSymLinks MultiViews
       AllowOverride All
       Require all granted
       
       DirectoryIndex index.php
       RewriteEngine On
   </Directory>
   ```

### Solução 3: Verificar Arquivo .htaccess

1. **Confirme que o arquivo `.htaccess` existe na raiz do projeto LJ-OS**
2. **Verifique se o conteúdo está correto:**
   ```apache
   RewriteEngine On
   DirectoryIndex index.php
   ```

### Solução 4: Teste Direto

1. **Acesse diretamente:**
   ```
   http://localhost/LJ-OS/index.php
   ```

2. **Se funcionar, o problema é no .htaccess**
3. **Se não funcionar, o problema é na configuração do Apache**

## 🧪 Teste de Funcionamento

1. **Acesse:**
   ```
   http://localhost/LJ-OS/test_htaccess.php
   ```

2. **Se você ver a página de teste, o .htaccess está funcionando**
3. **Se não ver, há problema na configuração do Apache**

## 🔍 Verificações Adicionais

### Verificar se o Apache está lendo o .htaccess:
1. **Abra o painel de controle do XAMPP**
2. **Clique em "Config" do Apache**
3. **Selecione "httpd.conf"**
4. **Procure por "AllowOverride"**

### Verificar logs de erro:
1. **Abra:**
   ```
   C:\xampp\apache\logs\error.log
   ```
2. **Procure por erros relacionados ao diretório LJ-OS**

## 📋 Passos para Resolver

1. **✅ Verificar se mod_rewrite está habilitado**
2. **✅ Verificar se AllowOverride está como All**
3. **✅ Verificar se o .htaccess existe e está correto**
4. **✅ Reiniciar o Apache**
5. **✅ Testar acessando `http://localhost/LJ-OS`**

## 🚨 Problemas Comuns

- **mod_rewrite não habilitado**
- **AllowOverride None em vez de All**
- **Arquivo .htaccess com sintaxe incorreta**
- **Permissões de arquivo incorretas**
- **Cache do navegador**

## 📞 Se Nada Funcionar

1. **Verifique os logs do Apache**
2. **Teste com um .htaccess mais simples**
3. **Verifique se há conflitos com outras configurações**
4. **Considere usar um arquivo de configuração específico no httpd.conf**

---

**💡 Dica:** Após fazer as alterações, sempre reinicie o Apache no painel de controle do XAMPP!
