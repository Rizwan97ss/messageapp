<script setup>
import { ref, watch } from 'vue'
import { ImagePlus, SendHorizonal, X } from 'lucide-vue-next'

const props = defineProps({
    draft: {
        type: Object,
        default: () => ({
            body: '',
            files: [],
        }),
    },
})

const emit = defineEmits(['send', 'typing', 'draft-change'])

const body = ref('')
const files = ref([])
let typingTimer = null

watch(
    () => props.draft,
    () => {
        body.value = props.draft?.body || ''
        files.value = props.draft?.files || []
    },
    { immediate: true, deep: true }
)

function emitDraft() {
    emit('draft-change', {
        body: body.value,
        files: files.value,
    })
}

function onFiles(event) {
    files.value = Array.from(event.target.files || [])
    emitDraft()

    event.target.value = ''
}

function removeFile(index) {
    files.value.splice(index, 1)
    emitDraft()
}

function handleTyping() {
    emitDraft()

    emit('typing', true)

    clearTimeout(typingTimer)

    typingTimer = setTimeout(() => {
        emit('typing', false)
    }, 900)
}

function submit() {
    if (!body.value.trim() && !files.value.length) return

    const form = new FormData()

    form.append('body', body.value)
    form.append('type', files.value.length ? 'file' : 'text')

    files.value.forEach(file => {
        form.append('attachments[]', file)
    })

    emit('send', form)

    body.value = ''
    files.value = []

    emit('draft-change', {
        body: '',
        files: [],
    })
    clearTimeout(typingTimer)

    emit('typing', false)
}
</script>

<template>
    <form
        class="border-t border-white/70 bg-white/70 p-4"
        @submit.prevent="submit"
    >
        <div
            v-if="files.length"
            class="mb-3 flex flex-wrap gap-2"
        >
            <div
                v-for="(file, index) in files"
                :key="file.name + index"
                class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700"
            >
                <span class="max-w-44 truncate">{{ file.name }}</span>

                <button
                    type="button"
                    @click="removeFile(index)"
                    class="rounded-full p-1 hover:bg-indigo-100"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <div class="flex items-end gap-3 rounded-3xl bg-white p-3 shadow-inner">
            <label class="grid h-12 w-12 cursor-pointer place-items-center rounded-2xl bg-slate-100 text-slate-600 transition hover:bg-slate-200">
                <ImagePlus class="h-5 w-5" />

                <input
                    type="file"
                    multiple
                    class="hidden"
                    @change="onFiles"
                />
            </label>

            <textarea
                v-model="body"
                rows="1"
                placeholder="Type a message..."
                class="max-h-32 min-h-12 flex-1 resize-none border-0 bg-transparent px-2 py-3 text-sm outline-none ring-0 placeholder:text-slate-400 focus:ring-0"
                @input="handleTyping"
                @keydown.enter.exact.prevent="submit"
            />

            <button
                type="submit"
                class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"
            >
                <SendHorizonal class="h-5 w-5" />
            </button>
        </div>
    </form>
</template>