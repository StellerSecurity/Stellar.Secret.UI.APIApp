<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretController extends Controller
{
    private SecretService $secretService;

    // Default secret lifetime in days if the client doesn't send a valid value
    private int $default_expire_message_in_days = 7;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;
    }

    /**
     * Handle creating a new secret from the UI.
     *
     * The client:
     * - Generates the raw UUID (secret_id)
     * - Hashes it and sends it as "id"
     * - Encrypts the message client-side and sends ciphertext as "message"
     * - Optionally sends files (id + content)
     * - Optionally flags has_password = true/false
     */
    public function add(Request $request): JsonResponse
    {
        $id      = $request->input('id');
        $message = $request->input('message');

        // Basic ID validation
        if ($id === null) {
            return response()->json(['response_code' => 400], 400);
        }

        if (! is_string($id) || strlen($id) < 16) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'id too short',
            ], 400);
        }

        // Message must be non-empty (ciphertext from client)
        if (empty($message) || ! is_string($message)) {
            return response()->json(['response_code' => 400], 400);
        }

        // Expiration is sent in hours from the UI. If invalid or <= 0, fall back to default.
        $hours = (int) $request->input('expires_at');

        if ($hours <= 0) {
            $hours = 24 * $this->default_expire_message_in_days;
        }

        $expires_at = now()->addHours($hours);

        // UI only tells us whether a password was used. The actual password never leaves the client.
        $hasPassword = (bool) $request->input('has_password', false);

        // File upload: UI sends ID + content.
        $files = $request->input('files');

        if ($files !== null) {
            if (! is_array($files)) {
                return response()->json(['response_code' => 518], 400);
            }

            foreach ($files as $file) {
                if (! isset($file['id']) || ! is_string($file['id'])) {
                    return response()->json(['response_code' => 519], 400);
                }

                if (! isset($file['content']) || ! is_string($file['content'])) {
                    return response()->json(['response_code' => 520], 400);
                }
            }
        }

        $data = [
            'id'           => $id,
            'message'      => $message,
            'expires_at'   => $expires_at,
            'has_password' => $hasPassword,
            'files'        => $files,
        ];

        $secret = $this->secretService->add($data);

        if ($secret->failed()) {
            return response()->json(['response_code' => 500], 500);
        }

        return response()->json($secret->object());
    }
}
