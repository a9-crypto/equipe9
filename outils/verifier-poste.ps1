# Controle d'un poste avant l'atelier ticketing.
# Lancer depuis la racine du depot :
#   powershell -ExecutionPolicy Bypass -File outils\verifier-poste.ps1
# Option -CI : ignore les controles propres a un poste utilisateur (app Claude).
# Fichier en ASCII volontairement (Windows PowerShell 5.1 lit mal l'UTF-8 sans BOM).

param([switch]$CI)

$ErrorActionPreference = 'Continue'
$racine = Split-Path -Parent $PSScriptRoot
Set-Location $racine
$script:ko = 0

function Resultat([bool]$ok, [string]$nom, [string]$parade = '') {
    if ($ok) {
        Write-Host "[OK] $nom" -ForegroundColor Green
    } else {
        $script:ko++
        Write-Host "[KO] $nom" -ForegroundColor Red
        if ($parade) { Write-Host "     -> $parade" -ForegroundColor Yellow }
    }
}

function Joignable([string]$url) {
    try {
        Invoke-WebRequest -Uri $url -Method Head -UseBasicParsing -TimeoutSec 10 | Out-Null
        return $true
    } catch {
        # Une reponse HTTP (401, 404...) prouve que le site est joignable.
        return [bool]$_.Exception.Response
    }
}

Write-Host "Controle du poste - atelier ticketing"
Write-Host "Dossier : $racine"
Write-Host ""

# 1. Git
$git = Get-Command git -ErrorAction SilentlyContinue
Resultat ($null -ne $git) "Git installe ($(if ($git) { (git --version) }))" "Installer Git for Windows, ou utiliser le kit portable : commande /installer dans l'app Claude."

# 1 bis. Identite Git (nom et email des commits). Si elle manque, /demarrer la regle pour le depot.
if ($git) {
    $nom = (git config --global user.name) 2>$null
    $mail = (git config --global user.email) 2>$null
    if ($nom -and $mail) {
        Write-Host "[OK] Identite Git : $nom" -ForegroundColor Green
    } else {
        Write-Host "[INFO] Identite Git globale absente : /demarrer la reglera pour le depot." -ForegroundColor Cyan
    }
}

# 2. Git Bash (shell utilise par Claude Code et par les hooks)
$bash = @(
    "$env:ProgramFiles\Git\bin\bash.exe",
    "$env:LOCALAPPDATA\Programs\Git\bin\bash.exe"
) | Where-Object { Test-Path $_ } | Select-Object -First 1
Resultat ($null -ne $bash) "Git Bash present ($bash)" "Reinstaller Git for Windows avec Git Bash."

# 3. App Claude desktop
if (-not $CI) {
    $claude = Get-StartApps -ErrorAction SilentlyContinue | Where-Object { $_.Name -like '*Claude*' }
    Resultat ($null -ne $claude) "App Claude installee" "Installer l'app Claude desktop (https://claude.ai/download)."
}

# 4. Reseau
foreach ($url in 'https://github.com', 'https://api.anthropic.com', 'https://claude.ai', 'https://raw.githubusercontent.com') {
    Resultat (Joignable $url) "Acces HTTPS a $url" "Demander l'ouverture de ce domaine sur le proxy."
}

# 5. PHP portable
$php = Join-Path $racine 'outils\php\php.exe'
$version = & $php -v 2>&1
$code = $LASTEXITCODE
$parade = "Verifier qu'AppLocker ou l'antivirus autorise outils\php\php.exe."
if ($code -eq -1073741515) {
    $parade = "Runtime Visual C++ absent : installer le Redistribuable Visual C++ 2015-2022 x64, ou copier vcruntime140.dll dans outils\php."
}
Resultat ($code -eq 0) "PHP portable demarre ($(($version | Select-Object -First 1)))" $parade

$modules = & $php -m 2>&1
Resultat (($modules -contains 'pdo_sqlite') -and ($modules -contains 'mbstring')) "Extensions pdo_sqlite et mbstring chargees" "Verifier outils\php\php.ini et le dossier outils\php\ext."

# 6. Tests de l'API
$tests = & $php outils\tests\smoke.php 2>&1
Resultat ($LASTEXITCODE -eq 0) "Tests de fumee ($(($tests | Select-Object -Last 1)))" "Lancer outils\php\php.exe outils\tests\smoke.php pour voir le detail."

# 7. Serveur local
$port = 8000
$serveur = Start-Process -FilePath $php -ArgumentList 'outils/serveur.php' -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 3
$repond = $false
try {
    $meta = Invoke-RestMethod -Uri "http://127.0.0.1:$port/api/meta" -TimeoutSec 10
    $repond = $meta.projects.Count -gt 0
} catch { }
Resultat $repond "Serveur local http://127.0.0.1:$port" "Port $port occupe ou bloque. Essayer : set PORT=8001 puis relancer."
if ($serveur) {
    # Arrete le lanceur et le serveur PHP qu'il a demarre
    Get-CimInstance Win32_Process -Filter "ParentProcessId=$($serveur.Id)" -ErrorAction SilentlyContinue |
        ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
    Stop-Process -Id $serveur.Id -Force -ErrorAction SilentlyContinue
}

Write-Host ""
if ($script:ko -eq 0) {
    Write-Host "Poste pret pour l'atelier." -ForegroundColor Green
} else {
    Write-Host "$script:ko controle(s) en echec. Voir les parades ci-dessus." -ForegroundColor Red
}
exit $script:ko
