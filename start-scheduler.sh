#!/bin/bash
# Corre el scheduler de Laravel en modo desarrollo.
# Ejecuta las tareas programadas cada minuto (equivale al cron: * * * * *)
#
# Uso:         ./start-scheduler.sh
# Detener:     Ctrl+C
#
# PRODUCCIÓN: en vez de este script, agrega al crontab del servidor:
#   * * * * * cd /var/www/spec-app && php artisan schedule:run >> /dev/null 2>&1

APP_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Iniciando Laravel Scheduler (desarrollo)..."
echo "Directorio: $APP_DIR"
echo "Presiona Ctrl+C para detener"
echo ""

cd "$APP_DIR"

while true; do
    echo "$(date '+%Y-%m-%d %H:%M:%S'): Ejecutando schedule:run..."
    php artisan schedule:run >> /dev/null 2>&1
    echo "$(date '+%Y-%m-%d %H:%M:%S'): Esperando 60 segundos..."
    sleep 60
done
