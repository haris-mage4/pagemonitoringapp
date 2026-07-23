<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageError extends Model
{
    /** @use HasFactory<\Database\Factories\PageErrorFactory> */
    use HasFactory;

    protected $fillable = [
        'page_id',
        'fingerprint',
        'message',
        'source',
        'stack',
        'first_seen_at',
        'last_seen_at',
        'occurrence_count',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
