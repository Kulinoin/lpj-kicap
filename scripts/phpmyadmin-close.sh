#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$0")/.."

docker compose stop phpmyadmin

echo
echo "phpMyAdmin ditutup."
