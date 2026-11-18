@echo off
REM =============================================================================
REM Arranca el worker del scheduler de Laravel (php artisan schedule:work).
REM
REM Para que inventario:sync-lenguas corra en segundo plano (cada N minutos
REM segun INVENTARIO_LENGUAS_SCHEDULE_MINUTES), este proceso debe permanecer activo.
REM
REM Documentacion completa:
REM   docs\sincronizacion-inventario.md
REM
REM Uso:
REM   1. Abrir esta carpeta en CMD o doble clic (dejar la ventana abierta).
REM   2. En servidor Linux preferir cron: * * * * * php artisan schedule:run
REM =============================================================================

cd /d "%~dp0.."

if not exist "artisan" (
  echo No se encontro artisan. Ejecute este .bat desde el proyecto inventario-de-lenguas.
  pause
  exit /b 1
)

echo.
echo [inventario-de-lenguas] schedule:work en ejecucion.
echo Deje esta ventana abierta. Ctrl+C para detener.
echo Ver: docs\sincronizacion-inventario.md
echo.

php artisan schedule:work

pause
