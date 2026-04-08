Param(
  [string]$Out = "deploy_vendas.zip"
)

$Include = @(
  'public','bootstrap','src','routes','resources','vendor','config','storage',
  'composer.json','composer.lock','.htaccess'
)

if (-not (Test-Path storage\logs)) { New-Item -ItemType Directory -Force -Path storage\logs | Out-Null }

if (Test-Path $Out) { Remove-Item $Out -Force }
Compress-Archive -Path $Include -DestinationPath $Out -Force
Write-Host "Gerado: $Out"

