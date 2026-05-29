#!/usr/bin/env bash
# Run on the server after git pull / deploy (from project root).
set -euo pipefail

php artisan optimize:clear
php artisan route:clear
php artisan storage:link --force

echo "Exam media route:"
php artisan route:list --name=exam-media

echo "Sample image URL:"
php artisan tinker --execute="echo route('public.exam-media.show', ['filename' => 'test.png']);"
