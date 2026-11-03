# Production checklist

## Required environment variables

Configure these values in the production environment. Do not commit their real
values:

- `APP_ENV=prod`
- `APP_DEBUG=0`
- `APP_SECRET`
- `DATABASE_URL`
- `MAILER_DSN`

The public web root must point to `public/`, and the application must be served
over HTTPS.

## Deployment

GitHub Actions runs automatic checks and deploys merged changes on `main` through a separate
workflow. See [GITHUB_ACTIONS.md](GITHUB_ACTIONS.md) for the SSH configuration,
preview, backup and production activation. Deployment requires the production secrets and the repository activation variable.
The workflow preserves production configuration and uploads and does not execute
migrations. The manual commands below are separate from the Actions workflow.

Run the following commands for each release:

```bash
composer install --no-dev --classmap-authoritative
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
php bin/console asset-map:compile --env=prod
```

Ensure that the PHP/web-server user can write to:

- `var/cache/`
- `var/log/`
- `public/uploads/covers/`
- `public/uploads/songs/`

## Verification

After deployment:

1. Check `/he`, `/en`, `/he/about`, `/en/about`, `/sitemap.xml`, and
   `/robots.txt`.
2. Send a contact message and verify email delivery.
3. Sign in to the administration and test one image and MP3 upload.
4. Confirm that canonical URLs use the final HTTPS domain.
5. Submit `/sitemap.xml` in Google Search Console.
6. Request indexing for `/he/about` and `/en/about`.

## Backups

Back up both the database and `public/uploads/`. A database-only backup is not
enough because media files are stored on disk.

Recommended minimum:

- daily database backup;
- daily incremental upload backup;
- retention of at least 30 days;
- periodic restore test in a separate environment.

## External items still required

- final domain and HTTPS certificate;
- production SMTP credentials;
- official Open Graph image;
- official external profiles for Schema.org `sameAs`;
- replacement of demonstration songs with final content.
