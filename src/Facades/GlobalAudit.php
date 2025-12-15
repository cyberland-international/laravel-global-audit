<?php
namespace Cyberland\GlobalAudit\Facades;

use Illuminate\Support\Facades\Facade;

class GlobalAudit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'global-audit';
    }
}
