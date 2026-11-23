#!/usr/bin/env bash
# Prépare l'archive à déposer sur l'hébergement (cPanel) : code du dernier commit,
# dépendances PHP de production et fichiers CSS/JS compilés.
# Usage (Git Bash, depuis la racine du projet) : bash scripts/build-deploy.sh
# Résultat : ../elc-deploy.tar.gz
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WORK="$ROOT/../elc-deploy"
ARCHIVE="$ROOT/../elc-deploy.tar.gz"

cd "$ROOT"

if [ -n "$(git status --porcelain)" ]; then
    echo "Attention : des modifications ne sont pas commitées, elles ne seront PAS dans l'archive." >&2
fi

echo "1/4 Compilation des fichiers CSS/JS"
npm run build

echo "2/4 Export du code (dernier commit)"
rm -rf "$WORK" "$ARCHIVE"
mkdir -p "$WORK"
git archive HEAD | tar -x -C "$WORK"
cp -r public/build "$WORK/public/build"
rm -rf "$WORK/tests" "$WORK/scripts" "$WORK/.github"

echo "3/4 Dépendances PHP de production"
(cd "$WORK" && composer install --no-dev --optimize-autoloader --no-interaction --no-progress)
rm -f "$WORK/bootstrap/cache/"*.php

echo "4/4 Archive"
tar -czf "$ARCHIVE" -C "$WORK" .
rm -rf "$WORK"

echo "Archive prête : $(cd "$(dirname "$ARCHIVE")" && pwd)/$(basename "$ARCHIVE")"
