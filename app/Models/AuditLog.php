<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'log_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Tabel ini cuma punya changed_at, bukan created_at/updated_at standar Laravel
    public $timestamps = false;

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'old_value',
        'new_value',
        'changed_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $log) {
            if (empty($log->log_id)) {
                $log->log_id = (string) Str::uuid();
            }
            if (empty($log->changed_at)) {
                $log->changed_at = now();
            }
        });
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by', 'user_id');
    }
}
