Param(
  [string]$Out = "deploy_vendas_subdir.zip"
)

$ErrorActionPreference = "Stop"

$stagingRoot = "_deploy_vendas"
$targetRoot = Join-Path $stagingRoot "vendas"

if (Test-Path $stagingRoot) { Remove-Item $stagingRoot -Recurse -Force }
New-Item -ItemType Directory -Path $targetRoot -Force | Out-Null

$include = @(
  "public","bootstrap","src","routes","resources","vendor","config","storage",
  "composer.json","composer.lock",".env.example"
)

foreach ($entry in $include) {
  if (Test-Path $entry) {
    Copy-Item -Path $entry -Destination $targetRoot -Recurse -Force
  }
}

# .htaccess para quando o app roda fisicamente em /vendas
$htaccess = @"
RewriteEngine On

# Serve arquivos de /imagens a partir de /public/imagens
RewriteRule ^imagens/(.*)$ public/imagens/$1 [L]

# /vendas ou /vendas/
RewriteRule ^$ public/index.php [L]

# Se o alvo já é /vendas/public/*
RewriteRule ^public/ - [L]

# Se existe arquivo/diretório na raiz da pasta /vendas, encaminha para public/
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^(.+)$ public/$1 [L]

RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+)$ public/$1 [L]

# Demais rotas para o front controller
RewriteRule ^ public/index.php [L]
"@

Set-Content -Path (Join-Path $targetRoot ".htaccess") -Value $htaccess -Encoding UTF8

if (-not (Test-Path (Join-Path $targetRoot "storage/logs"))) {
  New-Item -ItemType Directory -Force -Path (Join-Path $targetRoot "storage/logs") | Out-Null
}

if (Test-Path $Out) { Remove-Item $Out -Force }
Compress-Archive -Path (Join-Path $stagingRoot "*") -DestinationPath $Out -Force

Write-Host "Gerado: $Out"
