<script setup>
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import {
    X,
    Users,
    Mail,
    LogOut,
    ShieldCheck,
    UserPlus,
    Crown,
    UserMinus,
    Search,
    Check,
} from 'lucide-vue-next'

const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits([
    'close',
    'leave',
    'add-members',
    'remove-member',
    'update-member-role',
])

const page = usePage()
const currentUser = computed(() => page.props.auth.user)

const showAddMembers = ref(false)
const search = ref('')
const users = ref([])
const selectedUsers = ref([])
let searchTimer = null

const members = computed(() => {
    return props.conversation.active_users || props.conversation.activeUsers || []
})

const currentMember = computed(() => {
    return members.value.find(user => user.id === currentUser.value.id)
})

const currentRole = computed(() => {
    return currentMember.value?.pivot?.role
})

const isGroup = computed(() => props.conversation.type === 'group')

const canManage = computed(() => {
    return isGroup.value && ['owner', 'admin'].includes(currentRole.value)
})

const isOwner = computed(() => currentRole.value === 'owner')

const title = computed(() => {
    if (props.conversation.type === 'group') {
        return props.conversation.name || 'Group Chat'
    }

    return members.value.map(user => user.name).join(', ') || 'Direct Chat'
})

const filteredUsers = computed(() => {
    const memberIds = members.value.map(user => user.id)

    return users.value.filter(user => !memberIds.includes(user.id))
})

watch(search, () => {
    clearTimeout(searchTimer)

    if (!search.value.trim()) {
        users.value = []
        return
    }

    searchTimer = setTimeout(loadUsers, 300)
})

async function loadUsers() {
    const { data } = await axios.get(route('chat.users'), {
        params: { search: search.value },
    })

    users.value = data
}

function toggleUser(user) {
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

function addSelectedMembers() {
    if (!selectedUsers.value.length) return

    emit('add-members', selectedUsers.value.map(user => user.id))

    selectedUsers.value = []
    search.value = ''
    users.value = []
    showAddMembers.value = false
}

function canRemove(user) {
    const role = user.pivot?.role

    if (!canManage.value) return false
    if (user.id === currentUser.value.id) return false
    if (role === 'owner') return false
    if (currentRole.value === 'admin' && role === 'admin') return false

    return true
}

function canPromote(user) {
    if (!canManage.value) return false
    if (user.pivot?.role !== 'member') return false

    return true
}

function canDemote(user) {
    if (!canManage.value) return false
    if (user.pivot?.role !== 'admin') return false

    // Only owner can remove admin role.
    return isOwner.value
}

function removeMember(user) {
    if (confirm(`Remove ${user.name} from this group?`)) {
        emit('remove-member', user.id)
    }
}

function promote(user) {
    emit('update-member-role', user.id, 'admin')
}

function demote(user) {
    if (confirm(`Remove admin role from ${user.name}?`)) {
        emit('update-member-role', user.id, 'member')
    }
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex justify-end bg-slate-950/40 backdrop-blur-sm">
        <aside class="h-full w-full max-w-md overflow-y-auto bg-white shadow-2xl">
            <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/90 p-5 backdrop-blur">
                <div>
                    <h2 class="text-xl font-black text-slate-900">
                        {{ isGroup ? 'Group Details' : 'Chat Details' }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        {{ members.length }} member{{ members.length === 1 ? '' : 's' }}
                    </p>
                </div>

                <button
                    type="button"
                    @click="$emit('close')"
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200"
                >
                    <X class="h-5 w-5" />
                </button>
            </header>

            <div class="p-5">
                <div class="rounded-[2rem] bg-gradient-to-br from-indigo-50 via-sky-50 to-rose-50 p-6 text-center">
                    <div class="mx-auto grid h-24 w-24 place-items-center rounded-[2rem] bg-gradient-to-br from-indigo-200 to-rose-200 text-3xl font-black text-indigo-800">
                        {{ title.slice(0, 2).toUpperCase() }}
                    </div>

                    <h3 class="mt-4 text-2xl font-black text-slate-900">
                        {{ title }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ isGroup ? 'Group conversation' : 'Private conversation' }}
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <Users class="mb-2 h-5 w-5 text-indigo-600" />
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            Members
                        </p>
                        <p class="text-xl font-black text-slate-900">
                            {{ members.length }}
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-4">
                        <ShieldCheck class="mb-2 h-5 w-5 text-emerald-600" />
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                            Your Role
                        </p>
                        <p class="text-xl font-black capitalize text-slate-900">
                            {{ currentRole || 'member' }}
                        </p>
                    </div>
                </div>

                <button
                    v-if="canManage"
                    type="button"
                    @click="showAddMembers = !showAddMembers"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700"
                >
                    <UserPlus class="h-5 w-5" />
                    Add Members
                </button>

                <div
                    v-if="showAddMembers"
                    class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-4"
                >
                    <div class="mb-3 flex items-center gap-2 rounded-2xl bg-white px-4 py-3">
                        <Search class="h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search registered users..."
                            class="w-full border-0 bg-transparent p-0 text-sm outline-none ring-0 focus:ring-0"
                        />
                    </div>

                    <div v-if="selectedUsers.length" class="mb-3 flex flex-wrap gap-2">
                        <span
                            v-for="user in selectedUsers"
                            :key="user.id"
                            class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-black text-indigo-700"
                        >
                            {{ user.name }}
                        </span>
                    </div>

                    <div class="max-h-52 space-y-2 overflow-y-auto">
                        <button
                            v-for="user in filteredUsers"
                            :key="user.id"
                            type="button"
                            @click="toggleUser(user)"
                            class="flex w-full items-center gap-3 rounded-2xl bg-white p-3 text-left hover:bg-indigo-50"
                        >
                            <img
                                :src="user.profile_photo_url"
                                :alt="user.name"
                                class="h-10 w-10 rounded-2xl object-cover"
                            />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black text-slate-800">
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ user.email }}
                                </p>
                            </div>

                            <div
                                class="grid h-7 w-7 place-items-center rounded-full"
                                :class="isSelected(user) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-transparent'"
                            >
                                <Check class="h-4 w-4" />
                            </div>
                        </button>
                    </div>

                    <button
                        v-if="selectedUsers.length"
                        type="button"
                        @click="addSelectedMembers"
                        class="mt-3 w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-black text-white hover:bg-indigo-700"
                    >
                        Add Selected
                    </button>
                </div>

                <div class="mt-6">
                    <h4 class="mb-3 text-sm font-black uppercase tracking-wide text-slate-400">
                        Members
                    </h4>

                    <div class="space-y-2">
                        <div
                            v-for="user in members"
                            :key="user.id"
                            class="rounded-2xl bg-slate-50 p-3"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    :src="user.profile_photo_url"
                                    :alt="user.name"
                                    class="h-11 w-11 rounded-2xl object-cover"
                                />

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-black text-slate-800">
                                        {{ user.name }}
                                    </p>

                                    <p class="flex items-center gap-1 truncate text-sm text-slate-500">
                                        <Mail class="h-3.5 w-3.5" />
                                        {{ user.email }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black capitalize"
                                    :class="user.pivot?.role === 'owner'
                                        ? 'bg-amber-100 text-amber-700'
                                        : user.pivot?.role === 'admin'
                                            ? 'bg-indigo-100 text-indigo-700'
                                            : 'bg-slate-200 text-slate-600'"
                                >
                                    {{ user.pivot?.role || 'member' }}
                                </span>
                            </div>

                            <div
                                v-if="canRemove(user) || canPromote(user) || canDemote(user)"
                                class="mt-3 flex flex-wrap gap-2 pl-14"
                            >
                                <button
                                    v-if="canPromote(user)"
                                    type="button"
                                    @click="promote(user)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-indigo-50 px-3 py-2 text-xs font-black text-indigo-600 hover:bg-indigo-100"
                                >
                                    <Crown class="h-3.5 w-3.5" />
                                    Make Admin
                                </button>

                                <button
                                    v-if="canDemote(user)"
                                    type="button"
                                    @click="demote(user)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-amber-50 px-3 py-2 text-xs font-black text-amber-600 hover:bg-amber-100"
                                >
                                    <Crown class="h-3.5 w-3.5" />
                                    Remove Admin
                                </button>

                                <button
                                    v-if="canRemove(user)"
                                    type="button"
                                    @click="removeMember(user)"
                                    class="inline-flex items-center gap-1 rounded-xl bg-rose-50 px-3 py-2 text-xs font-black text-rose-600 hover:bg-rose-100"
                                >
                                    <UserMinus class="h-3.5 w-3.5" />
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    v-if="isGroup"
                    type="button"
                    @click="$emit('leave')"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-50 px-5 py-3 text-sm font-black text-rose-600 hover:bg-rose-100"
                >
                    <LogOut class="h-5 w-5" />
                    Leave Group
                </button>
            </div>
        </aside>
    </div>
</template>