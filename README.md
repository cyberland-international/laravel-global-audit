# Laravel Global Audit

Simple, automatic Eloquent audit logging for Laravel applications.

This package records create, update and delete events for your Eloquent models into a dedicated `global_audit_logs` table.

---

## Installation

Require the package via Composer:

```bash
composer require cyberland-international/laravel-global-audit
```

Laravel 10+ will auto-discover the service provider.

If you prefer to register it manually, add this to `config/app.php`:

```php
'providers' => [
    // ...
    Cyberland\LaravelGlobalAudit\GlobalAuditServiceProvider::class,
],
```

---

## Configuration

Publish the config and migration files:

```bash
php artisan vendor:publish --provider="Cyberland\\LaravelGlobalAudit\\GlobalAuditServiceProvider"
```

This will publish:

- Config: `config/global-audit.php`
- Migration: `database/migrations/0000_00_00_000000_create_global_audit_logs_table.php`

Run the migration:

```bash
php artisan migrate
```

Open `config/global-audit.php` to adjust:

- Which models are audited
- Which attributes are ignored
- User resolution logic, etc. (depending on your config options)

---

## Usage

Once installed, the package listens globally to Eloquent model events and records audit logs whenever a model is created, updated or deleted (according to your config).

You can query the audit logs via the `AuditLog` model:

```php
use Cyberland\LaravelGlobalAudit\Models\AuditLog;

$logs = AuditLog::latest()->take(50)->get();
```

Each log entry typically contains:

- `user_id` (Auth ID)
- `http_method`
- `event` (e.g. `created`, `updated`, `deleted`)
- `model_type` and `model_id`
- `ip_address`
- `url`
- `user_agent`
- `changes` (JSONB) (contains `old`, `new`, and `dirty`) 
- Timestamps (created_at)

> Note: Adjust the exact fields above to match your `global_audit_logs` migration.

---

## Scoping and Filtering

You can filter logs by model, user, or event, for example:

```php
$logs = AuditLog::where('model_type', App\Models\User::class)
    ->where('event', 'updated')
    ->get();
```

Feel free to add your own scopes on the `AuditLog` model to encapsulate common queries.

---

## Testing

Run your test suite (if present) with:

```bash
php artisan test
```

or, if using PHPUnit directly:

```bash
phpunit
```

---

## Requirements

- PHP ^8.1
- Laravel 10.x (`illuminate/support` and `illuminate/database` ^10.0)

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
