<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SchedulerController extends Controller
{
    public function __invoke(): Response
    {
        // Preserve the existing timer trigger while the base API is private.
        // The destination is fixed; caller input is never forwarded.
        try {
            Http::connectTimeout(5)->timeout(90)
                ->get('https://stellarsecretapiprod.azurewebsites.net/api/v1/secretcontroller/scheduler')
                ->throw();
        } catch (\Throwable $exception) {
            Log::warning('Secret expiration scheduler failed.');

            return response('', 502);
        }

        return response('', 204);
    }
}
