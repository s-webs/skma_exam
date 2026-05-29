# Production deploy

After each deploy, from the project root:

```bash
bash deploy/post-deploy.sh
```

Exam images are ordinary public files:

- `storage/app/public/questions/*.png`
- `storage/app/public/answers/*.png`

URLs: `/storage/questions/...` and `/storage/answers/...` (requires `storage:link`).

If images do not load, check that `public/storage` symlink exists and nginx serves files from `public/`.

Include `deploy/nginx-upload-limits.conf.example` in the site `server` block if uploads fail.
