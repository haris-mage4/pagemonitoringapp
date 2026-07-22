<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Scan extends Model
{
    /** @use HasFactory<\Database\Factories\ScanFactory> */
    use HasFactory;

    protected $fillable = [
        'page_id',
        'status',
        'trigger',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scanResult(): HasOne
    {
        return $this->hasOne(ScanResult::class);
    }
}
