<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    /** @use HasFactory<\Database\Factories\ScanResultFactory> */
    use HasFactory;

    protected $fillable = [
        'scan_id',
        'device',
        'performance',
        'accessibility',
        'seo',
        'best_practices',
        'fcp',
        'lcp',
        'cls',
        'tbt',
        'speed_index',
        'tti',
        'raw_json',
        'exit_code',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'raw_json' => 'array',
            'cls' => 'float',
        ];
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
