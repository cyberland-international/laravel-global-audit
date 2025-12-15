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
    Cyberland\GlobalAudit\GlobalAuditServiceProvider::class,
],
```

---

## Configuration

Publish the config and migration files:

```bash
php artisan vendor:publish --provider="Cyberland\GlobalAudit\GlobalAuditServiceProvider" --tag=config
php artisan vendor:publish --provider="Cyberland\GlobalAudit\GlobalAuditServiceProvider" --tag=migrations
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

You can query the audit logs via the `GlobalAuditLog` model:

```php
use Cyberland\GlobalAudit\Models\GlobalAuditLog;

$logs = GlobalAuditLog::latest()->take(50)->get();
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
use Cyberland\GlobalAudit\Models\GlobalAuditLog;
use App\Models\User;

$logs = GlobalAuditLog::where('model_type', User::class)
    ->where('event', 'updated')
    ->get();
```

Feel free to add your own scopes on the `GlobalAuditLog` model to encapsulate common queries.

---

## Manual logging (facade)

In addition to model lifecycle events, you can manually create audit logs for actions that do not involve Eloquent models (e.g. logins, logouts, custom API calls).

First, (optionally) register the facade alias in your application's `config/app.php`:

```php
'aliases' => [
    // ...
    'GlobalAudit' => Cyberland\\GlobalAudit\\Facades\\GlobalAudit::class,
],
```

Then you can log events anywhere in your application, for example in an auth controller:

```php
use GlobalAudit; // if alias is registered
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! auth()->attempt($credentials)) {
            GlobalAudit::log(__METHOD__, [
                'status' => 'failed',
                'email' => $request->input('email'),
                'guard' => 'web',
            ]);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();

        GlobalAudit::log(__METHOD__, [
            'status' => 'success',
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json(['message' => 'Logged in']);
    }
}
```

The `log` method signature is:

```php
GlobalAudit::log(string $event, array $changes = [], ?\Illuminate\Contracts\Auth\Authenticatable $user = null): GlobalAuditLog
```

- `$event` is a short descriptor of what happened (e.g. `__METHOD__`, `'login'`, `'logout'`, `'api_call'`).
- `$changes` is any contextual data you want to store (sensitive keys listed in `global-audit.hidden` will be removed).
- `$user` is optional; when omitted, the current authenticated user (if any) is used.

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
