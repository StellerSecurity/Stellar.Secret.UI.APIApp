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
            $expires_at = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"))->addHours($expires_at);
        }

        $password = $request->input('password');

        if($password !== null && strlen($password) > 0) {
            $password = hash("sha512", $password);
        }

        $secret = $this->secretService->add(
            [
                'id' => $request->input('id'),
                'message' => $request->input('message'),
                'expires_at' => $expires_at,
                'password' => $password
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

        if( $request->input('id') === null) {
            return response()->json(['response_code' => 400]);
        }

        $id = hash("sha512", $request->input('id'));

        $find = $this->secretService->view($id)->object();

        if($find !== null) {
            return response()->json(['response_code' => 400]);
        }

        $this->secretService->delete($id)->object();

        return response()->json([$find, 'response_code' => 200]);
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
