# Production deploy (exam.skma.edu.kz)

## After each deploy

From the project root on the server:

```bash
bash deploy/post-deploy.sh
sudo systemctl reload php8.3-fpm   # adjust PHP version
```

## Exam images

Files must exist under:

- `storage/app/public/questions/` — e.g. `N6w60qq94yquestions.png`
- `storage/app/public/answers/` — e.g. `8LfJCWKf82answers.png`

`post-deploy.sh` runs `php artisan storage:link`.

If images live elsewhere, set `EXAM_MEDIA_ROOT` in `.env` to that directory.

## Nginx

Include both snippets in the `server` block:

1. `deploy/nginx-upload-limits.conf.example`
2. `deploy/nginx-exam-media.conf.example`

Then: `sudo nginx -t && sudo systemctl reload nginx`

## Smoke test

```bash
curl -I "https://exam.skma.edu.kz/exam-media/N6w60qq94yquestions.png"
curl -I "https://exam.skma.edu.kz/exam-media/8LfJCWKf82answers.png"
```

Expect `HTTP/2 200`. Reload the exam page (F5) after deploy so `image_url` in the page uses `/exam-media/`, not `/media/`.
