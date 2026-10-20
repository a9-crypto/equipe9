#!/usr/bin/env bash
# Garde-fou de l'atelier : la branche main ne reçoit jamais de commit, de merge ou de push direct.
# Claude Code exécute ce hook avant chaque commande Bash (voir .claude/settings.json).
# Code de sortie 2 = commande bloquée. Le message d'erreur part vers Claude.

entree=$(cat)

# Extrait le champ "command" du JSON reçu sur l'entrée standard.
commande=$(printf '%s' "$entree" | sed -nE 's/.*"command"[[:space:]]*:[[:space:]]*"(([^"\\]|\\.)*)".*/\1/p')

case "$commande" in
  *git*) ;;
  *) exit 0 ;;
esac

bloque() {
  echo "Bloqué par le garde-fou de l'atelier : $1" >&2
  echo "Crée ou utilise la branche equipe-XX. La fusion dans main passe par une pull request sur github.com." >&2
  exit 2
}

# Push explicite vers main, quelle que soit la branche courante
if printf '%s' "$commande" | grep -Eq 'git[[:space:]].*push.*([[:space:]:]|^)main([[:space:]]|$)'; then
  bloque "push vers main interdit."
fi

branche=$(git -C "${CLAUDE_PROJECT_DIR:-.}" symbolic-ref --short -q HEAD 2>/dev/null)
if [ "$branche" = "main" ] || [ "$branche" = "master" ]; then
  if printf '%s' "$commande" | grep -Eq 'git[[:space:]]+(commit|merge|push|rebase|cherry-pick|reset[[:space:]]+--hard)([[:space:]]|$)'; then
    bloque "tu es sur la branche $branche. Les commits, merges et push y sont interdits."
  fi
fi

exit 0
