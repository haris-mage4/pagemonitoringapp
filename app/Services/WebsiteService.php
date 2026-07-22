<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;

class WebsiteService
{
    public function list(): Collection
    {
        return Website::query()->withCount('pages')->get();
    }

    public function find(Website $website): Website
    {
        return $website->load('pages');
    }

    public function create(array $data): Website
    {
        return Website::create($data);
    }

    public function update(Website $website, array $data): Website
    {
        $website->update($data);

        return $website;
    }

    public function delete(Website $website): void
    {
        $website->delete();
    }

    public function setEnabled(Website $website, bool $enabled): Website
    {
        $website->update(['enabled' => $enabled]);

        return $website;
    }
}
