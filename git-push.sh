#!/bin/bash

# Script para automatizar git push
# Uso: ./git-push.sh [branch]

echo "=== GIT PUSH AUTOMÁTICO ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Define o branch (padrão: branch atual)
if [ -z "$1" ]; then
    branch=$(git branch --show-current)
else
    branch="$1"
fi

echo "🌿 Branch: $branch"

# Verifica se há commits para enviar
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u} 2>/dev/null)

if [ -z "$REMOTE" ]; then
    echo ""
    echo "⚠️  Branch '$branch' não tem upstream configurado!"
    echo ""
    read -p "Deseja criar o branch no repositório remoto? (s/n): " create_remote
    
    if [ "$create_remote" = "s" ] || [ "$create_remote" = "S" ]; then
        echo "📤 Criando branch no remoto e fazendo push..."
        git push -u origin $branch
        
        if [ $? -eq 0 ]; then
            echo "✅ Branch criado e push realizado com sucesso!"
        else
            echo "❌ Erro ao fazer push!"
            exit 1
        fi
        exit 0
    else
        echo "❌ Operação cancelada!"
        exit 0
    fi
fi

# Verifica se há mudanças não commitadas
if ! git diff-index --quiet HEAD --; then
    echo ""
    echo "⚠️  Existem mudanças não commitadas!"
    echo ""
    git status -s
    echo ""
    echo "Opções:"
    echo "1) Fazer commit e continuar"
    echo "2) Ignorar mudanças e fazer push apenas dos commits existentes"
    echo "3) Cancelar operação"
    echo ""
    read -p "Escolha uma opção (1/2/3): " choice
    
    case $choice in
        1)
            echo ""
            read -p "Digite a mensagem do commit: " commit_msg
            if [ -z "$commit_msg" ]; then
                commit_msg="Commit automático antes do push - $(date '+%d/%m/%Y %H:%M:%S')"
            fi
            git add -A
            git commit -m "$commit_msg"
            ;;
        2)
            echo "📤 Fazendo push apenas dos commits existentes..."
            ;;
        3)
            echo "❌ Operação cancelada!"
            exit 0
            ;;
        *)
            echo "❌ Opção inválida!"
            exit 1
            ;;
    esac
fi

# Atualiza informações do remoto
git fetch origin

# Verifica novamente após possível commit
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u})
BASE=$(git merge-base @ @{u})

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "✅ Nada para enviar! Repositório já está sincronizado."
    exit 0
elif [ "$REMOTE" = "$BASE" ]; then
    # Temos commits locais para enviar
    echo ""
    echo "📊 Commits a serem enviados:"
    git log --oneline $REMOTE..$LOCAL
    echo ""
    
    read -p "Confirma o envio? (s/n): " confirm
    if [ "$confirm" = "s" ] || [ "$confirm" = "S" ]; then
        echo ""
        echo "📤 Enviando commits..."
        git push origin $branch
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "✅ Push realizado com sucesso!"
        else
            echo "❌ Erro ao fazer push!"
            exit 1
        fi
    else
        echo "❌ Push cancelado!"
        exit 0
    fi
elif [ "$LOCAL" = "$BASE" ]; then
    echo "⚠️  O branch remoto tem commits que você não tem localmente!"
    echo "Execute ./git-pull.sh primeiro para atualizar seu repositório."
    exit 1
else
    echo "🔀 Divergência detectada!"
    echo ""
    echo "Opções:"
    echo "1) Fazer pull e depois push (recomendado)"
    echo "2) Forçar push (⚠️  CUIDADO: sobrescreve o remoto)"
    echo "3) Cancelar"
    echo ""
    read -p "Escolha uma opção (1/2/3): " choice
    
    case $choice in
        1)
            echo "📥 Fazendo pull primeiro..."
            git pull origin $branch
            
            if [ $? -eq 0 ]; then
                echo "📤 Agora fazendo push..."
                git push origin $branch
                
                if [ $? -eq 0 ]; then
                    echo "✅ Sincronização completa!"
                else
                    echo "❌ Erro no push após pull!"
                    exit 1
                fi
            else
                echo "❌ Erro no pull! Resolva os conflitos e tente novamente."
                exit 1
            fi
            ;;
        2)
            echo ""
            echo "⚠️  ATENÇÃO: Você está prestes a forçar o push!"
            echo "Isso irá sobrescrever o histórico remoto."
            read -p "Tem certeza? Digite 'CONFIRMAR' para continuar: " force_confirm
            
            if [ "$force_confirm" = "CONFIRMAR" ]; then
                echo "🔴 Forçando push..."
                git push --force origin $branch
                
                if [ $? -eq 0 ]; then
                    echo "✅ Push forçado realizado!"
                else
                    echo "❌ Erro no push forçado!"
                    exit 1
                fi
            else
                echo "❌ Push forçado cancelado!"
                exit 0
            fi
            ;;
        3)
            echo "❌ Operação cancelada!"
            exit 0
            ;;
        *)
            echo "❌ Opção inválida!"
            exit 1
            ;;
    esac
fi

echo ""
echo "🎉 Processo concluído!"
