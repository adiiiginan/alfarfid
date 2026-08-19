<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garment extends Model
{
    use HasFactory;

    protected $table = 'garments';

    protected $primaryKey = 'garment_id';

    // Primary key berupa UUID (char 36), bukan auto-increment integer.
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'garment_code',
        'category_id',
        'division_id',
        'size',
        'current_tag_id',
        'max_cycle_limit',
        'current_cycle_count',
        'status',
        'date_first_used',
        'date_retired',
    ];

    protected $casts = [
        'date_first_used' => 'date',
        'date_retired'    => 'date',
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
     * Jenis/kategori baju (FR-04).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(GarmentCategory::class, 'category_id', 'category_id');
    }

    /**
     * Divisi pemilik baju ini (menentukan warna baju).
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    /**
     * Tag RFID yang sedang aktif terpasang ke garment ini.
     */
    public function currentTag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'current_tag_id', 'tag_id');
    }

    /**
     * Riwayat scan untuk garment ini (FR-05).
     */
    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'garment_id', 'garment_id');
    }

    /**
     * Riwayat bind/unbind tag ke garment ini (FR-19).
     */
    public function tagBindings(): HasMany
    {
        return $this->hasMany(TagGarmentBinding::class, 'garment_id', 'garment_id');
    }

    /**
     * Alert yang terkait garment ini (FR-03).
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'garment_id', 'garment_id');
    }
}
