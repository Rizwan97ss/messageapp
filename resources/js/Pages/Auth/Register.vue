<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-gradient-to-br from-sky-50 via-indigo-50 to-rose-50 px-4 py-8">
        <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-10 lg:grid-cols-2">
            <div class="hidden lg:block">
                <div class="max-w-xl">
                    <div class="mb-6 inline-flex items-center rounded-full bg-white/80 px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                        Join secure messaging
                    </div>

                    <h1 class="text-5xl font-black tracking-tight text-slate-950">
                        Create your private chat account.
                    </h1>

                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        Start realtime conversations, create groups, share media, and manage secure messaging in one modern workspace.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-indigo-600">Safe</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Protected chats</div>
                        </div>

                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-emerald-600">Fast</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Realtime sync</div>
                        </div>

                        <div class="rounded-3xl bg-white/75 p-5 shadow-xl shadow-indigo-100/60 ring-1 ring-white/80 backdrop-blur">
                            <div class="text-2xl font-black text-rose-600">Media</div>
                            <div class="mt-1 text-sm font-semibold text-slate-600">Share files</div>
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
                            Create account
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Register to start secure conversations.
                        </p>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
                                Full name
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Your name"
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                            />

                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                                Email address
                            </label>

                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
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
                                autocomplete="new-password"
                                placeholder="Create a password"
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                            />

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">
                                Confirm password
                            </label>

                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                            />

                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div
                            v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                            class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100"
                        >
                            <label class="flex items-start gap-3">
                                <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                                <span class="text-sm leading-6 text-slate-600">
                                    I agree to the
                                    <a
                                        target="_blank"
                                        :href="route('terms.show')"
                                        class="font-bold text-indigo-600 hover:text-indigo-800"
                                    >
                                        Terms of Service
                                    </a>
                                    and
                                    <a
                                        target="_blank"
                                        :href="route('policy.show')"
                                        class="font-bold text-indigo-600 hover:text-indigo-800"
                                    >
                                        Privacy Policy
                                    </a>.
                                </span>
                            </label>

                            <InputError class="mt-2" :message="form.errors.terms" />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3.5 text-sm font-black text-white shadow-xl shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span v-if="form.processing">Creating account...</span>
                            <span v-else>Create account</span>
                        </button>

                        <p class="text-center text-sm text-slate-500">
                            Already registered?
                            <Link
                                :href="route('login')"
                                class="font-bold text-indigo-600 hover:text-indigo-800"
                            >
                                Log in
                            </Link>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>