#!/bin/bash

# Script para automatizar git pull
# Uso: ./git-pull.sh

echo "=== GIT PULL AUTOMÁTICO ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Mostra o branch atual
current_branch=$(git branch --show-current)
echo "🌿 Branch atual: $current_branch"

# Verifica se há mudanças não commitadas
if ! git diff-index --quiet HEAD --; then
    echo ""
    echo "⚠️  Aviso: Existem mudanças não commitadas!"
    echo ""
    echo "Opções:"
    echo "1) Fazer stash das mudanças e continuar"
    echo "2) Cancelar operação"
    echo ""
    read -p "Escolha uma opção (1/2): " choice
    
    case $choice in
        1)
            echo "📦 Fazendo stash das mudanças..."
            git stash save "Stash automático antes do pull - $(date '+%d/%m/%Y %H:%M:%S')"
            stash_created=true
            ;;
        2)
            echo "❌ Operação cancelada!"
            exit 0
            ;;
        *)
            echo "❌ Opção inválida!"
            exit 1
            ;;
    esac
fi

# Busca atualizações do remoto
echo ""
echo "🔄 Buscando atualizações do repositório remoto..."
git fetch origin

# Verifica se há atualizações
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u} 2>/dev/null)

if [ -z "$REMOTE" ]; then
    echo "⚠️  Branch não tem upstream configurado!"
    echo ""
    read -p "Deseja configurar 'origin/$current_branch' como upstream? (s/n): " setup_upstream
    
    if [ "$setup_upstream" = "s" ] || [ "$setup_upstream" = "S" ]; then
        git branch --set-upstream-to=origin/$current_branch $current_branch
        REMOTE=$(git rev-parse @{u})
    else
        echo "❌ Operação cancelada!"
        exit 1
    fi
fi

BASE=$(git merge-base @ @{u} 2>/dev/null)

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "✅ Já está atualizado!"
elif [ "$LOCAL" = "$BASE" ]; then
    echo "📥 Baixando atualizações..."
    git pull origin $current_branch
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Atualização concluída com sucesso!"
        
        # Mostra resumo das mudanças
        echo ""
        echo "📊 Resumo das mudanças:"
        git log --oneline $LOCAL..$REMOTE
    else
        echo "❌ Erro ao fazer pull!"
        exit 1
    fi
elif [ "$REMOTE" = "$BASE" ]; then
    echo "📤 Você tem commits locais não enviados!"
    echo "Use ./git-push.sh para enviar suas mudanças."
else
    echo "🔀 Divergência detectada! Fazendo merge..."
    git pull origin $current_branch
    
    if [ $? -ne 0 ]; then
        echo "❌ Conflitos detectados! Resolva os conflitos manualmente."
        exit 1
    fi
fi

# Restaura o stash se foi criado
if [ "$stash_created" = true ]; then
    echo ""
    echo "📦 Restaurando mudanças do stash..."
    git stash pop
    
    if [ $? -ne 0 ]; then
        echo "⚠️  Conflitos ao aplicar stash! Resolva manualmente."
        echo "Use 'git stash list' para ver seus stashes."
    fi
fi

echo ""
echo "🎉 Processo concluído!"
