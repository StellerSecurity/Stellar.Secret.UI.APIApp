<?php

namespace Tests\Feature;

use App\Http\Controllers\V1\SchedulerController;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    private const URL = 'https://stellarsecretapiprod.azurewebsites.net/api/v1/secretcontroller/scheduler';

    public function test_success_returns_no_upstream_content(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::URL => Http::response('internal response', 200)]);

        $response = (new SchedulerController())();

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        Http::assertSent(fn ($request) => $request->url() === self::URL
            && $request->method() === 'GET' && $request->data() === []);
        Http::assertSentCount(1);
    }

    public function test_upstream_failure_is_not_reported_as_success(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::URL => Http::response('internal error details', 500)]);

        $response = (new SchedulerController())();

        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function test_connection_failure_returns_bad_gateway(): void
    {
        Http::preventStrayRequests();
        Http::fake(fn () => throw new ConnectionException('connection failed'));

        $response = (new SchedulerController())();

        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }
}
