<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { Search, X, Users, MessageCircle, Check } from 'lucide-vue-next'

const emit = defineEmits(['close', 'created'])

const mode = ref('direct')
const search = ref('')
const users = ref([])
const selectedUsers = ref([])
const groupName = ref('')
const loading = ref(false)
const error = ref('')

let searchTimer = null

watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(loadUsers, 300)
})

async function loadUsers() {
    const { data } = await axios.get(route('chat.users'), {
        params: { search: search.value },
    })

    users.value = data
}

function toggleUser(user) {
    if (mode.value === 'direct') {
        selectedUsers.value = [user]
        return
    }

    const exists = selectedUsers.value.some(item => item.id === user.id)

    if (exists) {
        selectedUsers.value = selectedUsers.value.filter(item => item.id !== user.id)
    } else {
        selectedUsers.value.push(user)
    }
}

function isSelected(user) {
    return selectedUsers.value.some(item => item.id === user.id)
}

async function submit() {
    error.value = ''

    if (!selectedUsers.value.length) {
        error.value = 'Please select at least one user.'
        return
    }

    if (mode.value === 'group' && !groupName.value.trim()) {
        error.value = 'Please enter group name.'
        return
    }

    loading.value = true

    try {
        let response

        if (mode.value === 'direct') {
            response = await axios.post(route('chat.conversations.direct'), {
                user_id: selectedUsers.value[0].id,
            })
        } else {
            response = await axios.post(route('chat.conversations.group'), {
                name: groupName.value,
                user_ids: selectedUsers.value.map(user => user.id),
            })
        }

        emit('created', response.data)
    } catch (e) {
        error.value = e.response?.data?.message || 'Something went wrong.'
    } finally {
        loading.value = false
    }
}

const title = computed(() => mode.value === 'direct' ? 'Start New Chat' : 'Create Group')
</script>

<template>
    <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 p-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-900">
                        {{ title }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        Select registered users from your app.
                    </p>
                </div>

                <button
                    type="button"
                    @click="$emit('close')"
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="p-6">
                <div class="mb-5 grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1">
                    <button
                        type="button"
                        @click="mode = 'direct'; selectedUsers = []"
                        class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-black transition"
                        :class="mode === 'direct' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'"
                    >
                        <MessageCircle class="h-4 w-4" />
                        Direct Chat
                    </button>

                    <button
                        type="button"
                        @click="mode = 'group'; selectedUsers = []"
                        class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-black transition"
                        :class="mode === 'group' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'"
                    >
                        <Users class="h-4 w-4" />
                        Group
                    </button>
                </div>

                <div v-if="mode === 'group'" class="mb-4">
                    <label class="mb-1 block text-sm font-bold text-slate-700">
                        Group name
                    </label>

                    <input
                        v-model="groupName"
                        type="text"
                        placeholder="Team discussion"
                        class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:ring-indigo-200"
                    />
                </div>

                <div class="mb-4 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <Search class="h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search registered users by name or email..."
                        class="w-full border-0 p-0 text-sm outline-none ring-0 focus:ring-0"
                    />
                </div>

                <div v-if="selectedUsers.length" class="mb-4 flex flex-wrap gap-2">
                    <span
                        v-for="user in selectedUsers"
                        :key="user.id"
                        class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-sm font-bold text-indigo-700"
                    >
                        {{ user.name }}
                        <button type="button" @click="toggleUser(user)">
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </span>
                </div>

                <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                    <button
                        v-for="user in users"
                        :key="user.id"
                        type="button"
                        @click="toggleUser(user)"
                        class="flex w-full items-center gap-3 rounded-2xl p-3 text-left transition hover:bg-slate-50"
                    >
                        <img
                            :src="user.profile_photo_url"
                            :alt="user.name"
                            class="h-11 w-11 rounded-2xl object-cover"
                        />

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-black text-slate-800">
                                {{ user.name }}
                            </p>
                            <p class="truncate text-sm text-slate-500">
                                {{ user.email }}
                            </p>
                        </div>

                        <div
                            class="grid h-7 w-7 place-items-center rounded-full border"
                            :class="isSelected(user)
                                ? 'border-indigo-600 bg-indigo-600 text-white'
                                : 'border-slate-200 text-transparent'"
                        >
                            <Check class="h-4 w-4" />
                        </div>
                    </button>

                    <div
                        v-if="!users.length"
                        class="rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500"
                    >
                        Search for registered users.
                    </div>
                </div>

                <p
                    v-if="error"
                    class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600"
                >
                    {{ error }}
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="rounded-2xl bg-slate-100 px-5 py-3 text-sm font-black text-slate-600 hover:bg-slate-200"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="loading"
                        @click="submit"
                        class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {{ loading ? 'Please wait...' : mode === 'direct' ? 'Start Chat' : 'Create Group' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>