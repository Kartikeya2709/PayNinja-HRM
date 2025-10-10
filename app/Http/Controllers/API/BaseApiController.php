<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    /**
     * Success response method.
     *
     * @param  mixed  $result
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     *
     * @param  string  $error
     * @param  array  $errorMessages
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}