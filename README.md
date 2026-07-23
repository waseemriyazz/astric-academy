<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo"></a></p>

# Astryx Academy — Backend

Laravel backend for the Astryx Academy platform: courses, student portal, admin panel, and payment processing (Easebuzz + PayGlocal).

This README is written for the whole team, not just people who already know Laravel — every command below explains **what it does** and **when you'd run it**, not just the command itself.

---

## First-time setup

Run these once, in order, when setting up the project on a new machine.

```bash
# 1. Install PHP dependencies (like npm install, but for PHP — reads composer.json,
#    downloads everything into vendor/)
composer install

# 2. Copy the example environment file to a real one, then fill in your own values
#    (database, API keys, etc.) in the new .env file
cp .env.example .env

# 3. Generate the app's encryption key (Laravel needs this to encrypt sessions,
#    cookies, etc. — without it, the app won't boot)
php artisan key:generate

# 4. Create the database tables (runs every migration file in database/migrations/,
#    in order, building the schema from scratch)
php artisan migrate

# 5. Populate the database with initial data — admin user, sample courses,
#    testimonials, FAQs, and the payment gateway config rows (Easebuzz/PayGlocal)
php artisan db:seed
```

## Running the app day-to-day

```bash
# Start the backend server (serves the API + admin panel at http://localhost:8000)
php artisan serve

# In a separate terminal, if you're also touching frontend assets (admin panel CSS/JS):
npm run dev
```

The React frontend (course pages, checkout) is a **separate project** (`landing-New`) — see its own README for how to run that.

---

## Everyday commands

### Database

```bash
# Add new tables/columns after pulling code with new migration files
php artisan migrate

# ⚠️ Destroys ALL data and rebuilds the database from scratch, then reseeds it.
# Only use this locally when you want a totally clean slate.
php artisan migrate:fresh --seed

# Check which migrations have run and which haven't
php artisan migrate:status
```

### Clearing cached config/routes (run this if changes to `.env` or `routes/` don't seem to take effect)

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Interactive shell — run PHP/database code directly without writing a script

```bash
php artisan tinker
```
Useful for one-off checks, e.g. `App\Models\Payment::where('status', 'paid')->count()`.

### See all available routes

```bash
php artisan route:list
```

---

## Payment gateway commands

These are custom commands built specifically for this project's payment integration — not standard Laravel.

### `php artisan payments:status [txnid]`
Check a payment's **live status directly against the gateway** (Easebuzz or PayGlocal) — the same check the app itself uses to decide success/failure. Doesn't rely on webhooks or callbacks having arrived yet.

```bash
# List all payments currently sitting as pending/processing
php artisan payments:status

# Check one specific transaction
php artisan payments:status ACAD_9887a0f0d242441389903f9235c743e7
```

### `php artisan payments:expire-stale`
Sweeps payments that have been stuck as pending/processing for too long (default 30 minutes) and reconciles them against the gateway's status API — marks genuinely completed ones as paid, expires genuinely dead ones, and leaves anything still legitimately in-progress alone. **Runs automatically every 15 minutes** in production (see `routes/console.php`) — you don't normally need to run this manually, but it's safe to if you want an immediate check.

```bash
php artisan payments:expire-stale
```

### `php artisan payglocal:test`
Sends one real test transaction to PayGlocal's sandbox using whatever credentials are currently saved in the admin panel, and prints the full result — safe to run anytime, never affects which gateway is actually live for real checkouts.

```bash
php artisan payglocal:test
php artisan payglocal:test --amount=5.00   # optional: change the test amount
```

---

## Admin panel

Visit `/admin/gateways` (while logged in as an admin) to:
- Switch which payment gateway is live (Easebuzz or PayGlocal) — **only one can be active at a time**, toggling one automatically turns the other off
- Enter/update gateway credentials (merchant ID, keys, etc.)
- Switch between sandbox and production mode per gateway

---

## Testing PayGlocal locally

PayGlocal needs to send a callback to our server after a customer completes checkout. Since your local machine (`localhost:8000`) isn't reachable from the internet, you need a tunnel for that callback to reach you during local testing:

```bash
# 1. Install ngrok (one-time, via Homebrew on Mac)
brew install ngrok

# 2. Sign up free at https://dashboard.ngrok.com/signup, get your authtoken from
#    https://dashboard.ngrok.com/get-started/your-authtoken, then run once:
ngrok config add-authtoken YOUR_TOKEN_HERE

# 3. With `php artisan serve` already running in another terminal, start the tunnel:
ngrok http 8000
```

ngrok will print a public URL like `https://abc123.ngrok-free.dev`. Put that in your `.env`:
```
PAYGLOCAL_CALLBACK_URL=https://abc123.ngrok-free.dev/api/payment/payglocal/success
```
Then `php artisan config:clear` so the new value takes effect. This is **dev-only** — in production, the real domain is used directly and no tunnel is needed.

---

## Dependency management (composer)

Composer is PHP's equivalent of npm — it manages this project's PHP packages, listed in `composer.json`.

```bash
# Install everything listed in composer.json (run after cloning, or after pulling
# changes that added new dependencies)
composer install

# Add a new package
composer require vendor/package-name

# Update all packages to their latest allowed versions (careful — can introduce
# breaking changes, usually only done deliberately)
composer update
```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. Full documentation: [laravel.com/docs](https://laravel.com/docs).
