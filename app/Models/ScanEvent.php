<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScanEvent extends Model
{
    protected $table = 'scan_events';
    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // tabel ini tidak punya kolom updated_at

    protected $fillable = [
        'tag_id',
        'garment_id',
        'session_id',
        'device_id',
        'event_type',
        'scan_timestamp',
        'cycle_count_after',
        'location',
    ];

    protected $casts = [
        'scan_timestamp' => 'datetime',
    ];

    /**
     * Generate UUID di sisi PHP SEBELUM insert, supaya Eloquent langsung
     * tahu nilai primary key-nya (tidak bergantung pada DEFAULT uuid()
     * di MySQL, yang tidak pernah "dikembalikan" ke model karena
     * $incrementing = false).
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'tag_id');
    }

    public function garment()
    {
        return $this->belongsTo(Garment::class, 'garment_id', 'garment_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function session()
    {
        return $this->belongsTo(AutoclaveSession::class, 'session_id', 'session_id');
    }
}
