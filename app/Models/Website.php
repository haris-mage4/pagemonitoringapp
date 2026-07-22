<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    /** @use HasFactory<\Database\Factories\WebsiteFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'environment',
        'schedule',
        'enabled',
    ];

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
}
