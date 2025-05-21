<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\FilesecretService;
use App\Services\SecretService;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecretController extends Controller
{

    private SecretService $secretService;

    private FilesecretService $filesecretService;

    private int $default_expire_message_in_days = 7;

    public function __construct(SecretService $secretService, FilesecretService $filesecretService)
    {
        $this->secretService = $secretService;
        $this->filesecretService = $filesecretService;
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

        $secret = $this->secretService->view($id)->object();

        if(!isset($secret->id)) {
            return response()->json(['response_code' => 400]);
        }

        $filesExternal = null;
        if($secret->fileIds !== null) {
            $filesExternal = $this->filesecretService->find($secret->fileIds)->object();
            // delete all files from storage.
            $this->filesecretService->delete($secret->fileIds);
        }

        unset($secret->fileIds); // not needed for UI...
        $secret->files = $filesExternal;
        $secret->response_code = 200;

        // delete the secret itself.
        $this->secretService->delete($id)->object();

        return response()->json($secret);
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

        $id = hash("sha512", $id);

        $secret = $this->secretService->view($id)->object();

        if($secret === null) {
            return response()->json(['response_code' => 400]);
        }

        if($secret->fileIds !== null) {
            $filesExternal = $this->filesecretService->find($secret->fileIds)->object();
            // delete all files from storage.
            $this->filesecretService->delete($secret->fileIds);
        }

        $delete = $this->secretService->delete($id)->object();

        return response()->json($delete);
    }

}
