<?php
namespace Cyberland\GlobalAudit\Services;

use Cyberland\GlobalAudit\Models\GlobalAuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class GlobalAuditService
{
    public function log(
        ?string $event,
        array $changes = [],
        ?Authenticatable $user = null
    ): GlobalAuditLog {
        $user = $user ?: Auth::user();

        $filteredChanges = Arr::except($changes, config('global-audit.hidden', []));

        return GlobalAuditLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'event' => $event,
            'model_type' => null,
            'model_id' => null,
            'changes' => $filteredChanges,
            'ip_address' => Request::ip(),
            'url' => Request::fullUrl(),
            'user_agent' => Request::userAgent(),
            'http_method' => Request::method(),
            'created_at' => now(),
        ]);
    }
}
