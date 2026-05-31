export type Conversation = {
    id: number;
    title: string | null;
    model_used: string | null;
    is_pinned: boolean;
    pinned_at: string | null;
    created_at: string;
    updated_at: string;
    messages_count?: number;
};

export type MessageAttachment = {
    id: number;
    original_filename: string;
    mime_type: string;
    size_bytes: number;
    created_at: string;
};

export type Message = {
    id: number;
    conversation_id: number;
    role: 'user' | 'assistant';
    content: string;
    status?: 'pending' | 'success' | 'failed' | null;
    error_message?: string | null;
    created_at: string;
    updated_at: string;
    attachments?: MessageAttachment[];
    comments?: Comment[];
};

export type OpenRouterModel = {
    id: string;
    name: string;
    description?: string | null;
    context_length?: number | null;
    pricing?: Record<string, string | number | null> | null;
};

export type Prompt = {
    id: number;
    name: string;
    text: string;
    created_at: string;
    updated_at: string;
};

export type Comment = {
    id: number;
    message_id: number;
    user_id: number;
    text: string;
    created_at: string;
    updated_at: string;
};
