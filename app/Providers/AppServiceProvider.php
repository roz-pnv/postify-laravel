<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use stdClass;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(ResponseFactory $response): void
    {
        $response->macro('api', function (array $data = null, ?bool $success = null, int $status = 200):JsonResponse {
            if (is_null($success)) {
                $success = $status >= 200 && $status < 400;
            }

            if (is_null($data)) {
                $data = new stdClass();
            }

            return response()->json([
                'success' => $success,
                'data'    => $data,
            ], $status);
        });
    }
}
