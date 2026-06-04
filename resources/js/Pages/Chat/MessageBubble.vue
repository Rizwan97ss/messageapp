<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { CheckCheck, FileText, MoreVertical, Pencil, Trash2, X, Check } from 'lucide-vue-next'

const props = defineProps({
    message: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['update', 'delete'])

const page = usePage()
const currentUser = computed(() => page.props.auth.user)

const menuOpen = ref(false)
const editing = ref(false)
const editBody = ref(props.message.body || '')

const isMine = computed(() => props.message.sender_id === currentUser.value.id)
const isDeleted = computed(() => Boolean(props.message.deleted_at))

const time = computed(() => {
    return new Date(props.message.created_at).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    })
})

const isRead = computed(() => {
    return props.message.reads?.some(read => read.user_id !== currentUser.value.id)
})

function fileUrl(attachment) {
    return `/storage/${attachment.path}`
}

function isImage(attachment) {
    return attachment.mime_type?.startsWith('image/')
}

function startEdit() {
    editBody.value = props.message.body || ''
    editing.value = true
    menuOpen.value = false
}

function cancelEdit() {
    editing.value = false
    editBody.value = props.message.body || ''
}

function saveEdit() {
    if (!editBody.value.trim()) return

    emit('update', editBody.value)
    editing.value = false
}

function deleteMessage() {
    menuOpen.value = false

    if (confirm('Delete this message?')) {
        emit('delete')
    }
}
function deleteForMe() {
    menuOpen.value = false
    emit('delete', 'me')
}

function deleteForEveryone() {
    menuOpen.value = false

    if (confirm('Delete this message for everyone?')) {
        emit('delete', 'everyone')
    }
}

const canDeleteForEveryone = computed(() => {
    if (!isMine.value || isDeleted.value) return false

    const createdAt = new Date(props.message.created_at)
    const limitMs = 2 * 24 * 60 * 60 * 1000

    return Date.now() - createdAt.getTime() <= limitMs
})
</script>

<template>
    <div class="group flex" :class="isMine ? 'justify-end' : 'justify-start'">
        <div class="relative max-w-[78%]">
            <div class="rounded-3xl px-4 py-3 shadow-sm" :class="isMine
                ? 'rounded-br-md bg-indigo-600 text-white'
                : 'rounded-bl-md bg-white text-slate-800'">
                <p v-if="!isMine" class="mb-1 text-xs font-bold text-indigo-600">
                    {{ message.sender?.name }}
                </p>

                <template v-if="message.deleted_for_me">
    <p class="italic text-slate-400">
        You deleted this message
    </p>
</template>

<template v-else-if="message.deleted_at">
    <p class="italic text-slate-400">
        This message was deleted
    </p>
</template>

                <template v-else>
                    <div v-if="editing" class="space-y-2">
                        <textarea v-model="editBody" rows="2"
                            class="w-full resize-none rounded-2xl border-0 bg-white/90 px-3 py-2 text-sm text-slate-900 outline-none ring-2 ring-indigo-200 focus:ring-indigo-300" />

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="cancelEdit"
                                class="grid h-8 w-8 place-items-center rounded-xl bg-white/20 hover:bg-white/30">
                                <X class="h-4 w-4" />
                            </button>

                            <button type="button" @click="saveEdit"
                                class="grid h-8 w-8 place-items-center rounded-xl bg-white text-indigo-600">
                                <Check class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <p v-else-if="message.body" class="whitespace-pre-wrap text-sm leading-relaxed">
                        {{ message.body }}
                    </p>

                    <div v-if="message.attachments?.length" class="mt-3 grid gap-2">
                        <template v-for="attachment in message.attachments" :key="attachment.id">
                            <a v-if="isImage(attachment)" :href="fileUrl(attachment)" target="_blank"
                                class="block overflow-hidden rounded-2xl">
                                <img :src="fileUrl(attachment)" :alt="attachment.original_name"
                                    class="max-h-72 w-full object-cover" />
                            </a>

                            <a v-else :href="fileUrl(attachment)" target="_blank"
                                class="flex items-center gap-3 rounded-2xl bg-black/10 px-3 py-2 text-sm">
                                <FileText class="h-5 w-5" />
                                <span class="truncate">{{ attachment.original_name }}</span>
                            </a>
                        </template>
                    </div>
                </template>

                <div class="mt-2 flex items-center justify-end gap-1 text-[11px]"
                    :class="isMine ? 'text-indigo-100' : 'text-slate-400'">
                    <span>{{ time }}</span>

                    <span v-if="message.edited_at && !isDeleted">
                        · edited
                    </span>

                    <CheckCheck v-if="isMine && !isDeleted" class="h-3.5 w-3.5"
                        :class="isRead ? 'text-emerald-300' : 'text-indigo-200'" />
                </div>
            </div>

            <div v-if="isMine && !isDeleted && !editing"
                class="absolute -left-10 top-2 opacity-0 transition group-hover:opacity-100">
                <button type="button" @click="menuOpen = !menuOpen"
                    class="grid h-8 w-8 place-items-center rounded-xl bg-white text-slate-500 shadow hover:bg-slate-50">
                    <MoreVertical class="h-4 w-4" />
                </button>

                <div v-if="menuOpen"
                    class="absolute right-0 top-9 z-20 w-36 overflow-hidden rounded-2xl bg-white py-1 shadow-xl ring-1 ring-slate-100">
                    <button type="button" @click="startEdit"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-bold text-slate-600 hover:bg-slate-50">
                        <Pencil class="h-4 w-4" />
                        Edit
                    </button>

                    <button type="button" @click="deleteForMe"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-bold text-slate-600 hover:bg-slate-50">
                        <Trash2 class="h-4 w-4" />
                        Delete for me
                    </button>

                    <button v-if="canDeleteForEveryone" type="button" @click="deleteForEveryone"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-bold text-rose-600 hover:bg-rose-50">
                        <Trash2 class="h-4 w-4" />
                        Delete for everyone
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>