#!/bin/bash

# ! Direktori project Laravel
PROJECT_DIR="/www/wwwroot/laravel-12.novaardiansyah.my.id"
PHP_BIN="/www/server/php/83/bin/php"

echo "Force stop queue:work for project $PROJECT_DIR ..."

# ! Stop hanya queue:work milik project ini
ps aux | grep 'php artisan queue:work' | grep "$PROJECT_DIR" | grep -v grep | awk '{print $2}' | xargs -r kill -9

sleep 3

cd "$PROJECT_DIR" || { echo "Gagal masuk ke direktori project!"; exit 1; }

echo "--queue:restart..."
$PHP_BIN artisan queue:restart

echo "queue:work start..."
nohup $PHP_BIN artisan queue:work > storage/logs/queue.log 2>&1 &

echo "Done. Worker queue sudah berjalan ulang di $PROJECT_DIR"