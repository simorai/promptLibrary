<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateShareLinkRequest;
use App\Http\Resources\ConversationShareResource;
use App\Models\Conversation;
use App\Services\ConversationShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationShareController extends Controller
{
    public function __construct(private readonly ConversationShareService $shareService)
    {
    }

    public function generate(GenerateShareLinkRequest $request, Conversation $conversation): ConversationShareResource
    {
        $this->authorize('share', $conversation);

        $share = $this->shareService->generateLink($conversation, $request->validated('visibility'));

        return new ConversationShareResource($share);
    }

    public function revoke(Conversation $conversation): JsonResponse
    {
        $this->authorize('revokeShare', $conversation);

        $this->shareService->revokeActiveLinks($conversation);

        return response()->json(status: 204);
    }

    public function show(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $share = $this->shareService->findActiveByToken($token);

        abort_if(! $share, 404);

        if ($share->visibility === 'restricted' && ! $request->user()) {
            return redirect()->guest(route('login'));
        }

        return response()->json($this->shareService->buildSharedConversationPayload($share->conversation));
    }
}
