<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;

class PageService
{
    public function listForWebsite(Website $website): Collection
    {
        return $website->pages()->get();
    }

    public function find(Page $page): Page
    {
        return $page->load('website');
    }

    public function create(Website $website, array $data): Page
    {
        return $website->pages()->create($data);
    }

    public function update(Page $page, array $data): Page
    {
        $page->update($data);

        return $page;
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function setEnabled(Page $page, bool $enabled): Page
    {
        $page->update(['enabled' => $enabled]);

        return $page;
    }
}
