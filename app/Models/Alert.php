<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = 'alerts';
    protected $primaryKey = 'alert_id';

    public $incrementing = false;
    protected $keyType = 'string';

    const UPDATED_AT = null;
    const CREATED_AT = 'triggered_at';

    protected $fillable = [
        'garment_id',
        'tag_id',
        'device_id',
        'alert_type',
        'severity',
        'message',
        'status',
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
}
