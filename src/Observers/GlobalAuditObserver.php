<?php
namespace Cyberland\GlobalAudit\Observers;

use Cyberland\GlobalAudit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class GlobalAuditObserver
{
    protected array $excludedSensitiveAttributes;

    public function __construct()
    {
        $this->excludedSensitiveAttributes = config('global-audit.hidden', [
            'password',
            'remember_token',
            'api_token',
            'token',
        ]);
    }

    public function created(Model $model): void
    {
        DB::connection()->afterCommit(function () use ($model) {
            $this->log('create', $model);
        });
    }

    public function updated(Model $model): void
    {
        $original = $model->getOriginal();
        $dirty = $model->getDirty();

        DB::connection()->afterCommit(function () use ($model, $original, $dirty) {
            $this->log('update', $model, $original, $dirty);
        });
    }

    public function deleted(Model $model): void
    {
        DB::connection()->afterCommit(function () use ($model) {
            $this->log('delete', $model);
        });
    }

    protected function log(string $action, Model $model, array $original = [], array $dirty = []): void
    {
        $skipped = config('global-audit.skip_models', []);
        $skipped[] = AuditLog::class;

        foreach ($skipped as $skip) {
            if ($model instanceof $skip) {
                return;
            }
        }

        $user = Auth::user();

        if ($action === 'update') {
            $old = Arr::except($original, $this->excludedSensitiveAttributes);
            $new = Arr::except($model->getAttributes(), $this->excludedSensitiveAttributes);
            $dirtyFiltered = Arr::except($dirty, $this->excludedSensitiveAttributes);

            if (empty($dirtyFiltered)) {
                return;
            }

            $changes = [
                'old' => $old,
                'new' => $new,
                'dirty' => $dirtyFiltered,
            ];
        } else {
            $changes = Arr::except($model->getAttributes(), $this->excludedSensitiveAttributes);
        }

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'url' => Request::fullUrl(),
            'user_agent' => Request::userAgent(),
            'http_method' => Request::method(),
        ]);
    }
}