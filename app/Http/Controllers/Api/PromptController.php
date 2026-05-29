<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromptRequest;
use App\Http\Requests\UpdatePromptRequest;
use App\Http\Resources\PromptResource;
use App\Models\Prompt;
use App\Services\PromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function __construct(private readonly PromptService $promptService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $prompts = $this->promptService->search(
            $request->user(),
            $request->string('q')->toString() ?: null,
        );

        return response()->json([
            'data' => PromptResource::collection($prompts),
        ]);
    }

    public function store(StorePromptRequest $request): PromptResource
    {
        $prompt = $this->promptService->create(
            $request->user(),
            $request->validated('name'),
            $request->validated('text'),
        );

        return new PromptResource($prompt);
    }

    public function update(UpdatePromptRequest $request, Prompt $prompt): PromptResource
    {
        $this->authorize('update', $prompt);

        $updated = $this->promptService->update($prompt, $request->validated());

        return new PromptResource($updated);
    }

    public function destroy(Prompt $prompt): JsonResponse
    {
        $this->authorize('delete', $prompt);

        $this->promptService->delete($prompt);

        return response()->json(status: 204);
    }
}
