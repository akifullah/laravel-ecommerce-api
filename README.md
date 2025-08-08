## Laravel E-commerce API

A Laravel 12 starter for building an e‑commerce REST API, pre-configured with Sanctum API tokens and an example `Address` model + relations.

### Tech stack
- **Framework**: Laravel 12 (PHP ^8.2)
- **Auth**: Laravel Sanctum (API tokens)
- **Tooling**: Vite 7, Tailwind CSS 4, Axios
- **Testing**: Pest 3, PHPUnit
- **Queue**: Built-in queue worker (dev script runs `queue:listen`)

### Requirements
- **PHP**: 8.2+
- **Composer**: 2.x
- **Node.js**: 18+ (for Vite 7)
- **Database**: SQLite (default) or MySQL/PostgreSQL

### Getting started
1) Clone and install dependencies
```bash
git clone https://github.com/akifullah/laravel-ecommerce-api.git laravel-ecommerce-api
cd laravel-ecommerce-api
composer install
npm install
```

2) Environment
```bash
cp .env.example .env
php artisan key:generate
```

3) Database
- **SQLite (quick start)**
  ```bash
  mkdir -p database
  touch database/database.sqlite
  # In .env set: DB_CONNECTION=sqlite and clear other DB_* creds
  ```
- **MySQL/PostgreSQL**: Update `.env` `DB_*` values accordingly.

4) Migrate (optionally seed)
```bash
php artisan migrate --seed
```

5) Run the app (all-in-one)
```bash
composer run dev
```
This starts the PHP dev server, queue worker, and Vite dev server concurrently.
Alternatively run individually:
```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### API & Authentication
- Sanctum is installed and configured for token-based auth (`auth:sanctum`).
- Example protected route: `GET /api/user`.

Create a personal access token (PAT) for your first user:
```bash
php artisan tinker
>>> $u = App\Models\User::first();
>>> $token = $u->createToken('dev')->plainTextToken;
>>> $token
```

Call the protected endpoint with the token:
```bash
curl -H "Authorization: Bearer <token>" http://127.0.0.1:8000/api/user
```

Notes:
- Tokens do not expire by default (`config/sanctum.php` `expiration` is `null`).
- Stateful SPA cookie domains are prefilled for local dev; for token auth this is not required.

### Data model (starter)
- **User**
  - Traits: `HasApiTokens`, `SoftDeletes`, `HasFactory`, `Notifiable`
  - Relations: `addresses()`, `defaultBillingAddress()`, `defaultShippingAddress()`
  - Extra fields: `phone`, `wallet_balance`, `loyality_points`, `preferences` (json), `metadata` (json), `locale`, `timezone`, consent flags, 2FA columns, soft deletes.
  - Password auto-hashed via mutator; additional casts for booleans/arrays/datetimes.
- **Address**
  - Belongs to `User`
  - Fields: `label`, `first_name`, `last_name`, `company`, `address_line1/2`, `city`, `state`, `postal_code`, `country_code`, `phone`, `is_default_shipping`, `is_default_billing`, `latitude`, `longitude`
  - Soft deletes; helpful indices for lookups.

### Seeding
- `php artisan db:seed` creates:
  - 1 `User` (random email, password is `password`)
  - 1 `Address` linked to `user_id = 1`
- To see the seeded user and create a token:
  ```bash
  php artisan tinker
  >>> App\Models\User::first(['id','email']);
  >>> App\Models\User::first()->createToken('dev')->plainTextToken;
  ```

### Project scripts
- **Composer**
  - **dev**: Run app server, queue worker, and Vite together
  - **test**: Clear config and run the test suite
- **NPM**
  - **dev**: Start Vite dev server
  - **build**: Production asset build

### Routes
- Default web route: `GET /` returns the `welcome` view (see `routes/web.php`).
- API (see `routes/api.php`):
  - `GET /api/user` — returns the authenticated user (requires `Authorization: Bearer <token>`)

### Build assets
```bash
npm run build
```

### Tests
```bash
php artisan test
```

### Deployment notes
- Ensure proper `APP_KEY` and `APP_ENV/APP_DEBUG` settings.
- Configure your web server to serve `public/index.php`.
- Run `php artisan config:cache route:cache view:cache` for optimized builds.

### License
MIT
