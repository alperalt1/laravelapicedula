<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use PhpParser\Node\Stmt\Return_;

trait ApiResponses
{
  protected function successResponse($data, $message = null, $code = 200): JsonResponse
  {
    return response()->json([
      'status' => 'success',
      'message'=> $message,
      'data'=> $data,
      'errors'=> null
    ], $code);
  }

  protected function errorResponse($message, $code, $errors = null):JsonResponse
  {
    return response()->json([
      'status' => 'error',
      'message' => $message,
      'data' => null,
      'errors' => $errors
    ], $code);
  }
}