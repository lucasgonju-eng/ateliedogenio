#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if ! command -v php >/dev/null 2>&1; then
  echo "php não encontrado no PATH. Instale o PHP 8.2 para executar os testes."
  exit 1
fi

if [ ! -f "$ROOT_DIR/vendor/bin/phpunit" ]; then
  echo "Dependências não instaladas. Execute 'composer install' antes de rodar os testes."
  exit 1
fi

cd "$ROOT_DIR"
PHPUnit="vendor/bin/phpunit"

echo "Executando testes unitários..."
$PHPUnit --testsuite Unit

echo "Executando testes de integração..."
$PHPUnit --testsuite Integration

echo "Testes finalizados com sucesso."

