<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-gradient-to-br from-sky-50 via-indigo-50 to-rose-50 px-4 py-8">
        <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-10 lg:grid-cols-2">
            <div class="hidden lg:block">
                <div class="max-w-xl">
                    <div class="mb-6 inline-flex items-center rounded-full bg-white/80 px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                        Secure realtime messaging
                    </div>

                    <h1 class="text-5xl font-black tracking-tight text-slate-950">
                        Welcome back to your private chat space.
                    </h1>

                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        Continue conversations, manage groups, send media, and keep your messages protected with a clean modern workspace.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-indigo-600">E2E</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Encrypted chat</div>
                        </div>

                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-emerald-600">Live</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Realtime updates</div>
                        </div>

                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-rose-600">Groups</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Team messaging</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-md">
                <div class="rounded-[2rem] border border-white/80 bg-white/85 p-8 shadow-2xl shadow-indigo-200/60 backdrop-blur">
                    <div class="mb-8 text-center">
                        <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-gradient-to-br from-indigo-600 to-sky-500 text-2xl font-black text-white shadow-lg shadow-indigo-200">
                            💬
                        </div>

                        <h2 class="text-3xl font-black tracking-tight text-slate-950">
                            Log in
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Enter your credentials to continue.
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100"
                    >
                        {{ status }}
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                                Email address
                            </label>

                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@example.com"
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                            />

                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-700">
                                Password
                            </label>

                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                            />

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.remember" name="remember" />
                                <span class="ms-2 text-sm font-medium text-slate-600">
                                    Remember me
                                </span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm font-bold text-indigo-600 transition hover:text-indigo-800"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3.5 text-sm font-black text-white shadow-xl shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span v-if="form.processing">Signing in...</span>
                            <span v-else>Log in</span>
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Protected access for your messaging workspace.
                </p>
            </div>
        </div>
    </div>
</template>