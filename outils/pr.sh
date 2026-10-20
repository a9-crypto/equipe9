#!/usr/bin/env bash
# Pull request sans la CLI gh : réutilise l'identifiant GitHub enregistré par Git lors du push.
# Usage (depuis la racine du dépôt, sur la branche equipe-XX) :
#   bash outils/pr.sh creer <fichier>       crée la pull request de la branche vers main
#                                           (1re ligne du fichier = titre, la suite = description)
#   bash outils/pr.sh commenter <fichier>   publie le fichier en commentaire de la pull request ouverte
# Le jeton GitHub n'est jamais affiché. PHP sert seulement à lire et écrire le JSON.

set -euo pipefail
cd "$(git rev-parse --show-toplevel)"

if [ -x outils/php/php.exe ] && [[ "$(uname -s)" =~ MINGW|MSYS|CYGWIN ]]; then PHP=outils/php/php.exe; else PHP=php; fi

erreur() { echo "Erreur : $1" >&2; exit 1; }

depot=$(git remote get-url origin | sed -E 's#^(https://github\.com/|git@github\.com:)##; s#\.git$##')
proprietaire=${depot%%/*}
branche=$(git symbolic-ref --short HEAD)
[ "$branche" != "main" ] || erreur "tu es sur main. Passe sur la branche equipe-XX."

jeton=$(printf 'protocol=https\nhost=github.com\n\n' | GIT_TERMINAL_PROMPT=0 git credential fill 2>/dev/null | sed -n 's/^password=//p')
[ -n "$jeton" ] || erreur "aucun identifiant GitHub enregistré. Lance d'abord git push."

entetes=$(mktemp)
trap 'rm -f "$entetes"' EXIT
printf 'Authorization: Bearer %s\nAccept: application/vnd.github+json\nX-GitHub-Api-Version: 2022-11-28\n' "$jeton" > "$entetes"
api() { curl -sS -H @"$entetes" "$@"; }

# Numéro et adresse de la pull request ouverte pour la branche, ou rien
pr_ouverte() {
  api "https://api.github.com/repos/$depot/pulls?state=open&head=$proprietaire:$branche" |
    "$PHP" -r '$d = json_decode(stream_get_contents(STDIN), true); if (is_array($d) && isset($d[0]["number"])) echo $d[0]["number"], " ", $d[0]["html_url"];'
}

# Affiche l'adresse renvoyée par GitHub, ou son message d'erreur
resultat() {
  "$PHP" -r '$d = json_decode(stream_get_contents(STDIN), true);
    if (isset($d["html_url"])) { echo $argv[1], " : ", $d["html_url"], "\n"; exit(0); }
    fwrite(STDERR, "Erreur GitHub : " . ($d["message"] ?? "réponse inattendue") . "\n"); exit(1);' "$1"
}

action=${1:-}
fichier=${2:-}
[ -f "$fichier" ] || erreur "fichier introuvable : $fichier"

case "$action" in
  creer)
    existante=$(pr_ouverte)
    if [ -n "$existante" ]; then echo "Pull request déjà ouverte : ${existante#* }"; exit 0; fi
    corps=$("$PHP" -r '$l = preg_split("/\R/", file_get_contents($argv[1]), 2);
      echo json_encode(["title" => trim($l[0]), "head" => $argv[2], "base" => "main", "body" => trim($l[1] ?? "")],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);' "$fichier" "$branche")
    api -X POST "https://api.github.com/repos/$depot/pulls" -d "$corps" | resultat "Pull request créée"
    ;;
  commenter)
    existante=$(pr_ouverte)
    [ -n "$existante" ] || erreur "aucune pull request ouverte pour la branche $branche."
    corps=$("$PHP" -r 'echo json_encode(["body" => file_get_contents($argv[1])], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);' "$fichier")
    api -X POST "https://api.github.com/repos/$depot/issues/${existante%% *}/comments" -d "$corps" | resultat "Commentaire publié"
    ;;
  *)
    erreur "usage : bash outils/pr.sh creer <fichier> | commenter <fichier>"
    ;;
esac
