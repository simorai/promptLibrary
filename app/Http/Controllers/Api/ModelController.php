<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ModelController extends Controller
{
    public function __construct(private readonly OpenRouterService $openRouterService)
    {
    }

    public function index(): JsonResponse
    {
        try {
            $response = $this->openRouterService->listModels();
            $models = $response['data'] ?? [];
        } catch (RuntimeException $exception) {
            return response()->json([
                'error' => 'Model provider is currently unavailable.',
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json([
            'data' => $this->openRouterService->formatModels($models),
        ]);
    }
}
