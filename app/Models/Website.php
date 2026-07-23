<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    /** @use HasFactory<\Database\Factories\WebsiteFactory> */
    use HasFactory;

    public const SCHEDULE_INTERVAL_MINUTES = [
        'hourly' => 60,
        'every_6_hours' => 60 * 6,
        'daily' => 60 * 24,
        'weekly' => 60 * 24 * 7,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'base_url',
        'environment',
        'schedule',
        'enabled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function uptimeChecks(): HasMany
    {
        return $this->hasMany(UptimeCheck::class);
    }
}
