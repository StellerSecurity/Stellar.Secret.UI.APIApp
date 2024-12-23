<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecretController extends Controller
{

    private SecretService $secretService;

    private int $default_expire_message_in_days = 7;

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

        $id = $request->input("id");
        $message = $request->input("message");

        if(strlen($id) !== 36 || $id === null) {
            return response()->json(['response_code' => 400]);
        }

        // in hours.
        $expires_at = (int) $request->input('expires_at');

        // if none is set, expire in 7 days.
        if($expires_at <= 0) {
            $expires_at = 24 * $this->default_expire_message_in_days;
        }

        $expires_at = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"))->addHours($expires_at);

        $password = $request->input('password');

        if($password !== null && strlen($password) > 0) {
            $password = hash("sha512", $password);
        }

        $data = [
            'id'            => $id,
            'message'       => $message,
            'expires_at'    => $expires_at,
            'password'      => $password
        ];

        $secret = $this->secretService->add($data)->object();

        return response()->json($secret);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {

        $id = $request->input('id');

        if($id === null) {
            return response()->json(['response_code' => 400]);
        }

        $id = hash("sha512", $id);

        $find = $this->secretService->view($id)->object();

        if(!isset($find->id)) {
            return response()->json(['response_code' => 400]);
        }

        $find->response_code = 200;

        $this->secretService->delete($id)->object();

        return response()->json($find);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {

        $id = $request->input('id');

        if($id === null) {
            return response()->json(['response_code' => 400]);
        }

        $delete = $this->secretService->delete(
            hash("sha512", $id)
        )->object();

        return response()->json($delete);
    }

}
