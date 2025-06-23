#!/bin/bash

# ! Direktori project Laravel
PROJECT_DIR="/www/wwwroot/laravel-12.novaardiansyah.my.id"
PHP_BIN="/www/server/php/83/bin/php"

echo "Force stop schedule:work for project $PROJECT_DIR ..."

# ! Stop hanya schedule:work milik project ini
ps aux | grep 'php artisan schedule:work' | grep "$PROJECT_DIR" | grep -v grep | awk '{print $2}' | xargs -r kill -9

sleep 3

echo "schedule:work start..."

cd "$PROJECT_DIR" || { echo "Gagal masuk ke direktori project!"; exit 1; }
$PHP_BIN artisan schedule:work > storage/logs/schedule.log 2>&1 &

echo "Done. schedule:work sudah berjalan ulang di $PROJECT_DIR"