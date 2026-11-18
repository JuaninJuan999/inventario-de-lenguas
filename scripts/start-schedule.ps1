# Arranca php artisan schedule:work desde la raiz del proyecto.
# Documentacion: docs/sincronizacion-inventario.md
#
# Uso:
#   powershell -ExecutionPolicy Bypass -File scripts\start-schedule.ps1

$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..')

if (-not (Test-Path '.\artisan')) {
    Write-Error 'No se encontro artisan. Ejecute desde el proyecto inventario-de-lenguas.'
}

Write-Host '[inventario-de-lenguas] schedule:work en ejecucion. Ctrl+C para detener.'
Write-Host 'Ver: docs/sincronizacion-inventario.md'
php artisan schedule:work
