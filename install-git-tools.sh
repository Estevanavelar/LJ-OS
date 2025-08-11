#!/bin/bash

# Script de Instalação Git Tools para Linux/macOS
# Execute como: ./install-git-tools.sh

# Cores para o terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# Banner
echo -e "${CYAN}╔════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║        INSTALADOR GIT TOOLS           ║${NC}"
echo -e "${CYAN}║         Configuração Global            ║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════╝${NC}"
echo ""

# Verifica se está no diretório correto
if [ ! -f "git-menu.sh" ]; then
    echo -e "${RED}❌ Erro: Execute este script no diretório dos Git Tools!${NC}"
    echo -e "${YELLOW}Navegue até a pasta 'Comandos git' e execute novamente.${NC}"
    exit 1
fi

echo -e "${BLUE}🔧 Configurando Git Tools globalmente...${NC}"

# Cria diretório de instalação global
INSTALL_DIR="$HOME/.git-tools"
mkdir -p "$INSTALL_DIR"

# Copia todos os scripts para o diretório global
echo -e "${YELLOW}📁 Copiando scripts para $INSTALL_DIR...${NC}"
cp *.sh "$INSTALL_DIR/"
cp *.bat "$INSTALL_DIR/" 2>/dev/null || true

# Torna os scripts executáveis
chmod +x "$INSTALL_DIR"/*.sh

# Cria arquivo de aliases
ALIASES_FILE="$HOME/.git-tools-aliases"
echo -e "${YELLOW}📝 Criando arquivo de aliases...${NC}"

cat > "$ALIASES_FILE" << 'EOF'
# Git Tools - Aliases Globais
# Adicione este conteúdo ao seu ~/.bashrc, ~/.zshrc ou ~/.profile

# Menu principal
alias gitmenu='~/.git-tools/git-menu.sh'

# Operações básicas
alias gcommit='~/.git-tools/git-commit.sh'
alias gcomitar='~/.git-tools/comitar.sh'
alias gpull='~/.git-tools/git-pull.sh'
alias gpush='~/.git-tools/git-push.sh'
alias gsync='~/.git-tools/git-sync.sh'

# Gerenciamento
alias gbranch='~/.git-tools/git-branch.sh'
alias gstash='~/.git-tools/git-stash.sh'
alias gmerge='~/.git-tools/git-merge.sh'
alias gtag='~/.git-tools/git-tag.sh'
alias glog='~/.git-tools/git-log.sh'

# Aliases curtos
alias gc='gcommit'
alias gp='gpush'
alias gl='gpull'
alias gs='gsync'
alias gb='gbranch'
alias gst='gstash'
alias gm='gmerge'
alias gt='gtag'
alias glg='glog'

# Comando de ajuda
alias githelp='~/.git-tools/git-help.sh'
alias help='githelp'
alias ghelp='githelp'

# Funções para melhor compatibilidade
gitmenu() { ~/.git-tools/git-menu.sh "$@"; }
gcommit() { ~/.git-tools/git-commit.sh "$@"; }
gsync() { ~/.git-tools/git-sync.sh "$@"; }
gbranch() { ~/.git-tools/git-branch.sh "$@"; }
gstash() { ~/.git-tools/git-stash.sh "$@"; }
githelp() { ~/.git-tools/git-help.sh "$@"; }

echo "🚀 Git Tools carregado! Use 'gitmenu' para começar ou 'githelp' para ajuda."
EOF

echo -e "${GREEN}✅ Arquivo de aliases criado: $ALIASES_FILE${NC}"

# Tenta encontrar e configurar o arquivo de configuração do shell
SHELL_CONFIG=""
if [ -f "$HOME/.bashrc" ]; then
    SHELL_CONFIG="$HOME/.bashrc"
elif [ -f "$HOME/.zshrc" ]; then
    SHELL_CONFIG="$HOME/.zshrc"
elif [ -f "$HOME/.profile" ]; then
    SHELL_CONFIG="$HOME/.profile"
fi

if [ -n "$SHELL_CONFIG" ]; then
    if grep -q "git-tools-aliases" "$SHELL_CONFIG"; then
        echo -e "${YELLOW}⚠️  Git Tools já configurado em $SHELL_CONFIG${NC}"
    else
        echo "" >> "$SHELL_CONFIG"
        echo "# Git Tools - Configuração automática" >> "$SHELL_CONFIG"
        echo "source \"$ALIASES_FILE\"" >> "$SHELL_CONFIG"
        echo -e "${GREEN}✅ Configuração adicionada a $SHELL_CONFIG${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Arquivo de configuração do shell não encontrado${NC}"
    echo -e "${YELLOW}Configure manualmente adicionando 'source \"$ALIASES_FILE\"' ao seu arquivo de configuração${NC}"
fi

# Cria script de desinstalação
UNINSTALL_SCRIPT="$INSTALL_DIR/uninstall.sh"
cat > "$UNINSTALL_SCRIPT" << 'EOF'
#!/bin/bash
echo "Desinstalando Git Tools..."

# Remove do arquivo de configuração do shell
SHELL_CONFIG=""
if [ -f "$HOME/.bashrc" ]; then
    SHELL_CONFIG="$HOME/.bashrc"
elif [ -f "$HOME/.zshrc" ]; then
    SHELL_CONFIG="$HOME/.zshrc"
elif [ -f "$HOME/.profile" ]; then
    SHELL_CONFIG="$HOME/.profile"
fi

if [ -n "$SHELL_CONFIG" ]; then
    # Remove linhas relacionadas ao Git Tools
    sed -i '/git-tools/d' "$SHELL_CONFIG"
    sed -i '/Git Tools/d' "$SHELL_CONFIG"
fi

# Remove arquivo de aliases
rm -f "$HOME/.git-tools-aliases"

# Remove diretório de instalação
rm -rf "$HOME/.git-tools"

echo "✅ Git Tools desinstalado com sucesso!"
EOF

chmod +x "$UNINSTALL_SCRIPT"

# ATIVAÇÃO AUTOMÁTICA DOS ALIASES
echo -e "${BLUE}🚀 Ativando aliases automaticamente...${NC}"
source "$ALIASES_FILE"

echo ""
echo -e "${GREEN}🎉 INSTALAÇÃO CONCLUÍDA!${NC}"
echo ""
echo -e "${CYAN}📋 ALIASES ATIVADOS AUTOMATICAMENTE:${NC}"
echo -e "${WHITE}✅ gitmenu - Menu principal${NC}"
echo -e "${WHITE}✅ gcommit - Commit rápido${NC}"
echo -e "${WHITE}✅ gsync - Sincronização completa${NC}"
echo -e "${WHITE}✅ gbranch - Gerenciar branches${NC}"
echo -e "${WHITE}✅ githelp - Ver ajuda completa${NC}"
echo ""
echo -e "${GREEN}🎯 TESTE AGORA:${NC}"
echo -e "${WHITE}\"githelp\"${NC} - Para ver todos os comandos"
echo -e "${WHITE}\"gitmenu\"${NC} - Para abrir o menu principal"
echo ""
echo -e "${YELLOW}💡 DICA: Os aliases já estão funcionando! Não precisa reiniciar o terminal.${NC}"
echo -e "${YELLOW}💡 DICA: Para ativar manualmente: source \"$ALIASES_FILE\"${NC}"
echo ""
echo -e "${BLUE}🔧 Para desinstalar: $UNINSTALL_SCRIPT${NC}"
echo -e "${BLUE}📁 Scripts instalados em: $INSTALL_DIR${NC}"
echo -e "${BLUE}📝 Aliases configurados em: $ALIASES_FILE${NC}"
echo -e "${BLUE}📝 Comando para ativar: source \"$ALIASES_FILE\"${NC}"
