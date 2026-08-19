<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoclaveMachine extends Model
{
    use HasFactory;

    protected $table = 'autoclave_machines';

    protected $primaryKey = 'machine_id';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'machine_name',
        'location',
        'install_date',
        'status',
    ];

    protected $casts = [
        'install_date' => 'date',
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
     * Semua sesi (bisa lebih dari 1 per hari, lihat session_number) di mesin ini.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AutoclaveSession::class, 'machine_id', 'machine_id');
    }
}
