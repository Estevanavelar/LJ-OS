#!/bin/bash

# Git Tools - Sistema de Ajuda
# Uso: ./git-help.sh ou githelp

# Cores para o terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# Banner
echo -e "${CYAN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║                    GIT TOOLS - AJUDA                        ║${NC}"
echo -e "${CYAN}║              Sistema de Comandos Git                        ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Comandos principais
echo -e "${YELLOW}📋 COMANDOS PRINCIPAIS:${NC}"
echo -e "  ${GREEN}gitmenu${NC}     - Menu principal interativo"
echo -e "  ${GREEN}gcommit${NC}     - Commit rápido"
echo -e "  ${GREEN}gsync${NC}       - Sincronização completa"
echo -e "  ${GREEN}gbranch${NC}     - Gerenciar branches"
echo -e "  ${GREEN}gstash${NC}      - Gerenciar stashes"
echo -e "  ${GREEN}gmerge${NC}      - Merge de branches"
echo -e "  ${GREEN}gtag${NC}        - Gerenciar tags"
echo -e "  ${GREEN}glog${NC}        - Ver histórico"
echo ""

# Aliases curtos
echo -e "${YELLOW}⚡ ALIASES CURTOS:${NC}"
echo -e "  ${GREEN}gc${NC}          - Commit rápido (gcommit)"
echo -e "  ${GREEN}gp${NC}          - Push (gpush)"
echo -e "  ${GREEN}gl${NC}          - Pull (gpull)"
echo -e "  ${GREEN}gs${NC}          - Sync (gsync)"
echo -e "  ${GREEN}gb${NC}          - Branch (gbranch)"
echo -e "  ${GREEN}gst${NC}         - Stash (gstash)"
echo -e "  ${GREEN}gm${NC}          - Merge (gmerge)"
echo -e "  ${GREEN}gt${NC}          - Tag (gtag)"
echo -e "  ${GREEN}glg${NC}         - Log (glog)"
echo ""

# Comandos adicionais
echo -e "${YELLOW}🔧 COMANDOS ADICIONAIS:${NC}"
echo -e "  ${GREEN}gcomitar${NC}    - Commit com interface amigável"
echo -e "  ${GREEN}gpull${NC}       - Pull (atualizar do remoto)"
echo -e "  ${GREEN}gpush${NC}       - Push (enviar para remoto)"
echo ""

# Uso
echo -e "${YELLOW}📖 COMO USAR:${NC}"
echo -e "  ${WHITE}gitmenu${NC}                    - Abre o menu principal"
echo -e "  ${WHITE}gcommit \"mensagem\"${NC}        - Commit com mensagem"
echo -e "  ${WHITE}gsync${NC}                      - Sincroniza com remoto"
echo -e "  ${WHITE}gbranch${NC}                    - Gerencia branches"
echo ""

# Exemplos práticos
echo -e "${YELLOW}💡 EXEMPLOS PRÁTICOS:${NC}"
echo -e "  ${WHITE}gitmenu${NC}                    - Menu completo"
echo -e "  ${WHITE}gcommit \"feat: nova funcionalidade\"${NC}"
echo -e "  ${WHITE}gsync${NC}                      - Pull + Push automático"
echo -e "  ${WHITE}gbranch${NC}                    - Criar/alterar branches"
echo -e "  ${WHITE}gstash save \"trabalho em progresso\"${NC}"
echo ""

# Informações do sistema
echo -e "${YELLOW}ℹ️  INFORMAÇÕES:${NC}"
if [ -f "$HOME/.git-tools-aliases" ]; then
    echo -e "  ${GREEN}✅ Aliases instalados globalmente${NC}"
    echo -e "  ${WHITE}Localização:${NC} $HOME/.git-tools-aliases"
else
    echo -e "  ${RED}❌ Aliases não encontrados${NC}"
    echo -e "  ${WHITE}Execute:${NC} ./install-git-tools.sh"
fi

if [ -d "$HOME/.git-tools" ]; then
    echo -e "  ${GREEN}✅ Scripts instalados em:${NC} $HOME/.git-tools"
else
    echo -e "  ${RED}❌ Scripts não encontrados${NC}"
fi
echo ""

# Comandos de instalação
echo -e "${YELLOW}🔧 INSTALAÇÃO:${NC}"
echo -e "  ${WHITE}./install-git-tools.sh${NC}     - Instalar globalmente (Linux/macOS)"
echo -e "  ${WHITE}./install-git-tools.ps1${NC}   - Instalar globalmente (Windows)"
echo -e "  ${WHITE}./git-tools-portable.sh${NC}   - Usar portátil (qualquer pasta)"
echo ""

# Ajuda adicional
echo -e "${YELLOW}❓ MAIS AJUDA:${NC}"
echo -e "  ${WHITE}gitmenu${NC}                    - Menu interativo completo"
echo -e "  ${WHITE}./INSTALACAO.md${NC}            - Documentação de instalação"
echo -e "  ${WHITE}./README-GIT-SCRIPTS.md${NC}    - Documentação dos scripts"
echo ""

# Footer
echo -e "${CYAN}══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}💡 DICA: Use 'gitmenu' para acessar o menu principal!${NC}"
echo -e "${CYAN}══════════════════════════════════════════════════════════════════${NC}"
