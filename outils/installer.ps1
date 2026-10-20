# Kit portable de l'atelier : installe Git sans droits admin, puis relie le dossier au depot GitHub.
# A utiliser seulement si Git n'est pas installe sur le poste (dossier obtenu par "Download ZIP").
# Lancer depuis la racine du dossier :
#   powershell -ExecutionPolicy Bypass -File outils\installer.ps1 -Depot https://github.com/<compte>/<depot>.git
# Options de test : -Destination <dossier>, -ForcerPortable (extrait meme si Git existe), -SansVariables
# Fichier en ASCII volontairement (Windows PowerShell 5.1 lit mal l'UTF-8 sans BOM).

param(
    [string]$Depot = '',
    [string]$Destination = (Join-Path $env:LOCALAPPDATA 'atelier-git'),
    [switch]$ForcerPortable,
    [switch]$SansVariables
)

$ErrorActionPreference = 'Stop'
$racine = Split-Path -Parent $PSScriptRoot
Set-Location $racine

function Etape([string]$texte) { Write-Host "-> $texte" }

# 1. Retire la marque "telecharge depuis Internet" posee par Windows sur les fichiers du zip
Etape 'Deblocage des fichiers du dossier'
Get-ChildItem -Path $racine -Recurse -File | Unblock-File -ErrorAction SilentlyContinue

# 2. Git : celui du poste s'il existe, sinon la version portable livree dans outils\git
$gitPoste = Get-Command git -ErrorAction SilentlyContinue
if ($gitPoste -and -not $ForcerPortable) {
    Etape "Git deja installe sur le poste : $($gitPoste.Source)"
    $git = $gitPoste.Source
} else {
    $archive = Get-ChildItem (Join-Path $PSScriptRoot 'git') -Filter 'PortableGit-*.7z.exe' | Select-Object -First 1
    if (-not $archive) { throw 'Archive PortableGit absente du dossier outils\git.' }

    if (-not (Test-Path (Join-Path $Destination 'cmd\git.exe'))) {
        Etape "Extraction de Git portable dans $Destination (1 a 2 minutes)"
        New-Item -ItemType Directory -Force -Path $Destination | Out-Null
        $p = Start-Process -FilePath $archive.FullName -ArgumentList "-o`"$Destination`"", '-y' -Wait -PassThru
        if ($p.ExitCode -ne 0) { throw "Extraction en echec (code $($p.ExitCode))." }
    }

    # Finalisation propre a PortableGit (le script s'efface une fois execute)
    if (Test-Path (Join-Path $Destination 'post-install.bat')) {
        Etape 'Finalisation de Git portable'
        Start-Process -FilePath (Join-Path $Destination 'git-bash.exe') `
            -ArgumentList '--no-needs-console', '--hide', '--no-cd', '--command=post-install.bat' `
            -WorkingDirectory $Destination -Wait
    }

    $git = Join-Path $Destination 'cmd\git.exe'
    if (-not (Test-Path $git)) { throw 'git.exe introuvable apres extraction.' }

    if (-not $SansVariables) {
        Etape 'Declaration de Git pour Claude (variables de l''utilisateur, sans droits admin)'
        $dossierCmd = Join-Path $Destination 'cmd'
        $pathUser = [Environment]::GetEnvironmentVariable('Path', 'User')
        if (-not (($pathUser -split ';') -contains $dossierCmd)) {
            $nouveau = if ([string]::IsNullOrEmpty($pathUser)) { $dossierCmd } else { "$pathUser;$dossierCmd" }
            [Environment]::SetEnvironmentVariable('Path', $nouveau, 'User')
        }
        [Environment]::SetEnvironmentVariable('CLAUDE_CODE_GIT_BASH_PATH', (Join-Path $Destination 'bin\bash.exe'), 'User')
    }
}

function G {
    & $git @args
    if ($LASTEXITCODE -ne 0) { throw "Commande git en echec : git $($args -join ' ')" }
}

G --version

# 3. Relie le dossier au depot GitHub du binome
if (Test-Path (Join-Path $racine '.git')) {
    Etape 'Le dossier est deja relie a un depot Git.'
} elseif ($Depot) {
    Etape "Liaison au depot $Depot (une fenetre de connexion GitHub peut s'ouvrir)"
    G init -q -b main
    G remote add origin $Depot
    G fetch -q origin main
    G reset -q origin/main
    G branch -q --set-upstream-to=origin/main main
    $ecarts = @(& $git status --porcelain)
    if ($ecarts.Count -gt 0) {
        Write-Host "Attention : $($ecarts.Count) fichier(s) different(s) du depot GitHub."
    } else {
        Etape 'Dossier identique au depot GitHub.'
    }
} else {
    Write-Host 'Aucune adresse de depot fournie : liaison GitHub non faite.'
}

Write-Host ''
Write-Host 'Installation terminee.'
Write-Host 'Fermez l''app Claude, rouvrez-la, ouvrez une nouvelle session sur ce dossier, puis tapez /demarrer.'
