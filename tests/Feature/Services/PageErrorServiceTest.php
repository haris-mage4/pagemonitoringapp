<?php

use App\Models\Page;
use App\Models\PageError;
use App\Services\PageErrorService;
use Illuminate\Support\Facades\Process;

test('check stores new errors returned by the capture script', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'success' => true,
            'errors' => [
                ['message' => 'TypeError: x is not a function', 'source' => 'https://example.test/app.js:12', 'stack' => null],
            ],
        ])),
    ]);

    $page = Page::factory()->create();

    $newErrors = app(PageErrorService::class)->check($page);

    expect($newErrors)->toHaveCount(1);
    $this->assertDatabaseHas('page_errors', [
        'page_id' => $page->id,
        'message' => 'TypeError: x is not a function',
        'occurrence_count' => 1,
    ]);
});

test('check bumps occurrence_count for a repeat error instead of duplicating', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'success' => true,
            'errors' => [
                ['message' => 'Repeated error', 'source' => null, 'stack' => null],
            ],
        ])),
    ]);

    $page = Page::factory()->create();
    $service = app(PageErrorService::class);

    $service->check($page);
    $secondRun = $service->check($page);

    expect($secondRun)->toHaveCount(0);
    expect(PageError::where('page_id', $page->id)->count())->toBe(1);
    expect(PageError::where('page_id', $page->id)->first()->occurrence_count)->toBe(2);
});

test('check returns no errors and logs nothing crashes when the script fails', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'node: command not found', exitCode: 127),
    ]);

    $page = Page::factory()->create();

    $newErrors = app(PageErrorService::class)->check($page);

    expect($newErrors)->toBe([]);
    expect(PageError::where('page_id', $page->id)->count())->toBe(0);
});

test('check returns no errors when the script output is not valid json', function () {
    Process::fake([
        '*' => Process::result(output: 'not json'),
    ]);

    $page = Page::factory()->create();

    $newErrors = app(PageErrorService::class)->check($page);

    expect($newErrors)->toBe([]);
});
