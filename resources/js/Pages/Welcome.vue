<script setup>
import { Head, Link } from '@inertiajs/vue3'
import {
    MessageCircle,
    ShieldCheck,
    Users,
    Image,
    Radio,
    Lock,
    ArrowRight,
    CheckCircle2,
    Sparkles,
    Zap,
} from 'lucide-vue-next'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
})

const features = [
    'Realtime private chat',
    'Group conversations',
    'Typing indicators',
    'Media and file sharing',
    'Read receipts',
    'Responsive mobile UI',
    'Private channels',
    'End-to-end encryption ready',
]
</script>

<template>
    <Head title="Welcome" />

    <main class="min-h-screen overflow-hidden bg-gradient-to-br from-sky-50 via-indigo-50 to-rose-50 text-slate-900">
        <div class="absolute left-0 top-0 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-rose-300/30 blur-3xl"></div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6">
            <header class="flex items-center justify-between py-6">
                <div class="flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                        <MessageCircle class="h-6 w-6" />
                    </div>

                    <div>
                        <h1 class="text-lg font-black">SecureChat</h1>
                        <p class="text-xs font-semibold text-slate-500">Laravel + Vue Realtime</p>
                    </div>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-2xl bg-white/80 px-5 py-2.5 text-sm font-black text-slate-700 shadow-sm ring-1 ring-white/70 backdrop-blur transition hover:bg-white"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-2xl px-5 py-2.5 text-sm font-black text-slate-700 transition hover:bg-white/70"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </header>

            <section class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[1.05fr_0.95fr]">
                <div>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm ring-1 ring-white/80 backdrop-blur">
                        <Sparkles class="h-4 w-4" />
                        Modern realtime messaging platform
                    </div>

                    <h2 class="max-w-3xl text-5xl font-black tracking-tight text-slate-950 sm:text-6xl lg:text-7xl">
                        Chat with users,
                        <span class="bg-gradient-to-r from-indigo-600 via-sky-500 to-rose-500 bg-clip-text text-transparent">
                            securely and instantly.
                        </span>
                    </h2>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                        A responsive Laravel Jetstream + Inertia Vue chat app with realtime messaging, group chats, media sharing, read receipts, typing indicators, and encryption-ready architecture.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('chat')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-7 py-4 text-sm font-black text-white shadow-xl shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700"
                        >
                            Open Chat
                            <ArrowRight class="h-5 w-5" />
                        </Link>

                        <Link
                            v-else
                            :href="route('register')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-7 py-4 text-sm font-black text-white shadow-xl shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700"
                        >
                            Start Messaging
                            <ArrowRight class="h-5 w-5" />
                        </Link>

                        <Link
                            :href="$page.props.auth.user ? route('dashboard') : route('login')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-7 py-4 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-slate-50"
                        >
                            {{ $page.props.auth.user ? 'Dashboard' : 'Log in' }}
                        </Link>
                    </div>

                    <div class="mt-8 grid max-w-xl grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl bg-white/70 p-4 text-center shadow-sm ring-1 ring-white/80 backdrop-blur">
                            <Radio class="mx-auto mb-2 h-5 w-5 text-emerald-500" />
                            <p class="text-xs font-black text-slate-600">Realtime</p>
                        </div>

                        <div class="rounded-2xl bg-white/70 p-4 text-center shadow-sm ring-1 ring-white/80 backdrop-blur">
                            <Users class="mx-auto mb-2 h-5 w-5 text-indigo-500" />
                            <p class="text-xs font-black text-slate-600">Groups</p>
                        </div>

                        <div class="rounded-2xl bg-white/70 p-4 text-center shadow-sm ring-1 ring-white/80 backdrop-blur">
                            <Image class="mx-auto mb-2 h-5 w-5 text-sky-500" />
                            <p class="text-xs font-black text-slate-600">Media</p>
                        </div>

                        <div class="rounded-2xl bg-white/70 p-4 text-center shadow-sm ring-1 ring-white/80 backdrop-blur">
                            <Lock class="mx-auto mb-2 h-5 w-5 text-rose-500" />
                            <p class="text-xs font-black text-slate-600">Secure</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-indigo-300/40 blur-3xl"></div>
                    <div class="absolute -bottom-8 -left-8 h-40 w-40 rounded-full bg-rose-300/40 blur-3xl"></div>

                    <div class="relative rounded-[2rem] border border-white/70 bg-white/80 p-5 shadow-2xl backdrop-blur">
                        <div class="mb-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-indigo-200 to-rose-200 font-black text-indigo-800">
                                    SC
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-900">Project Group</h3>
                                    <p class="text-sm font-semibold text-emerald-500">3 members online</p>
                                </div>
                            </div>

                            <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-600">
                                Live
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="max-w-[82%] rounded-3xl rounded-bl-md bg-slate-100 px-4 py-3 text-sm text-slate-700">
                                Hey, realtime messages are working now.
                            </div>

                            <div class="ml-auto max-w-[82%] rounded-3xl rounded-br-md bg-indigo-600 px-4 py-3 text-sm text-white shadow-lg shadow-indigo-200">
                                Nice. Let’s create the group details panel too.
                            </div>

                            <div class="max-w-[82%] rounded-3xl rounded-bl-md bg-slate-100 px-4 py-3 text-sm text-slate-700">
                                Uploaded media preview looks clean.
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-slate-500 shadow-sm">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                                Sarah is typing...
                            </div>
                        </div>

                        <div class="mt-5 rounded-3xl bg-slate-100 p-3">
                            <div class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm text-slate-400">
                                Type a secure message...
                                <Zap class="ml-auto h-4 w-4 text-indigo-500" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 pb-12 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in features"
                    :key="feature"
                    class="flex items-center gap-3 rounded-3xl border border-white/70 bg-white/70 p-4 shadow-sm backdrop-blur"
                >
                    <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald-500" />
                    <span class="text-sm font-bold text-slate-700">{{ feature }}</span>
                </div>
            </section>

            <footer class="border-t border-white/70 py-6 text-center text-sm font-semibold text-slate-500">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </footer>
        </div>
    </main>
</template>