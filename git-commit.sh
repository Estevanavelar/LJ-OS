#!/bin/bash

# Script para automatizar commits no Git
# Uso: ./comitar.sh "mensagem do commit" ou apenas ./comitar.sh

echo "=== GIT COMMIT AUTOMÁTICO ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Mostra o status atual
echo "📊 Status atual do repositório:"
git status -s

# Se não houver mudanças, sai
if [ -z "$(git status --porcelain)" ]; then
    echo "✅ Nenhuma mudança para commit!"
    exit 0
fi

# Adiciona todas as mudanças
echo ""
echo "📝 Adicionando todas as mudanças..."
git add -A

# Define a mensagem do commit
if [ -z "$1" ]; then
    # Se não foi fornecida mensagem, pede ao usuário
    echo ""
    echo "Digite a mensagem do commit (ou pressione Enter para mensagem padrão):"
    read -r commit_message
    
    if [ -z "$commit_message" ]; then
        # Mensagem padrão com timestamp
        commit_message="Atualização automática - $(date '+%d/%m/%Y %H:%M:%S')"
    fi
else
    # Usa a mensagem fornecida como argumento
    commit_message="$1"
fi

# Faz o commit
echo ""
echo "💾 Fazendo commit com a mensagem: $commit_message"
git commit -m "$commit_message"

# Mostra o resultado
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Commit realizado com sucesso!"
    echo ""
    echo "📊 Último commit:"
    git log -1 --oneline
else
    echo "❌ Erro ao fazer commit!"
    exit 1
fi
