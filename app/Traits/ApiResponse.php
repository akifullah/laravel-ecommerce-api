<?php

namespace App\Traits;


trait ApiResponse
{
    protected function successResponse($responseData = [], $statusCode = 200)
    {
        return response()->json([
            "success" => true,
            "message" => $responseData["message"] ?? "Success.",
            "data" => $responseData["data"] ?? [],
        ], $statusCode);
    }

    protected function errorResponse($responseData, $statusCode = 500)
    {

        $response = [
            "success" => false,
            "message" => $responseData["message"] ?? "Something goes wrong.",
            "error" => $responseData["error"] ?? ""
        ];
        return response()->json($response, $statusCode);
    }
}
