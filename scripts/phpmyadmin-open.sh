#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$0")/.."

docker compose --profile tools up -d phpmyadmin

echo
echo "phpMyAdmin dibuka:"
echo "http://127.0.0.1:8190"
echo
echo "Login:"
echo "Server   : mysql"
echo "Username : lpj_kicap"
echo "Password : lpj_kicap_secret"
echo "Database : lpj_kicap"
