<?php

use App\Jobs\CheckPageErrorsJob;
use App\Models\Page;
use App\Services\PageErrorService;

test('handle delegates to PageErrorService with the page', function () {
    $page = Page::factory()->create();

    $this->mock(PageErrorService::class, function ($mock) use ($page) {
        $mock->shouldReceive('check')->once()->with(Mockery::on(fn ($arg) => $arg->is($page)));
    });

    (new CheckPageErrorsJob($page))->handle(app(PageErrorService::class));
});
