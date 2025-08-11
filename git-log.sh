#!/bin/bash

# Script para automatizar visualização de logs e histórico do Git
# Uso: ./git-log.sh

echo "=== GIT LOG VIEWER ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Função para mostrar logs básicos
show_basic_logs() {
    echo ""
    echo "📋 Logs básicos:"
    echo "1) Últimos 10 commits"
    echo "2) Últimos 20 commits"
    echo "3) Últimos 50 commits"
    echo "4) Todos os commits"
    echo ""
    read -p "Escolha uma opção (1-4): " log_choice
    
    case $log_choice in
        1)
            git log --oneline -10
            ;;
        2)
            git log --oneline -20
            ;;
        3)
            git log --oneline -50
            ;;
        4)
            git log --oneline
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs com filtros de tempo
show_time_filtered_logs() {
    echo ""
    echo "⏰ Logs filtrados por tempo:"
    echo "1) Commits de hoje"
    echo "2) Commits da última semana"
    echo "3) Commits do último mês"
    echo "4) Commits do último ano"
    echo "5) Commits desde data específica"
    echo "6) Commits entre duas datas"
    echo ""
    read -p "Escolha uma opção (1-6): " time_choice
    
    case $time_choice in
        1)
            git log --oneline --since="midnight"
            ;;
        2)
            git log --oneline --since="1 week ago"
            ;;
        3)
            git log --oneline --since="1 month ago"
            ;;
        4)
            git log --oneline --since="1 year ago"
            ;;
        5)
            read -p "Data (formato: YYYY-MM-DD): " start_date
            if [ ! -z "$start_date" ]; then
                git log --oneline --since="$start_date"
            fi
            ;;
        6)
            read -p "Data inicial (YYYY-MM-DD): " start_date
            read -p "Data final (YYYY-MM-DD): " end_date
            if [ ! -z "$start_date" ] && [ ! -z "$end_date" ]; then
                git log --oneline --since="$start_date" --until="$end_date"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs com filtros de autor
show_author_filtered_logs() {
    echo ""
    echo "👤 Logs filtrados por autor:"
    echo "1) Buscar por nome de autor"
    echo "2) Buscar por email de autor"
    echo "3) Listar todos os autores"
    echo "4) Commits de autor específico"
    echo ""
    read -p "Escolha uma opção (1-4): " author_choice
    
    case $author_choice in
        1)
            read -p "Nome do autor: " author_name
            if [ ! -z "$author_name" ]; then
                echo ""
                echo "🔍 Buscando commits de '$author_name':"
                git log --oneline --author="$author_name"
            fi
            ;;
        2)
            read -p "Email do autor: " author_email
            if [ ! -z "$author_email" ]; then
                echo ""
                echo "🔍 Buscando commits de '$author_email':"
                git log --oneline --author="$author_email"
            fi
            ;;
        3)
            echo ""
            echo "👥 Autores do repositório:"
            git log --format='%aN <%aE>' | sort -u
            ;;
        4)
            echo ""
            echo "👥 Autores disponíveis:"
            git log --format='%aN' | sort -u | head -10
            echo "..."
            echo ""
            read -p "Nome do autor: " author_name
            if [ ! -z "$author_name" ]; then
                echo ""
                echo "📊 Estatísticas de '$author_name':"
                git log --author="$author_name" --pretty=format:"%h - %an, %ar : %s" --stat
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs com filtros de mensagem
show_message_filtered_logs() {
    echo ""
    echo "💬 Logs filtrados por mensagem:"
    echo "1) Buscar por texto na mensagem"
    echo "2) Buscar por regex na mensagem"
    echo "3) Buscar por tipo de commit (feat, fix, etc.)"
    echo ""
    read -p "Escolha uma opção (1-3): " message_choice
    
    case $message_choice in
        1)
            read -p "Texto a buscar: " search_text
            if [ ! -z "$search_text" ]; then
                echo ""
                echo "🔍 Buscando commits com '$search_text':"
                git log --oneline --grep="$search_text"
            fi
            ;;
        2)
            read -p "Regex a buscar: " search_regex
            if [ ! -z "$search_regex" ]; then
                echo ""
                echo "🔍 Buscando commits com regex '$search_regex':"
                git log --oneline --grep="$search_regex"
            fi
            ;;
        3)
            echo ""
            echo "📝 Tipos de commit comuns:"
            echo "feat: nova funcionalidade"
            echo "fix: correção de bug"
            echo "docs: documentação"
            echo "style: formatação"
            echo "refactor: refatoração"
            echo "test: testes"
            echo "chore: tarefas de manutenção"
            echo ""
            read -p "Tipo de commit: " commit_type
            if [ ! -z "$commit_type" ]; then
                echo ""
                echo "🔍 Buscando commits do tipo '$commit_type':"
                git log --oneline --grep="^$commit_type:"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs com filtros de arquivo
show_file_filtered_logs() {
    echo ""
    echo "📁 Logs filtrados por arquivo:"
    echo "1) Histórico de arquivo específico"
    echo "2) Commits que modificaram arquivo"
    echo "3) Buscar por extensão de arquivo"
    echo ""
    read -p "Escolha uma opção (1-3): " file_choice
    
    case $file_choice in
        1)
            read -p "Nome do arquivo: " file_name
            if [ ! -z "$file_name" ]; then
                echo ""
                echo "📋 Histórico do arquivo '$file_name':"
                git log --oneline --follow -- "$file_name"
            fi
            ;;
        2)
            read -p "Nome do arquivo: " file_name
            if [ ! -z "$file_name" ]; then
                echo ""
                echo "📊 Commits que modificaram '$file_name':"
                git log --oneline --stat -- "$file_name"
            fi
            ;;
        3)
            read -p "Extensão (ex: .js, .py, .md): " file_ext
            if [ ! -z "$file_ext" ]; then
                echo ""
                echo "🔍 Buscando commits em arquivos '$file_ext':"
                git log --oneline --name-only --grep=".*" | grep "$file_ext" | sort -u
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs com gráfico
show_graph_logs() {
    echo ""
    echo "📊 Logs com gráfico:"
    echo "1) Gráfico simples"
    echo "2) Gráfico com todos os branches"
    echo "3) Gráfico com tags"
    echo "4) Gráfico com estatísticas"
    echo ""
    read -p "Escolha uma opção (1-4): " graph_choice
    
    case $graph_choice in
        1)
            git log --graph --oneline --all -20
            ;;
        2)
            git log --graph --oneline --all --decorate -30
            ;;
        3)
            git log --graph --oneline --all --decorate --tags -30
            ;;
        4)
            git log --graph --oneline --all --decorate --stat -10
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para logs detalhados
show_detailed_logs() {
    echo ""
    echo "🔍 Logs detalhados:"
    echo "1) Commit específico por hash"
    echo "2) Diff de commit específico"
    echo "3) Estatísticas de commits"
    echo "4) Log com patches"
    echo ""
    read -p "Escolha uma opção (1-4): " detail_choice
    
    case $detail_choice in
        1)
            echo ""
            echo "Commits recentes:"
            git log --oneline -10
            echo ""
            read -p "Hash do commit: " commit_hash
            if [ ! -z "$commit_hash" ]; then
                echo ""
                echo "📋 Detalhes do commit $commit_hash:"
                git show --stat $commit_hash
            fi
            ;;
        2)
            echo ""
            echo "Commits recentes:"
            git log --oneline -10
            echo ""
            read -p "Hash do commit: " commit_hash
            if [ ! -z "$commit_hash" ]; then
                echo ""
                echo "📋 Diff do commit $commit_hash:"
                git show $commit_hash
            fi
            ;;
        3)
            echo ""
            echo "📊 Estatísticas de commits:"
            git log --pretty=format:"%h - %an, %ar : %s" --stat
            ;;
        4)
            echo ""
            echo "📋 Log com patches:"
            git log -p -5
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para exportar logs
export_logs() {
    echo ""
    echo "📤 Exportar logs:"
    echo "1) Exportar para arquivo de texto"
    echo "2) Exportar para CSV"
    echo "3) Exportar para JSON (formato simples)"
    echo ""
    read -p "Escolha uma opção (1-3): " export_choice
    
    case $export_choice in
        1)
            read -p "Nome do arquivo: " file_name
            if [ -z "$file_name" ]; then
                file_name="git-log-$(date '+%Y%m%d-%H%M%S').txt"
            fi
            git log --pretty=format:"%h - %an, %ar : %s" > "$file_name"
            echo "✅ Logs exportados para '$file_name'"
            ;;
        2)
            read -p "Nome do arquivo: " file_name
            if [ -z "$file_name" ]; then
                file_name="git-log-$(date '+%Y%m%d-%H%M%S').csv"
            fi
            echo "Hash,Author,Date,Message" > "$file_name"
            git log --pretty=format:"%h,%an,%ar,%s" >> "$file_name"
            echo "✅ Logs exportados para '$file_name'"
            ;;
        3)
            read -p "Nome do arquivo: " file_name
            if [ -z "$file_name" ]; then
                file_name="git-log-$(date '+%Y%m%d-%H%M%S').json"
            fi
            echo "[" > "$file_name"
            git log --pretty=format:'{"hash":"%h","author":"%an","date":"%ar","message":"%s"}' --no-merges | sed 's/}/},/g' | sed '$s/,$//' >> "$file_name"
            echo "]" >> "$file_name"
            echo "✅ Logs exportados para '$file_name'"
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Menu principal
while true; do
    echo ""
    echo "=== MENU LOG VIEWER ==="
    echo "🌿 Branch atual: $(git branch --show-current)"
    echo ""
    echo "1) 📋 Logs básicos"
    echo "2) ⏰ Filtros por tempo"
    echo "3) 👤 Filtros por autor"
    echo "4) 💬 Filtros por mensagem"
    echo "5) 📁 Filtros por arquivo"
    echo "6) 📊 Logs com gráfico"
    echo "7) 🔍 Logs detalhados"
    echo "8) 📤 Exportar logs"
    echo "0) 🚪 Sair"
    echo ""
    read -p "Escolha uma opção: " choice
    
    case $choice in
        1)
            show_basic_logs
            ;;
        2)
            show_time_filtered_logs
            ;;
        3)
            show_author_filtered_logs
            ;;
        4)
            show_message_filtered_logs
            ;;
        5)
            show_file_filtered_logs
            ;;
        6)
            show_graph_logs
            ;;
        7)
            show_detailed_logs
            ;;
        8)
            export_logs
            ;;
        0)
            echo ""
            echo "👋 Até logo!"
            exit 0
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
    
    echo ""
    read -p "Pressione Enter para continuar..."
done
