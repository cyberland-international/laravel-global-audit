<?php
namespace Cyberland\GlobalAudit;

use Cyberland\GlobalAudit\Observers\GlobalAuditObserver;
use Cyberland\GlobalAudit\Services\GlobalAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class GlobalAuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/global-audit.php' => config_path('global-audit.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        if (!config('global-audit.enabled', true)) {
            return;
        }

        $models = Cache::rememberForever('global_audit_models', function () {
            return $this->discoverModels();
        });

        foreach ($models as $modelClass) {
            /** @var class-string<Model> $modelClass */
            $modelClass::observe(GlobalAuditObserver::class);
        }
    }

    public function register(): void
    {
        $this->app->singleton('global-audit', function () {
            return new GlobalAuditService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/global-audit.php',
            'global-audit'
        );
    }

    protected function discoverModels(): array
    {
        $baseNamespace = config('global-audit.models_namespace', 'App\\Models\\');
        $basePath = app_path('Models');

        if (!is_dir($basePath)) {
            return [];
        }

        $files = scandir($basePath);
        $models = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $class = $baseNamespace . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $models[] = $class;
            }
        }

        return $models;
    }
}