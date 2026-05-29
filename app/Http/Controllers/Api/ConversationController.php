<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportConversationRequest;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Services\ConversationCrudService;
use App\Services\ConversationExportService;
use App\Services\ConversationPinService;
use App\Services\ConversationReadService;
use App\Services\ConversationSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationCrudService $conversationCrudService,
        private readonly ConversationReadService $conversationReadService,
        private readonly ConversationSearchService $conversationSearchService,
        private readonly ConversationPinService $conversationPinService,
        private readonly ConversationExportService $conversationExportService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversationSearchService->search(
            $request->user(),
            $request->string('q')->toString() ?: null,
        );

        return response()->json([
            'data' => ConversationResource::collection($conversations),
        ]);
    }

    public function store(StoreConversationRequest $request): ConversationResource
    {
        $conversation = $this->conversationCrudService->create(
            $request->user(),
            $request->validated('title'),
            $request->validated('model_used'),
        );

        $conversation = $this->conversationReadService->loadWithMessageCount($conversation);

        return new ConversationResource($conversation);
    }

    public function update(UpdateConversationRequest $request, Conversation $conversation): ConversationResource
    {
        $this->authorize('update', $conversation);

        $conversation = $this->conversationCrudService->update(
            $conversation,
            $request->validated('title'),
        );

        $conversation = $this->conversationReadService->refreshWithMessageCount($conversation);

        return new ConversationResource($conversation);
    }

    public function show(Conversation $conversation): ConversationResource
    {
        $this->authorize('view', $conversation);

        $conversation = $this->conversationReadService->loadForShow($conversation);

        return new ConversationResource($conversation);
    }

    public function destroy(Conversation $conversation): JsonResponse
    {
        $this->authorize('delete', $conversation);

        $this->conversationCrudService->delete($conversation);

        return response()->json(status: 204);
    }

    public function pin(Conversation $conversation): ConversationResource
    {
        $this->authorize('update', $conversation);

        $this->conversationPinService->togglePin($conversation);

        $conversation = $this->conversationReadService->refreshWithMessageCount($conversation);

        return new ConversationResource($conversation);
    }

    public function export(ExportConversationRequest $request, Conversation $conversation): BinaryFileResponse
    {
        $this->authorize('export', $conversation);

        return $this->conversationExportService->export($conversation, $request->validated('format'));
    }
}
