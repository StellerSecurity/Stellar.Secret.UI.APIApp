<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecretController extends Controller
{

    private SecretService $secretService;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {

        // in hours.
        $expires_at = $request->input('expires_at');

        if($expires_at > 0 && $expires_at !== null) {
            $expires_at = Carbon::createFromFormat('Y-m-d H:i:s', $expires_at)->addHours($expires_at);
        }

        $secret = $this->secretService->add(
            [
                'id' => $request->input('id'),
                'message' => $request->input('message'),
                'expires_at' => $expires_at,
                'password' => $request->input('password')
            ]
        )->object();

        return response()->json($secret);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {

        $find = $this->secretService->view(
            hash("sha512", $request->input('id'))
        )->object();

        return response()->json($find);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $delete = $this->secretService->delete(
            hash("sha512", $request->input('id'))
        )->object();

        return response()->json($delete);
    }

}
