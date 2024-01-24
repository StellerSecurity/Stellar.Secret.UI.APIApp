<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecretController extends Controller
{

    public function add(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function view(): JsonResponse
    {
        return response()->json([]);
    }

    public function delete(): JsonResponse
    {
        return response()->json([]);
    }

}
