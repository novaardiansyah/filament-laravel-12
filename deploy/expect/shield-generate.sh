#!/usr/bin/env bash

expect <<EOF
spawn php artisan shield:generate --all
expect "Which panel do you want to generate permissions/policies for?"
send "\r"
expect eof
EOF