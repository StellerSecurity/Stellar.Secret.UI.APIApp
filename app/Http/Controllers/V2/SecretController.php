<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretController extends Controller
{
    private SecretService $secretService;

    // Default secret lifetime in days if the client doesn't send a valid value.
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
        $id = $request->input('id');
        $message = $request->input('message');
        $files = $request->input('files');

        // Basic ID validation.
        if ($id === null) {
            return response()->json([
                'response_code' => 400,
                'response_message' => 'id required',
            ], 400);
        }

        if (! is_string($id) || strlen($id) < 16) {
            return response()->json([
                'response_code' => 400,
                'response_message' => 'id too short',
            ], 400);
        }

        // Files are optional, but if present they must be valid.
        if ($files !== null) {
            if (! is_array($files)) {
                return response()->json([
                    'response_code' => 518,
                    'response_message' => 'files must be an array',
                ], 400);
            }

            foreach ($files as $file) {
                if (! is_array($file)) {
                    return response()->json([
                        'response_code' => 518,
                        'response_message' => 'file item must be an object',
                    ], 400);
                }

                if (! isset($file['id']) || ! is_string($file['id']) || $file['id'] === '') {
                    return response()->json([
                        'response_code' => 519,
                        'response_message' => 'file id required',
                    ], 400);
                }

                if (! isset($file['content']) || ! is_string($file['content']) || $file['content'] === '') {
                    return response()->json([
                        'response_code' => 520,
                        'response_message' => 'file content required',
                    ], 400);
                }
            }
        }

        $hasMessage = is_string($message) && $message !== '';
        $hasFiles = is_array($files) && count($files) > 0;

        // A secret must contain either an encrypted message or at least one encrypted file.
        if (! $hasMessage && ! $hasFiles) {
            return response()->json([
                'response_code' => 400,
                'response_message' => 'message or file required',
            ], 400);
        }

        // Normalize empty message to an empty string for file-only secrets.
        // The base API/database expects message to be non-null.
        if (! $hasMessage) {
            $message = '';
        }

        // Accept encryption_version. Default to v1 if missing.
        $encryptionVersion = strtolower(trim((string) $request->input('encryption_version', 'v1')));

        if (! in_array($encryptionVersion, ['v1', 'v2'], true)) {
            return response()->json([
                'response_code' => 400,
                'response_message' => 'invalid encryption_version',
            ], 400);
        }

        // Expiration is sent in hours from the UI. If invalid or <= 0, fall back to default.
        $hours = (int) $request->input('expires_at');

        if ($hours <= 0) {
            $hours = 24 * $this->default_expire_message_in_days;
        }

        $expires_at = now()->addHours($hours);

        // UI only tells us whether a password was used. The actual password never leaves the client.
        $hasPassword = (bool) $request->input('has_password', false);

        $data = [
            'id' => $id,
            'message' => $message,
            'expires_at' => $expires_at,
            'has_password' => $hasPassword,
            'encryption_version' => $encryptionVersion,
            'files' => $files,
        ];

        $secret = $this->secretService->add($data);

        if ($secret->failed()) {
            return response()->json([
                'response_code' => 500,
                'upstream_status' => method_exists($secret, 'status') ? $secret->status() : null,
                'upstream_body' => method_exists($secret, 'body') ? $secret->body() : null,
            ], 500);
        }

        return response()->json($secret->object());
    }
}
