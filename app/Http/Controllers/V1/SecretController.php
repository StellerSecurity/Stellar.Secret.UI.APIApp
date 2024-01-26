<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
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

        $secret = $this->secretService->add(
            [
                'id' => $request->input('id'),
                'message' => $request->input('message'),
                'expires_at' => $request->input('expires_at'),
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
    public function delete(Request $request): JsonResponse
    {
        $delete = $this->secretService->delete($request->input('id'))->object();

        return response()->json($delete);
    }

}
