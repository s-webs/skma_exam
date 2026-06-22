# Production deploy

After each deploy, from the project root:

```bash
bash deploy/post-deploy.sh
```

## Queue worker (email)

Outgoing mail is queued (`QUEUE_CONNECTION=database`). A worker must run continuously:

```bash
php artisan queue:work database --sleep=3 --tries=3 --queue=high,default
```

- `high` — registration verification codes (processed first)
- `default` — exam invites and result reports

Example Supervisor program (`/etc/supervisor/conf.d/psihotest-queue.conf`):

```ini
[program:psihotest-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/psihotest/artisan queue:work database --sleep=3 --tries=3 --queue=high,default
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/psihotest/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Alternatively, use `deploy/queue-worker.service.example` with systemd.

Failed jobs: `php artisan queue:failed` and `php artisan queue:retry all`.

## Static files

Exam images are ordinary public files:

- `storage/app/public/questions/*.png`
- `storage/app/public/answers/*.png`

URLs: `/storage/questions/...` and `/storage/answers/...` (requires `storage:link`).

If images do not load, check that `public/storage` symlink exists and nginx serves files from `public/`.

Include `deploy/nginx-upload-limits.conf.example` in the site `server` block if uploads fail.
