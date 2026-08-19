<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagGarmentBinding extends Model
{
    use HasFactory;

    protected $table = 'tag_garment_bindings';

    protected $primaryKey = 'binding_id';

    public $incrementing = false;
    protected $keyType = 'string';

    // Tidak ada created_at/updated_at standar di tabel ini.
    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'garment_id',
        'bound_at',
        'unbound_at',
        'bound_by',
        'unbind_reason',
        'is_current',
    ];

    protected $casts = [
        'bound_at'   => 'datetime',
        'unbound_at' => 'datetime',
        'is_current' => 'boolean',
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

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'tag_id');
    }

    public function garment(): BelongsTo
    {
        return $this->belongsTo(Garment::class, 'garment_id', 'garment_id');
    }

    public function boundBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bound_by', 'user_id');
    }
    public function boundByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bound_by', 'user_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
