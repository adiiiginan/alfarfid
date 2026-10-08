<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GarmentCategory extends Model
{
    use HasFactory, Auditable;

    protected $table = 'garment_categories';

    protected $primaryKey = 'category_id';

    // Primary key berupa UUID (char 36), bukan auto-increment integer.
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom created_at ada, tapi tidak ada updated_at di tabel ini.
    const UPDATED_AT = null;

    protected $fillable = [
        'category_name',
        'description',
        'default_max_cycle',
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
     * Satu kategori bisa dipakai banyak garment (item baju).
     */
    public function garments(): HasMany
    {
        return $this->hasMany(Garment::class, 'category_id', 'category_id');
    }
}
