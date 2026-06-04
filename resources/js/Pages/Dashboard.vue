<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    MessageCircle,
    Users,
    ShieldCheck,
    Image,
    Radio,
    ArrowRight,
    Plus,
    Sparkles,
    Lock,
    CheckCircle2,
} from 'lucide-vue-next'

const page = usePage()
const user = page.props.auth.user

const stats = [
    {
        label: 'Realtime Chat',
        value: 'Live',
        icon: Radio,
        tone: 'from-emerald-400 to-teal-500',
    },
    {
        label: 'Media Sharing',
        value: 'Ready',
        icon: Image,
        tone: 'from-sky-400 to-indigo-500',
    },
    {
        label: 'Groups',
        value: 'Enabled',
        icon: Users,
        tone: 'from-violet-400 to-fuchsia-500',
    },
    {
        label: 'Secure Layer',
        value: 'Planned',
        icon: ShieldCheck,
        tone: 'from-rose-400 to-orange-500',
    },
]

const features = [
    'Private one-to-one conversations',
    'Group chats with leave option',
    'Typing indicators',
    'Realtime message delivery',
    'Media and file attachments',
    'Read receipts',
    'Responsive mobile layout',
    'End-to-end encryption ready',
]
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-br from-sky-50 via-indigo-50 to-rose-50 px-4 py-8 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl space-y-8">
                <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-xl shadow-indigo-100/50 backdrop-blur dark:border-white/10 dark:bg-slate-900/70 dark:shadow-none">
                    <div class="grid gap-8 p-6 md:grid-cols-[1.2fr_0.8fr] md:p-10">
                        <div class="flex flex-col justify-center">
                            <div class="mb-5 inline-flex w-fit items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700 ring-1 ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/20">
                                <Sparkles class="h-4 w-4" />
                                Modern secure messaging dashboard
                            </div>

                            <h1 class="max-w-3xl text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl">
                                Welcome back,
                                <span class="bg-gradient-to-r from-indigo-600 to-rose-500 bg-clip-text text-transparent">
                                    {{ user.name }}
                                </span>
                            </h1>

                            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
                                Manage conversations, start realtime chats, share media, create groups, and build toward a private end-to-end encrypted messaging experience.
                            </p>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <Link
                                    :href="route('chat')"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700 dark:shadow-none"
                                >
                                    <MessageCircle class="h-5 w-5" />
                                    Open Chat
                                    <ArrowRight class="h-4 w-4" />
                                </Link>

                                <Link
                                    :href="route('profile.show')"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-slate-700"
                                >
                                    <ShieldCheck class="h-5 w-5" />
                                    Security Settings
                                </Link>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-300/40 blur-3xl"></div>
                            <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-rose-300/40 blur-3xl"></div>

                            <div class="relative rounded-[2rem] border border-white/70 bg-white/80 p-5 shadow-2xl backdrop-blur dark:border-white/10 dark:bg-slate-800/80">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                            Chat preview
                                        </p>
                                        <h3 class="text-lg font-black text-slate-900 dark:text-white">
                                            Project Group
                                        </h3>
                                    </div>

                                    <div class="flex -space-x-2">
                                        <div class="grid h-9 w-9 place-items-center rounded-full bg-indigo-200 text-xs font-black text-indigo-800 ring-2 ring-white">
                                            A
                                        </div>
                                        <div class="grid h-9 w-9 place-items-center rounded-full bg-rose-200 text-xs font-black text-rose-800 ring-2 ring-white">
                                            S
                                        </div>
                                        <div class="grid h-9 w-9 place-items-center rounded-full bg-emerald-200 text-xs font-black text-emerald-800 ring-2 ring-white">
                                            M
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="max-w-[80%] rounded-3xl rounded-bl-md bg-slate-100 px-4 py-3 text-sm text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                                        Hey, the realtime chat is ready.
                                    </div>

                                    <div class="ml-auto max-w-[80%] rounded-3xl rounded-br-md bg-indigo-600 px-4 py-3 text-sm text-white">
                                        Great. Let’s add groups and media next.
                                    </div>

                                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-slate-500 shadow-sm dark:bg-slate-700 dark:text-slate-300">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        Someone is typing...
                                    </div>
                                </div>

                                <div class="mt-5 flex items-center gap-2 rounded-2xl bg-slate-100 p-2 dark:bg-slate-700">
                                    <button class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-500 dark:bg-slate-800">
                                        <Plus class="h-5 w-5" />
                                    </button>
                                    <div class="flex-1 rounded-xl bg-white px-4 py-3 text-sm text-slate-400 dark:bg-slate-800">
                                        Type a secure message...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="item in stats"
                        :key="item.label"
                        class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-indigo-100/40 backdrop-blur transition hover:-translate-y-1 dark:border-white/10 dark:bg-slate-900/70 dark:shadow-none"
                    >
                        <div
                            class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br text-white"
                            :class="item.tone"
                        >
                            <component :is="item.icon" class="h-6 w-6" />
                        </div>

                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            {{ item.label }}
                        </p>

                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">
                            {{ item.value }}
                        </h3>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-xl shadow-indigo-100/40 backdrop-blur dark:border-white/10 dark:bg-slate-900/70 dark:shadow-none">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <Lock class="h-6 w-6" />
                            </div>

                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white">
                                    Security roadmap
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Build privacy step by step.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-500/10">
                                <p class="font-black text-emerald-700 dark:text-emerald-300">
                                    Now
                                </p>
                                <p class="mt-1 text-sm text-emerald-700/80 dark:text-emerald-200/80">
                                    Authenticated private channels, Reverb realtime events, and protected media access.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-indigo-50 p-4 dark:bg-indigo-500/10">
                                <p class="font-black text-indigo-700 dark:text-indigo-300">
                                    Next
                                </p>
                                <p class="mt-1 text-sm text-indigo-700/80 dark:text-indigo-200/80">
                                    Client-side encryption, user public keys, encrypted media before upload, and device verification.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/70 bg-white/80 p-6 shadow-xl shadow-indigo-100/40 backdrop-blur dark:border-white/10 dark:bg-slate-900/70 dark:shadow-none">
                        <div class="mb-5">
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">
                                Included chat features
                            </h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Core features planned for your Laravel + Vue chat system.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="feature in features"
                                :key="feature"
                                class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 dark:bg-slate-800"
                            >
                                <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald-500" />
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    {{ feature }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>