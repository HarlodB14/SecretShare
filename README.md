# SecretShare - Secured Password Sharing Tool

A Laravel 12 application to share sensitive secrets using one-time or limited-use links.
Secrets are encrypted at rest and deleted after use.

## Features

- Create a secret-sharing link from a password/secret value
- Reveal a secret by opening the generated link
- One-time links by default (secret is deleted after first view)
- Optional expiration date (`expires_at`)
- Optional usage limit (`max_views`)
- Basic abuse protection via rate limiting on secret creation

## Tech Stack

- PHP 8.2+
- Laravel 12
- Tailwind CSS 4
- MySQL (runtime and tests in this workspace)

## Security Notes

- Secrets are encrypted with Laravel `Crypt::encryptString()` before persistence
- Plaintext secrets are never stored directly in the database
- Links use random 64-character tokens (`Str::random(64)`)
- Inaccessible secrets (expired / exhausted) are removed on access
- Secret creation endpoint is rate-limited (`throttle:secret-creation`)

## Local Setup

1. Install dependencies
2. Configure `.env`
3. Generate app key (if needed)
4. Run migrations
5. Serve app

    ```bash
    composer install
    npm install
    php artisan key:generate
    php artisan migrate
    php artisan serve
    npm run dev
    ```

## Running Tests

Feature tests are configured in `phpunit.xml` for MySQL database `secret_sharing_test`.

```bash
composer dump-autoload
php artisan test --testsuite=Feature
```

## Expiration Runtime (Queue + Scheduler)

Automatic expiry deletion relies on delayed jobs and a scheduled safety-net command.
Use a non-sync queue driver for real delayed execution:

```dotenv
QUEUE_CONNECTION=database
APP_TIMEZONE=Europe/Amsterdam
```

If you adjust timezone/env values, run `php artisan optimize:clear` before re-testing.

Run both processes during local development:

```bash
php artisan schedule:work
php artisan queue:work --queue=default --tries=3
```

Windows helper script: `setup-scheduler.bat`
