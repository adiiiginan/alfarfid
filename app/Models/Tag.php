<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    use HasFactory, Auditable;

    protected $table = 'tags';

    protected $primaryKey = 'tag_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tag_uid',
        'tag_type',
        'manufacturer',
        'rated_max_cycles',
        'total_cycles_used',
        'status',
        'date_first_deployed',
        'notes',
        'division_id',
    ];

    protected $casts = [
        'date_first_deployed' => 'date',
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
     * Garment yang saat ini memakai tag ini (kalau ada).
     */
    public function currentGarment(): HasOne
    {
        return $this->hasOne(Garment::class, 'current_tag_id', 'tag_id');
    }

    // tambahkan di Tag.php, dekat currentGarment()
    public function currentBinding(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TagGarmentBinding::class, 'tag_id', 'tag_id')
            ->where('is_current', true);
    }

    /**
     * Riwayat lengkap bind/unbind tag ini ke berbagai garment (FR-19).
     */
    public function garmentBindings(): HasMany
    {
        return $this->hasMany(TagGarmentBinding::class, 'tag_id', 'tag_id');
    }

    /**
     * Riwayat scan yang melibatkan tag ini.
     */
    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'tag_id', 'tag_id');
    }

    /**
     * Sisa umur fisik tag (dibanding rated_max_cycles), dipakai untuk
     * keputusan retire tag saat proses Tag Reassignment (FR-19).
     */
    public function getRemainingPhysicalCyclesAttribute(): int
    {
        return max(0, $this->rated_max_cycles - $this->total_cycles_used);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }
}
