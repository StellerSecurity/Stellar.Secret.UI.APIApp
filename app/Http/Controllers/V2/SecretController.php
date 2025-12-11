<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $id = $request->input('id');
        $message = $request->input('message');

        // id validation
        if ($id === null) {
            return response()->json(['response_code' => 400], 400);
        }

        if (!is_string($id) || strlen($id) < 16) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'id too short',
            ], 400);
        }

        // message must be non-empty (ciphertext from client)
        if (empty($message) || !is_string($message)) {
            return response()->json(['response_code' => 400], 400);
        }

        // in hours.
        $hours = (int) $request->input('expires_at');

        if ($hours <= 0) {
            $hours = 24 * $this->default_expire_message_in_days;
        }

        $expires_at = now()->addHours($hours);

        // in V2 the UI sends hashed password (or null)
        $password = $request->input('password');

        if ($password !== null && !is_string($password)) {
            return response()->json(['response_code' => 400], 400);
        }

        // file upload, the UI will send ID + Content.
        $files = $request->input('files');

        if ($files !== null) {
            if (!is_array($files)) {
                return response()->json(['response_code' => 518], 400);
            }

            foreach ($files as $file) {
                if (!isset($file['id']) || !is_string($file['id'])) {
                    return response()->json(['response_code' => 519], 400);
                }

                if (!isset($file['content']) || !is_string($file['content'])) {
                    return response()->json(['response_code' => 520], 400);
                }
            }
        }

        $data = [
            'id'         => $id,
            'message'    => $message,
            'expires_at' => $expires_at,
            'password'   => $password,
            'files'      => $files,
        ];

        $secret = $this->secretService->add($data);

        if ($secret->failed()) {
            return response()->json(['response_code' => 500], 500);
        }

        return response()->json($secret->object());
    }
}
