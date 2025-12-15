<?php
namespace Cyberland\GlobalAudit\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalAuditLog extends Model
{
    protected $table = 'global_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'model_type',
        'model_id',
        'ip_address',
        'url',
        'user_agent',
        'http_method',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}