<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $primaryKey = 'device_id';

    // Primary key berupa UUID (char 36), bukan auto-increment integer.
    public $incrementing = false;
    protected $keyType = 'string';

    // Tabel ini hanya punya created_at, tidak ada updated_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'device_name',
        'location',
        'division_id',
        'mac_address',
        'api_key_hash',
        'status',
        'last_heartbeat_at',
        'installed_at',
    ];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
        'installed_at'      => 'date',
    ];

    protected $hidden = [
        'api_key_hash',
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

    /**
     * Divisi/area tempat device ini dipasang.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    /**
     * Semua event scan yang tercatat lewat device ini.
     */
    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'device_id', 'device_id');
    }

    /**
     * Alert terkait device ini (mis. device_offline).
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'device_id', 'device_id');
    }

    /**
     * Cek apakah device sedang online berdasarkan status kolom.
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->status === 'online';
    }
}
