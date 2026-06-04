<script setup>
import { computed, ref } from 'vue'
import { Plus, Search } from 'lucide-vue-next'

const props = defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    activeId: {
        type: Number,
        default: null,
    },
})

defineEmits(['select', 'create-group'])

const search = ref('')

const filteredConversations = computed(() => {
    return props.conversations.filter((conversation) => {
        const title = conversationTitle(conversation).toLowerCase()
        return title.includes(search.value.toLowerCase())
    })
})

function conversationTitle(conversation) {
    if (conversation.type === 'group') {
        return conversation.name || 'Group Chat'
    }

    const users = conversation.active_users || conversation.activeUsers || []

    return users
        .map((user) => user.name)
        .join(', ') || 'Direct Chat'
}

function avatarText(conversation) {
    return conversationTitle(conversation)
        .split(' ')
        .map((word) => word[0])
        .join('')
        .slice(0, 2)
        .toUpperCase()
}

function latestText(conversation) {
    if (conversation.draft?.body || conversation.draft?.files?.length) {
        if (conversation.draft.body) {
            return `Draft: ${conversation.draft.body}`
        }

        return `Draft: ${conversation.draft.files.length} attachment(s)`
    }

    const latest = conversation.messages?.[0]

    if (!latest) return 'No messages yet'

    if (latest.deleted_at) return 'This message was deleted'

    if (latest.type !== 'text') return 'Shared media'

    return latest.body || 'Encrypted message'
}
</script>

<template>
    <aside
        class="flex w-full flex-col rounded-3xl border border-white/70 bg-white/80 p-4 shadow-xl backdrop-blur md:w-96">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Chats
                </h1>
                <p class="text-sm text-slate-500">
                    Realtime secure messages
                </p>
            </div>

            <button type="button" @click="$emit('create-group')"
                class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                <Plus class="h-5 w-5" />
            </button>
        </div>

        <div class="mb-4 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-sm">
            <Search class="h-4 w-4 text-slate-400" />
            <input v-model="search" type="text" placeholder="Search conversations..."
                class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 outline-none ring-0 placeholder:text-slate-400 focus:ring-0" />
        </div>

        <div class="min-h-0 flex-1 space-y-2 overflow-y-auto pr-1">
            <button v-for="conversation in filteredConversations" :key="conversation.id" type="button"
                @click="$emit('select', conversation)"
                class="flex w-full items-center gap-3 rounded-2xl p-3 text-left transition" :class="activeId === conversation.id
                    ? 'bg-indigo-100 text-indigo-950 shadow-sm'
                    : 'hover:bg-white'">
                <div
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-indigo-200 via-sky-100 to-rose-200 text-sm font-black text-indigo-800">
                    {{ avatarText(conversation) }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="truncate font-bold">
                        {{ conversationTitle(conversation) }}
                    </div>

                    <div
    class="truncate text-sm"
    :class="conversation.draft?.body || conversation.draft?.files?.length
        ? 'font-bold text-emerald-600'
        : 'text-slate-500'"
>
    {{ latestText(conversation) }}
</div>
                </div>

                <div class="h-2.5 w-2.5 rounded-full bg-emerald-400"></div>
            </button>

            <div v-if="!filteredConversations.length"
                class="rounded-2xl bg-white/70 p-5 text-center text-sm text-slate-500">
                No conversations yet.
            </div>
        </div>
    </aside>
</template>