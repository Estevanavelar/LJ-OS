# Script de Instalacao Git Tools para Windows (PowerShell)
# Execute como: .\install-git-tools.ps1

param(
    [switch]$Force
)

# Verifica se esta executando como administrador
function Test-Administrator {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Funcao para escrever com cores
function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

# Banner
Write-ColorOutput "==========================================" "Cyan"
Write-ColorOutput "        INSTALADOR GIT TOOLS           " "Cyan"
Write-ColorOutput "         Windows PowerShell            " "Cyan"
Write-ColorOutput "==========================================" "Cyan"
Write-Host ""

# Verifica se esta no diretorio correto
if (-not (Test-Path "git-menu.sh")) {
    Write-ColorOutput "ERRO: Execute este script no diretorio dos Git Tools!" "Red"
    Write-ColorOutput "Navegue ate a pasta 'Comandos git' e execute novamente." "Yellow"
    exit 1
}

Write-ColorOutput "Configurando Git Tools para Windows..." "Blue"

# Cria diretorio de instalacao global
$installDir = "$env:USERPROFILE\.git-tools"
if (-not (Test-Path $installDir)) {
    New-Item -ItemType Directory -Path $installDir -Force | Out-Null
}

# Copia todos os scripts para o diretorio global
Write-ColorOutput "Copiando scripts para $installDir..." "Yellow"
Copy-Item "*.sh" $installDir -Force
Copy-Item "*.bat" $installDir -Force

# Cria arquivo de perfil PowerShell
$profilePath = $PROFILE.CurrentUserAllHosts
$profileDir = Split-Path $profilePath -Parent

if (-not (Test-Path $profileDir)) {
    New-Item -ItemType Directory -Path $profileDir -Force | Out-Null
}

# Cria arquivo de aliases PowerShell
$aliasesFile = "$installDir\git-tools-aliases.ps1"
Write-ColorOutput "Criando arquivo de aliases PowerShell..." "Yellow"

$aliasesContent = @"
# Git Tools - Aliases para PowerShell
# Carregado automaticamente pelo perfil

# Menu principal
Set-Alias -Name gitmenu -Value "$installDir\git-menu.sh"

# Operacoes basicas
Set-Alias -Name gcommit -Value "$installDir\git-commit.sh"
Set-Alias -Name gcomitar -Value "$installDir\comitar.sh"
Set-Alias -Name gpull -Value "$installDir\git-pull.sh"
Set-Alias -Name gpush -Value "$installDir\git-push.sh"
Set-Alias -Name gsync -Value "$installDir\git-sync.sh"

# Gerenciamento
Set-Alias -Name gbranch -Value "$installDir\git-branch.sh"
Set-Alias -Name gstash -Value "$installDir\git-stash.sh"
Set-Alias -Name gmerge -Value "$installDir\git-merge.sh"
Set-Alias -Name gtag -Value "$installDir\git-tag.sh"
Set-Alias -Name glog -Value "$installDir\git-log.sh"

# Aliases curtos
Set-Alias -Name gc -Value gcommit
Set-Alias -Name gp -Value gpush
Set-Alias -Name gl -Value gpull
Set-Alias -Name gs -Value gsync
Set-Alias -Name gb -Value gbranch
Set-Alias -Name gst -Value gstash
Set-Alias -Name gm -Value gmerge
Set-Alias -Name gt -Value gtag
Set-Alias -Name glg -Value glog

# Comando de ajuda
Set-Alias -Name githelp -Value "$installDir\git-help.sh"
Set-Alias -Name help -Value githelp
Set-Alias -Name ghelp -Value githelp

# Funcoes para melhor compatibilidade
function Invoke-GitMenu { & "$installDir\git-menu.sh" }
function Invoke-GitCommit { & "$installDir\git-commit.sh" @args }
function Invoke-GitSync { & "$installDir\git-sync.sh" @args }
function Invoke-GitBranch { & "$installDir\git-branch.sh" }
function Invoke-GitStash { & "$installDir\git-stash.sh" }
function Invoke-GitHelp { & "$installDir\git-help.sh" }

Write-Host "Git Tools carregado! Use 'gitmenu' para comecar ou 'githelp' para ajuda." -ForegroundColor Green
"@

$aliasesContent | Out-File -FilePath $aliasesFile -Encoding UTF8

# Adiciona ao perfil PowerShell
Write-ColorOutput "Configurando perfil PowerShell..." "Blue"
$profileContent = Get-Content $profilePath -ErrorAction SilentlyContinue

if ($profileContent -and $profileContent -match "git-tools-aliases") {
    Write-ColorOutput "Git Tools ja configurado no perfil PowerShell" "Yellow"
} else {
    Add-Content $profilePath "`n# Git Tools - Configuracao automatica"
    Add-Content $profilePath ". '$aliasesFile'"
    Write-ColorOutput "Configuracao adicionada ao perfil PowerShell" "Green"
}

# Cria arquivo de lote para CMD
$batchFile = "$installDir\git-tools.bat"
$batchContent = @"
@echo off
REM Git Tools - Carregador para CMD
REM Adicione este arquivo ao PATH ou execute diretamente

if "%1"=="" (
    echo Git Tools - Comandos disponiveis:
    echo   gitmenu    - Menu principal
    echo   gcommit    - Commit rapido
    echo   gsync      - Sincronizacao completa
    echo   gbranch    - Gerenciar branches
    echo   gstash     - Gerenciar stashes
    echo   githelp    - Ver ajuda completa
    echo.
    echo Exemplo: git-tools.bat gitmenu
) else (
    "%~dp0%1.bat" %2 %3 %4 %5 %6 %7 %8 %9
)
"@

$batchContent | Out-File -FilePath $batchFile -Encoding ASCII

# Cria script de desinstalacao
$uninstallScript = "$installDir\uninstall.ps1"
$uninstallContent = @"
# Script de desinstalacao Git Tools
Write-Host "Desinstalando Git Tools..." -ForegroundColor Yellow

# Remove do perfil PowerShell
`$profilePath = `$PROFILE.CurrentUserAllHosts
`$profileContent = Get-Content `$profilePath -ErrorAction SilentlyContinue
if (`$profileContent) {
    `$newContent = `$profileContent | Where-Object { `$_ -notmatch "git-tools" }
    Set-Content `$profilePath `$newContent
}

# Remove diretorio
Remove-Item "$installDir" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "Git Tools desinstalado com sucesso!" -ForegroundColor Green
"@

$uninstallContent | Out-File -FilePath $uninstallScript -Encoding UTF8

Write-Host ""
Write-ColorOutput "INSTALACAO CONCLUIDA!" "Green"
Write-Host ""
Write-ColorOutput "PROXIMOS PASSOS:" "Cyan"
Write-ColorOutput "1. Reinicie o PowerShell ou execute: . '$aliasesFile'" "White"
Write-ColorOutput "2. Use os comandos em qualquer pasta:" "White"
Write-ColorOutput "   - gitmenu (menu principal)" "White"
Write-ColorOutput "   - gcommit (commit rapido)" "White"
Write-ColorOutput "   - gsync (sincronizacao completa)" "White"
Write-ColorOutput "   - gbranch (gerenciar branches)" "White"
Write-ColorOutput "   - githelp (ver ajuda completa)" "White"
Write-Host ""
Write-ColorOutput "TESTE AGORA:" "Green"
Write-ColorOutput "   'githelp' - Para ver todos os comandos" "White"
Write-ColorOutput "   'gitmenu' - Para abrir o menu principal" "White"
Write-Host ""
Write-ColorOutput "DICA: Use 'gitmenu' para acessar o menu principal!" "Yellow"
Write-ColorOutput "DICA: Use 'githelp' para ver todos os comandos!" "Yellow"
Write-Host ""
Write-ColorOutput "Para desinstalar: . '$uninstallScript'" "Blue"
Write-ColorOutput "Scripts instalados em: $installDir" "Blue"
Write-ColorOutput "Para CMD: $batchFile" "Blue"
