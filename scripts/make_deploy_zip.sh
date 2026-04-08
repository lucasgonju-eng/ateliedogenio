#!/usr/bin/env bash
set -euo pipefail

OUT="${1:-deploy_vendas.zip}"
rm -f "$OUT"
zip -r "$OUT" \
  public bootstrap src routes resources vendor config storage \
  composer.json composer.lock .htaccess \
  -x "storage/logs/*" "**/.git/*" "**/.vscode/*" "**/docs/*" "**/Chat*"
echo "Gerado: $OUT"

