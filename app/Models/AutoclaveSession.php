<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoclaveSession extends Model
{
    use HasFactory;

    protected $table = 'autoclave_sessions';

    protected $primaryKey = 'session_id';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'machine_id',
        'session_date',
        'session_number',
        'start_time',
        'end_time',
        'operator_id',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(AutoclaveMachine::class, 'machine_id', 'machine_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id', 'user_id');
    }

    /**
     * Semua item baju yang ikut dalam sesi ini (lewat scan_events).
     */
    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'session_id', 'session_id');
    }
}
