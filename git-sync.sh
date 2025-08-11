#!/bin/bash

# Script para sincronização completa do Git (add, commit, pull, push)
# Uso: ./git-sync.sh ["mensagem do commit"]

echo "=== GIT SYNC - SINCRONIZAÇÃO COMPLETA ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Mostra informações do repositório
echo "📁 Repositório: $(basename `git rev-parse --show-toplevel`)"
echo "🌿 Branch: $(git branch --show-current)"
echo ""

# Função para verificar conexão com o remoto
check_remote() {
    git ls-remote origin HEAD &>/dev/null
    if [ $? -ne 0 ]; then
        echo "❌ Erro: Não foi possível conectar ao repositório remoto!"
        echo "Verifique sua conexão com a internet e suas credenciais."
        exit 1
    fi
}

# Verifica conexão
echo "🔗 Verificando conexão com o remoto..."
check_remote
echo "✅ Conexão OK!"
echo ""

# Etapa 1: Verificar mudanças locais
echo "📊 Verificando mudanças locais..."
if [ -n "$(git status --porcelain)" ]; then
    echo "📝 Mudanças encontradas:"
    git status -s
    echo ""
    
    # Adiciona todas as mudanças
    echo "➕ Adicionando mudanças..."
    git add -A
    
    # Define mensagem do commit
    if [ -z "$1" ]; then
        echo ""
        echo "Digite a mensagem do commit (Enter para mensagem padrão):"
        read -r commit_message
        
        if [ -z "$commit_message" ]; then
            commit_message="Sincronização automática - $(date '+%d/%m/%Y %H:%M:%S')"
        fi
    else
        commit_message="$1"
    fi
    
    # Faz o commit
    echo "💾 Fazendo commit: $commit_message"
    git commit -m "$commit_message"
    
    if [ $? -eq 0 ]; then
        echo "✅ Commit realizado!"
        commit_made=true
    else
        echo "❌ Erro ao fazer commit!"
        exit 1
    fi
else
    echo "✅ Nenhuma mudança local para commit."
    commit_made=false
fi

echo ""

# Etapa 2: Buscar atualizações
echo "🔄 Buscando atualizações do remoto..."
git fetch origin

# Verifica status de sincronização
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u} 2>/dev/null)
BASE=$(git merge-base @ @{u} 2>/dev/null)

if [ -z "$REMOTE" ]; then
    echo "⚠️  Branch não tem upstream configurado!"
    
    if [ "$commit_made" = true ]; then
        echo ""
        read -p "Deseja criar o branch no remoto e fazer push? (s/n): " create_upstream
        
        if [ "$create_upstream" = "s" ] || [ "$create_upstream" = "S" ]; then
            echo "📤 Criando branch remoto e fazendo push..."
            git push -u origin $(git branch --show-current)
            
            if [ $? -eq 0 ]; then
                echo "✅ Branch criado e sincronizado!"
            else
                echo "❌ Erro ao criar branch remoto!"
                exit 1
            fi
        fi
    fi
    echo ""
    echo "🎉 Processo concluído!"
    exit 0
fi

# Etapa 3: Sincronizar com o remoto
if [ "$LOCAL" = "$REMOTE" ]; then
    echo "✅ Repositório já está sincronizado!"
elif [ "$LOCAL" = "$BASE" ]; then
    # Remoto tem commits que não temos
    echo "📥 Baixando atualizações do remoto..."
    git pull origin $(git branch --show-current)
    
    if [ $? -eq 0 ]; then
        echo "✅ Atualizações baixadas com sucesso!"
    else
        echo "❌ Erro ao fazer pull! Pode haver conflitos para resolver."
        exit 1
    fi
elif [ "$REMOTE" = "$BASE" ]; then
    # Temos commits locais para enviar
    echo "📤 Enviando commits locais..."
    git push origin $(git branch --show-current)
    
    if [ $? -eq 0 ]; then
        echo "✅ Commits enviados com sucesso!"
    else
        echo "❌ Erro ao fazer push!"
        exit 1
    fi
else
    # Divergência - precisamos fazer merge
    echo "🔀 Divergência detectada! Sincronizando..."
    echo ""
    echo "📥 Primeiro, baixando atualizações..."
    git pull origin $(git branch --show-current)
    
    if [ $? -eq 0 ]; then
        echo "✅ Merge realizado com sucesso!"
        echo ""
        echo "📤 Agora enviando tudo..."
        git push origin $(git branch --show-current)
        
        if [ $? -eq 0 ]; then
            echo "✅ Sincronização completa!"
        else
            echo "❌ Erro ao fazer push após merge!"
            exit 1
        fi
    else
        echo "❌ Conflitos detectados durante o merge!"
        echo ""
        echo "📋 Arquivos com conflito:"
        git diff --name-only --diff-filter=U
        echo ""
        echo "Resolva os conflitos manualmente, faça commit e execute o script novamente."
        exit 1
    fi
fi

echo ""

# Resumo final
echo "📊 === RESUMO FINAL ==="
echo "🌿 Branch: $(git branch --show-current)"
echo "📍 Último commit local:"
git log -1 --oneline
echo ""

# Verifica se está totalmente sincronizado
LOCAL_FINAL=$(git rev-parse @)
REMOTE_FINAL=$(git rev-parse @{u} 2>/dev/null)

if [ "$LOCAL_FINAL" = "$REMOTE_FINAL" ]; then
    echo "✅ Status: Totalmente sincronizado!"
else
    echo "⚠️  Status: Pode haver dessincronização"
fi

echo ""
echo "🎉 Processo de sincronização concluído!"
