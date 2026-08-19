<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'division_name',
        'color_name',
        'color_hex',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $division) {
            if (empty($division->division_id)) {
                $division->division_id = (string) Str::uuid();
            }
        });
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'division_id', 'division_id');
    }
}
