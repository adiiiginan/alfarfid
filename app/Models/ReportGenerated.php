<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportGenerated extends Model
{
    use HasUuids;

    protected $table = 'reports_generated'; // <-- tambahkan ini
    protected $primaryKey = 'report_id';
    public $timestamps = false;

    protected $fillable = [
        'report_type',
        'generated_by',
        'date_range_start',
        'date_range_end',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end'   => 'date',
        'generated_at'     => 'datetime',
    ];

    public function generatedByUser()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }
}
