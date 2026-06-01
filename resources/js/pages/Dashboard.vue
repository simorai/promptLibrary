<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type DashboardMetric = {
    label: string;
    value: number;
    href: string;
};

type DashboardConversation = {
    id: number;
    title: string;
    model_used: string;
    updated_at: string | null;
    href: string;
};

type DashboardPrompt = {
    id: number;
    name: string;
    updated_at: string | null;
    href: string;
};

const props = defineProps<{
    metrics: DashboardMetric[];
    recentConversations: DashboardConversation[];
    recentPrompts: DashboardPrompt[];
}>();

const dashboardNotes = [
    'Use the sidebar to switch between live conversations and saved prompts.',
    'Pin important threads to keep them visible during the workday.',
    'Open the share menu from a conversation to generate a link.',
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <section class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr]">
            <Card
                class="relative overflow-hidden border-sidebar-border/70 bg-gradient-to-br from-background via-background to-emerald-50/50 dark:to-emerald-950/20"
            >
                <CardHeader class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary">Live workspace</Badge>
                        <Badge>Inertia + Vue + Laravel</Badge>
                    </div>
                    <div class="space-y-2">
                        <CardTitle class="text-3xl"
                            >PromptLibrary Dashboard</CardTitle
                        >
                        <CardDescription class="max-w-2xl text-base">
                            Monitor active conversations, keep prompt assets
                            close, and move quickly between drafting, sharing,
                            and exporting.
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="metric in props.metrics"
                        :key="metric.label"
                        class="rounded-xl border border-sidebar-border/70 bg-background/80 p-4 shadow-sm"
                    >
                        <p class="text-sm text-muted-foreground">
                            {{ metric.label }}
                        </p>
                        <p class="mt-2 text-3xl font-semibold">
                            {{ metric.value }}
                        </p>
                        <Button as-child variant="ghost" class="mt-2 px-0">
                            <Link :href="metric.href">Open</Link>
                        </Button>
                    </div>
                </CardContent>

                <CardFooter class="flex flex-wrap gap-3">
                    <Button as-child>
                        <Link href="/conversations">New conversation</Link>
                    </Button>
                    <Button as-child variant="outline">
                        <Link href="/prompts">Open prompt library</Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost">More actions</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-56">
                            <DropdownMenuLabel
                                >Workspace actions</DropdownMenuLabel
                            >
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/conversations"
                                    >Review pinned threads</Link
                                >
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/conversations"
                                    >Export current conversation</Link
                                >
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/conversations"
                                    >Manage shared links</Link
                                >
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardFooter>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Focus panel</CardTitle>
                    <CardDescription
                        >Quick references for common dashboard
                        actions.</CardDescription
                    >
                </CardHeader>

                <CardContent class="space-y-4">
                    <Alert>
                        <AlertTitle>Tip</AlertTitle>
                        <AlertDescription>
                            Keep the most important conversation pinned so it
                            stays at the top of the sidebar.
                        </AlertDescription>
                    </Alert>

                    <Collapsible>
                        <div
                            class="rounded-xl border border-sidebar-border/70 p-4 shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="font-medium">
                                        What can I do here?
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        A short guide to the most useful
                                        actions.
                                    </p>
                                </div>
                                <CollapsibleTrigger as-child>
                                    <Button variant="secondary" size="sm"
                                        >Toggle</Button
                                    >
                                </CollapsibleTrigger>
                            </div>

                            <CollapsibleContent
                                class="mt-4 space-y-2 text-sm text-muted-foreground"
                            >
                                <p v-for="note in dashboardNotes" :key="note">
                                    {{ note }}
                                </p>
                            </CollapsibleContent>
                        </div>
                    </Collapsible>
                </CardContent>
            </Card>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <Card>
                <CardHeader>
                    <CardDescription>Recent conversations</CardDescription>
                    <CardTitle class="text-xl">Conversation activity</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="conversation in props.recentConversations"
                            :key="conversation.id"
                            class="rounded-lg border border-sidebar-border/70 p-3 shadow-sm"
                        >
                            <p class="font-medium">{{ conversation.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ conversation.model_used }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ conversation.updated_at }}
                            </p>
                            <Button as-child variant="ghost" class="mt-2 px-0">
                                <Link :href="conversation.href">Open</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription>Recent prompts</CardDescription>
                    <CardTitle class="text-xl">Prompt activity</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="prompt in props.recentPrompts"
                            :key="prompt.id"
                            class="rounded-lg border border-sidebar-border/70 p-3 shadow-sm"
                        >
                            <p class="font-medium">{{ prompt.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ prompt.updated_at }}
                            </p>
                            <Button as-child variant="ghost" class="mt-2 px-0">
                                <Link :href="prompt.href">Open prompts</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription>Quick link</CardDescription>
                    <CardTitle class="text-xl">Jump into work</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        Start a new conversation or revisit the prompt library
                        to keep the workflow moving.
                    </p>
                </CardContent>
                <CardFooter class="flex gap-2">
                    <Button as-child class="w-full">
                        <Link href="/conversations">Conversations</Link>
                    </Button>
                    <Button as-child variant="outline" class="w-full">
                        <Link href="/prompts">Prompts</Link>
                    </Button>
                </CardFooter>
            </Card>
        </section>
    </div>
</template>
