<?php

namespace App\Http\Controllers\V2;

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

        if($id === null) {
            return response()->json(['response_code' => 400]);
        }

        if(empty($message)) {
            return response()->json(['response_code' => 400]);
        }

        // in hours.
        $expires_at = (int) $request->input('expires_at');

        // if none is set, expire in 7 days.
        if($expires_at <= 0) {
            $expires_at = 24 * $this->default_expire_message_in_days;
        }

        $expires_at = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s"))->addHours($expires_at);

        // the difference between V1 and V2 is that the client-side (app) has the hashed password.
        // in V1 we hashed it on the API-side, now the UI does it. (if the user has set the pw).
        $password = $request->input('password');

        // file upload, the UI will send ID + Content.
        $files = $request->file('files');

        // not an array of files, something is odd.
        if($files !== null && !is_array($files)) {
            return response()->json(['response_code' => 518]);
        }

        if($files !== null) {

            if(!is_array($files)) {
                return response()->json(['response_code' => 518]);
            }

            foreach($files as $file) {
                if(!isset($file['id'])) {
                    return response()->json(['response_code' => 519]);
                }

                if(!isset($file['content'])) {
                    return response()->json(['response_code' => 520]);
                }
            }

        }

        $data = [
            'id'            => $id,
            'message'       => $message,
            'expires_at'    => $expires_at,
            'password'      => $password,
            'files'         => $files
        ];

        $secret = $this->secretService->add($data)->object();

        return response()->json($secret);
    }


}
