# PixelForge Studio

A Laravel 12, Vue 3 and Inertia app where users generate AI images through the Pixelforge API and pay with prepaid credits (US dollars). This is my submission for the PromptEdit Senior Product Engineer take-home assessment.

## Links

| | |
| --- | --- |
| Live app | `[https://pixelforge-studio-demo.laravel.cloud](https://pixelforge-studio-demo.laravel.cloud/)` |
| Admin login | `admin@yopmail.com` / `Password@123#` |
| User login | `john@yopmail.com` / `Password@123#` |

## What is built

**Required**

* **Generate.** Prompt, size, quality and up to 4 reference image URLs. The page shows a typical and a maximum price before the user submits.
* **Pricing.** Pixelforge cost plus a 25% markup, rounded up. The up-front price is an estimate. When a generation finishes, the actual price is calculated from the real token usage and rates, and stored.
* **History.** Live status (pending, queued, processing, succeeded, failed), estimated and actual cost, the image, and the error message when something fails. The page polls while any generation is unfinished.
* **Webhooks.** `POST /webhooks/pixelforge` verifies the signature and handles duplicate and out-of-order events.

**Stretch**

* **Admin report.** Daily and per-user spend, provider cost and profit. Provider cost is compared with Pixelforge's `GET /v1/usage` for the same days, and a mismatch is flagged.
* **Balances.** Each user has a credit balance and a wallet history. "Buy credits" adds a package (fake purchase). Generating is blocked when the user cannot afford the maximum price.

## Design decisions

* **Exact money.** Amounts are stored as integer nanodollars, so Pixelforge's fractional costs never lose precision.
* **Hold, then settle.** The maximum price is held when a user submits. When the generation finishes, the user pays the actual price and the rest is refunded. Failed generations are refunded in full.
* **Charged once.** Each generation is settled a single time, even if webhooks arrive twice or out of order.
* **Webhooks.** Signatures are verified, and each event is stored once by its id.
* **Reliability.** Temporary errors are retried, and a scheduled poll catches missed webhooks.
* **Roles.** New sign-ups get the `user` role and 5.00 starting credits. Admins only see reports.

## Run locally

Requirements: PHP 8.2+, Composer, Node 22, npm, PostgreSQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create a PostgreSQL database named `pixelforge-demo` (change the `DB_*` values in `.env` if yours differs), then:

```bash
php artisan migrate --seed
```

Set these in `.env`:

| Variable | What to put |
| --- | --- |
| `PIXELFORGE_API_KEY` | Your Pixelforge API key. |
| `PIXELFORGE_WEBHOOK_SECRET` | The signing secret of the same key. If empty, every webhook is rejected. |
| `PIXELFORGE_BASE_URL` | Leave as is unless Pixelforge says otherwise. |

Start everything:

```bash
composer dev
php artisan schedule:work
```

`composer dev` runs the web server, the queue worker and Vite. `schedule:work` runs the polling job and needs its own terminal.

For webhooks to reach your machine, expose the app with a tunnel (Expose, ngrok or Cloudflare Tunnel) and set the webhook URL of your Pixelforge key to `https://<your-tunnel>/webhooks/pixelforge`. Use a separate key for each environment, because each key has its own signing secret and its own usage report.

## Seeded accounts

All use the password `Password@123#`.

| Email | Role |
| --- | --- |
| admin@yopmail.com | admin |
| john@yopmail.com, alice@yopmail.com, bob@yopmail.com, emma@yopmail.com, david@yopmail.com | user |

## Known limitations

* "Buy credits" is a fake purchase with no payment gateway.
* Image URLs expire on Pixelforge's side. Expired images show a placeholder.
* The admin report covers at most 92 days and uses UTC days, matching how Pixelforge bills.
