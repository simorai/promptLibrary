<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import CommentThread from '@/components/CommentThread.vue';
import ConversationList from '@/components/ConversationList.vue';
import ExportDialog from '@/components/ExportDialog.vue';
import MessageBubble from '@/components/MessageBubble.vue';
import MessageInput from '@/components/MessageInput.vue';
import PromptLibraryPanel from '@/components/PromptLibraryPanel.vue';
import ShareLinkDialog from '@/components/ShareLinkDialog.vue';
import { useComments } from '@/composables/useComments';
import { useConversations } from '@/composables/useConversations';
import { useMessages } from '@/composables/useMessages';
import { useModels } from '@/composables/useModels';
import { usePrompts } from '@/composables/usePrompts';
import type { Message } from '@/types';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    friendlyPrimaryButtonClass,
    popButtonClass,
} from '@/lib/uiStyles';

const search = ref('');
const activeConversationId = ref<number | null>(null);
const draft = ref('');
const showPrompts = ref(false);
const showShare = ref(false);
const showExport = ref(false);
const shareLink = ref<string | null>(null);
const isStreaming = ref(false);
const isAutoCreatingConversation = ref(false);
const streamingAssistantText = ref('');
const pageError = ref<string | null>(null);
const renameDialogOpen = ref(false);
const renameConversationId = ref<number | null>(null);
const renameConversationTitle = ref('');
const renameConversationError = ref<string | null>(null);
const renameConversationSaving = ref(false);

const {
    conversations,
    fetchConversations,
    pinConversation,
    deleteConversation,
    createConversation,
    updateConversation,
    error: conversationsError,
} = useConversations();
const {
    messages,
    fetchMessages,
    streamMessage,
    error: messagesError,
} = useMessages();
const { prompts, fetchPrompts } = usePrompts();
const {
    models,
    selectedModel,
    fetchModels,
    loading: modelsLoading,
    error: modelsError,
} = useModels();
const { comments, addComment, deleteComment, hydrateComments } = useComments();

const activeConversation = computed(
    () =>
        conversations.value.find(
            (item) => item.id === activeConversationId.value,
        ) ?? null,
);

async function selectConversation(id: number): Promise<void> {
    activeConversationId.value = id;
    await fetchMessages(id);
    const allComments = messages.value.flatMap(
        (message) => message.comments ?? [],
    );
    hydrateComments(allComments);
}

async function onSendMessage(payload: {
    content: string;
    file: File | null;
}): Promise<void> {
    pageError.value = null;

    if (isStreaming.value || isAutoCreatingConversation.value) {
        return;
    }

    if (selectedModel.value === '') {
        pageError.value = 'Select a model and conversation before sending.';
        return;
    }

    let conversationId = activeConversationId.value;

    if (!conversationId) {
        isAutoCreatingConversation.value = true;

        const created = await createConversation(
            'New conversation',
            selectedModel.value,
        );

        if (!created) {
            isAutoCreatingConversation.value = false;
            pageError.value =
                conversationsError.value ?? 'Failed to create conversation.';
            return;
        }

        await fetchConversations();
        await selectConversation(created.id);
        conversationId = created.id;
        isAutoCreatingConversation.value = false;
    }

    const optimisticUserMessage: Message = {
        id: -Date.now(),
        conversation_id: conversationId,
        role: 'user',
        content: payload.content,
        status: 'success',
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        attachments: [],
    };

    messages.value.push(optimisticUserMessage);
    isStreaming.value = true;
    streamingAssistantText.value = '';

    const streamed = await streamMessage(
        conversationId,
        payload.content,
        payload.file,
        {
            model: selectedModel.value,
            temperature: 0.7,
            max_tokens: 1024,
            onChunk: (chunk) => {
                streamingAssistantText.value += chunk;
            },
        },
    );

    isStreaming.value = false;
    streamingAssistantText.value = '';

    if (!streamed) {
        pageError.value = messagesError.value ?? 'Message stream failed.';
        await fetchMessages(conversationId);

        return;
    }

    await fetchMessages(conversationId);
}

async function onSearch(): Promise<void> {
    await fetchConversations(search.value);
}

async function onCreateConversation(): Promise<void> {
    pageError.value = null;

    if (!selectedModel.value) {
        pageError.value =
            modelsError.value ??
            'Select a model before creating a conversation.';
        return;
    }

    const created = await createConversation(
        'New conversation',
        selectedModel.value,
    );

    if (created) {
        await fetchConversations();
        await selectConversation(created.id);

        return;
    }

    pageError.value =
        conversationsError.value ?? 'Failed to create conversation.';
}

function openRenameConversation(id: number): void {
    const conversation = conversations.value.find((item) => item.id === id);

    if (!conversation) {
        return;
    }

    renameConversationId.value = id;
    renameConversationTitle.value = conversation.title ?? '';
    renameConversationError.value = null;
    renameDialogOpen.value = true;
}

async function submitRenameConversation(): Promise<void> {
    if (renameConversationId.value === null || renameConversationSaving.value) {
        return;
    }

    renameConversationSaving.value = true;
    renameConversationError.value = null;

    const updated = await updateConversation(
        renameConversationId.value,
        renameConversationTitle.value.trim() || null,
    );

    renameConversationSaving.value = false;

    if (!updated) {
        renameConversationError.value =
            conversationsError.value ?? 'Failed to rename conversation.';

        return;
    }

    renameDialogOpen.value = false;
    renameConversationId.value = null;
}

async function onGenerateShare(
    visibility: 'public' | 'restricted',
): Promise<void> {
    pageError.value = null;

    if (!activeConversationId.value) {
        pageError.value =
            'Select a conversation before generating a share link.';
        return;
    }

    const response = await fetch(
        `/api/conversations/${activeConversationId.value}/share`,
        {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ visibility }),
        },
    );

    if (!response.ok) {
        pageError.value = 'Failed to generate share link.';
        return;
    }

    const json = (await response.json()) as { data: { url: string } };
    shareLink.value = json.data.url;
}

onMounted(async () => {
    await Promise.all([fetchConversations(), fetchPrompts(), fetchModels()]);

    if (modelsError.value) {
        pageError.value = modelsError.value;
    }

    if (conversations.value.length > 0) {
        await selectConversation(conversations.value[0].id);

        if (!selectedModel.value && activeConversation.value?.model_used) {
            selectedModel.value = activeConversation.value.model_used;
        }
    }
});
</script>

<template>
    <Head title="Conversations" />

    <div
        class="grid h-full flex-1 grid-cols-1 gap-4 p-4 lg:grid-cols-[300px_1fr_320px]"
    >
        <section class="space-y-3 p-3" :class="friendlyPanelClass">
            <div class="flex items-center gap-2">
                <select
                    v-model="selectedModel"
                    class="max-w-[220px] shrink-0 rounded-md border border-emerald-300/70 bg-background px-3 py-2 text-xs shadow-sm transition-[transform,box-shadow,border-color,background-color] outline-none hover:shadow-md focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:border-emerald-500/40"
                    :disabled="modelsLoading || isStreaming"
                >
                    <option value="" disabled>
                        {{
                            modelsLoading ? 'Loading models...' : 'Select model'
                        }}
                    </option>
                    <option
                        v-for="model in models"
                        :key="model.id"
                        :value="model.id"
                    >
                        {{ model.name || model.id }}
                    </option>
                </select>

                <Button
                    type="button"
                    size="sm"
                    class="shrink-0"
                    :class="[popButtonClass, friendlyPrimaryButtonClass]"
                    :disabled="!selectedModel"
                    @click="onCreateConversation"
                >
                    New
                </Button>
            </div>
            <input
                v-model="search"
                class="min-w-0 flex-1 rounded border px-2 py-1 text-sm"
                :class="friendlyOutlineButtonClass"
                placeholder="Search conversations"
                @input="onSearch"
            />

            <p
                v-if="modelsError"
                class="rounded border border-destructive/30 bg-destructive/10 px-2 py-1 text-xs text-destructive"
            >
                {{ modelsError }}
            </p>

            <ConversationList
                :conversations="conversations"
                :active-id="activeConversationId"
                class="text-emerald-700 hover:text-emerald-800 dark:border-emerald-500/40 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                @select="selectConversation"
                @rename="openRenameConversation"
                @pin="pinConversation"
                @delete="deleteConversation"
            />
        </section>

        <section class="space-y-3 p-3" :class="friendlyPanelClass">
            <div class="flex items-center justify-between">
                <h1 class="text-sm font-semibold">
                    {{ activeConversation?.title || 'Conversation' }}
                </h1>
                <div class="flex gap-2">
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        :class="[popButtonClass, friendlyOutlineButtonClass]"
                        @click="showShare = !showShare"
                    >
                        Share
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        :class="[popButtonClass, friendlyOutlineButtonClass]"
                        @click="showExport = !showExport"
                    >
                        Export
                    </Button>
                </div>
            </div>

            <div class="max-h-[55vh] space-y-2 overflow-auto">
                <MessageBubble
                    v-for="message in messages"
                    :key="message.id"
                    :message="message"
                />

                <MessageBubble
                    v-if="isStreaming && streamingAssistantText"
                    :message="{
                        id: -1,
                        conversation_id: activeConversationId || 0,
                        role: 'assistant',
                        content: `${streamingAssistantText}▌`,
                        created_at: new Date().toISOString(),
                        updated_at: new Date().toISOString(),
                    }"
                />
            </div>

            <MessageInput
                v-model="draft"
                :disabled="
                    isStreaming || isAutoCreatingConversation || !selectedModel
                "
                @send-message="onSendMessage"
                @open-prompts="showPrompts = !showPrompts"
            />

            <p
                v-if="pageError"
                class="rounded border border-destructive/30 bg-destructive/10 px-2 py-1 text-xs text-destructive"
            >
                {{ pageError }}
            </p>
        </section>

        <section class="space-y-3">
            <PromptLibraryPanel
                v-if="showPrompts"
                :prompts="prompts"
                @select-prompt="
                    (text) => {
                        draft = `${draft}${draft ? '\n' : ''}${text}`;
                        showPrompts = false;
                    }
                "
            />

            <ShareLinkDialog
                :visible="showShare"
                :link="shareLink"
                @generate-link="onGenerateShare"
                @close="showShare = false"
            />

            <ExportDialog
                :visible="showExport"
                :conversation-id="activeConversationId"
                @close="showExport = false"
            />

            <CommentThread
                :comments="comments"
                @add-comment="
                    (value) =>
                        activeConversationId &&
                        messages[0] &&
                        addComment(messages[0].id, value)
                "
                @delete-comment="deleteComment"
            />

            <Dialog v-model:open="renameDialogOpen">
                <DialogContent>
                    <form
                        class="space-y-4"
                        @submit.prevent="submitRenameConversation"
                    >
                        <DialogHeader class="space-y-2">
                            <DialogTitle>Rename conversation</DialogTitle>
                            <DialogDescription>
                                Update the title shown in the conversation list.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="space-y-2">
                            <Label for="conversation-title"
                                >Conversation title</Label
                            >
                            <Input
                                id="conversation-title"
                                v-model="renameConversationTitle"
                                placeholder="Enter a new title"
                                maxlength="100"
                                :disabled="renameConversationSaving"
                            />
                        </div>

                        <p
                            v-if="renameConversationError"
                            class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                        >
                            {{ renameConversationError }}
                        </p>

                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="secondary"
                                :class="popButtonClass"
                                :disabled="renameConversationSaving"
                                @click="renameDialogOpen = false"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :class="[
                                    popButtonClass,
                                    friendlyPrimaryButtonClass,
                                ]"
                                :disabled="renameConversationSaving"
                            >
                                Save changes
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </section>
    </div>
</template>
