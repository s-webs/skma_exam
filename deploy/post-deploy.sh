#!/usr/bin/env bash
# Run on the server after git pull (from project root).
set -euo pipefail

php artisan optimize:clear
php artisan storage:link --force

echo "Ensure exam images exist under storage/app/public/questions and storage/app/public/answers"
