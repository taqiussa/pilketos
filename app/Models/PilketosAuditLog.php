<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilketosAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'pilketos_audit_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
