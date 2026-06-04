<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { LogOut, MoreVertical } from 'lucide-vue-next'
import MessageBubble from './MessageBubble.vue'
import Composer from './Composer.vue'
import ChatDetailsDrawer from './Partials/ChatDetailsDrawer.vue'
const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
    messages: {
        type: Array,
        default: () => [],
    },
    typingUsers: {
        type: Array,
        default: () => [],
    },
    draft: {
        type: Object,
        default: () => ({
            body: '',
            files: [],
        }),
    },
})

const emit = defineEmits([
    'send',
    'typing',
    'draft-change',
    'back',
    'leave',
    'update-message',
    'delete-message',
    'add-members',
    'remove-member',
    'update-member-role',
]);
function handleSend(formData) {
    emit('send', formData)

    emit('draft-change', {
        body: '',
        files: [],
    })
}

const showDetails = ref(false)
const scrollBox = ref(null)

const title = computed(() => {
    if (props.conversation.type === 'group') {
        return props.conversation.name || 'Group Chat'
    }

    const users = props.conversation.active_users || props.conversation.activeUsers || []

    return users.map(user => user.name).join(', ') || 'Direct Chat'
})

const memberCount = computed(() => {
    const users = props.conversation.active_users || props.conversation.activeUsers || []
    return users.length
})

watch(
    () => props.messages.length,
    async () => {
        await nextTick()
        if (scrollBox.value) {
            scrollBox.value.scrollTop = scrollBox.value.scrollHeight
        }
    }
)
</script>

<template>
    <section
        class="hidden flex-1 flex-col overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl backdrop-blur md:flex">
        <header class="flex items-center justify-between border-b border-white/70 bg-white/60 p-5">
            <div class="flex items-center gap-3">
                <div
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-indigo-200 to-rose-200 text-lg font-black text-indigo-800">
                    {{ title.slice(0, 2).toUpperCase() }}
                </div>

                <div>
                    <h2 class="font-black text-slate-900">
                        {{ title }}
                    </h2>
                    <p class="text-sm">
    <span v-if="typingUsers.length" class="font-semibold text-emerald-600">
        {{ typingUsers[0].name }} is typing...
    </span>

    <span v-else class="text-slate-500">
        {{ memberCount }} member{{ memberCount === 1 ? '' : 's' }}
    </span>
</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button v-if="conversation.type === 'group'" type="button" @click="$emit('leave')"
                    class="inline-flex items-center gap-2 rounded-2xl bg-rose-50 px-4 py-2 text-sm font-bold text-rose-600 transition hover:bg-rose-100">
                    <LogOut class="h-4 w-4" />
                    Leave
                </button>

                <button type="button" @click="showDetails = true"
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                    <MoreVertical class="h-5 w-5" />
                </button>
            </div>
        </header>

        <div ref="scrollBox"
            class="flex-1 space-y-3 overflow-y-auto bg-gradient-to-b from-white/50 to-indigo-50/40 p-5">
            <MessageBubble v-for="message in messages" :key="message.id" :message="message"
                @update="$emit('update-message', message, $event)" @delete="$emit('delete-message', message, $event)" />
        </div>

        <Composer :draft="draft" @send="handleSend" @typing="$emit('typing', $event)"
            @draft-change="$emit('draft-change', $event)" />
        <ChatDetailsDrawer v-if="showDetails" :conversation="conversation" @close="showDetails = false"
            @leave="$emit('leave')" @add-members="$emit('add-members', $event)"
            @remove-member="$emit('remove-member', $event)"
            @update-member-role="(userId, role) => $emit('update-member-role', userId, role)" />
    </section>
</template>