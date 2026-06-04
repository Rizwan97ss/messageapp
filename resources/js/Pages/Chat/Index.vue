<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Sidebar from './Sidebar.vue'
import ChatWindow from './ChatWindow.vue'
import CreateGroupModal from './CreateGroupModal.vue'
import { encryptMessage, decryptMessage } from '@/crypto/chatCrypto'
const conversations = ref([])
const activeConversation = ref(null)
const messages = ref([])
const typingUsers = ref([])
const showGroupModal = ref(false)
const drafts = ref({})
let activeChannel = null

const page = usePage()
const currentUser = page.props.auth.user

async function loadConversations(autoSelect = true) {
    const { data } = await axios.get(route('chat.conversations.index'))
    conversations.value = data.map(item => ({
        ...item,
        draft: drafts.value[item.id] || null,
    }))

    if (autoSelect && !activeConversation.value && data.length) {
        await selectConversation(data[0])
    }

    if (activeConversation.value) {
        const freshConversation = data.find(item => item.id === activeConversation.value.id)

        if (freshConversation) {
            activeConversation.value = freshConversation
        }
    }
}
function updateDraft(conversationId, draft) {
    drafts.value[conversationId] = draft

    conversations.value = conversations.value.map(item => {
        if (item.id !== conversationId) return item

        return {
            ...item,
            draft,
        }
    })
}
async function selectConversation(conversation) {
    activeConversation.value = conversation
    messages.value = []
    typingUsers.value = []

    const { data } = await axios.get(route('chat.messages.index', conversation.id))
    const loadedMessages = data.data.reverse()

    for (const message of loadedMessages) {
        message.body = await decryptMessage(conversation.id, message)
    }

    messages.value = loadedMessages
    for (const message of messages.value) {
        if (message.sender_id !== currentUser.id) {
            axios.post(route('chat.messages.read', message.id))
        }
    }

    if (activeChannel) {
        window.Echo.leaveChannel(`private-conversation.${activeChannel}`)
    }

    activeChannel = conversation.id

    window.Echo.private(`conversation.${conversation.id}`)
        .listen('.message.sent', async (event) => {
            event.message.body = await decryptMessage(activeConversation.value.id, event.message)
            const exists = messages.value.some(item => Number(item.id) === Number(event.message.id))

            if (!exists) {
                messages.value.push(event.message)
            }

            if (event.message.sender_id !== currentUser.id) {
                await axios.post(route('chat.messages.read', event.message.id))
            }

            await loadConversations(false)
        })
        .listen('.user.typing', (event) => {
            if (event.user.id === currentUser.id) return

            typingUsers.value = event.typing ? [event.user] : []
        })
        .listen('.message.read', (event) => {
            const message = messages.value.find(item => item.id === event.message_id)

            if (!message) return

            message.reads = message.reads || []

            const exists = message.reads.some(read => read.user_id === event.user.id)

            if (!exists) {
                message.reads.push({
                    user_id: event.user.id,
                    read_at: event.read_at,
                })
            }
        }).listen('.message.updated', (event) => {
            const index = messages.value.findIndex(item => item.id === event.message.id)

            if (index !== -1) {
                messages.value[index] = event.message
            }
        })
        .listen('.message.deleted', async (event) => {
            const index = messages.value.findIndex(item => Number(item.id) === Number(event.message_id))

            if (index !== -1) {
                messages.value[index] = {
                    ...messages.value[index],
                    body: null,
                    deleted_at: event.deleted_at || new Date().toISOString(),
                    attachments: [],
                }

                return
            }

            if (activeConversation.value?.id === event.conversation_id) {
                const { data } = await axios.get(route('chat.messages.index', event.conversation_id))
                messages.value = data.data.reverse()
            }
        })
}

async function sendMessage(payload) {
    await sendTyping(false)

    const plainBody = payload.get('body') || ''

    if (plainBody) {
        const encrypted = await encryptMessage(activeConversation.value.id, plainBody)

        payload.set('body', '')
        payload.set('ciphertext', encrypted.ciphertext)
        payload.set('nonce', encrypted.nonce)
        payload.set('encryption_meta[version]', encrypted.encryption_meta.version)
        payload.set('encryption_meta[algorithm]', encrypted.encryption_meta.algorithm)
    }

    const { data } = await axios.post(
        route('chat.messages.store', activeConversation.value.id),
        payload,
        { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    data.body = await decryptMessage(activeConversation.value.id, data)

    const exists = messages.value.some(item => Number(item.id) === Number(data.id))

    if (!exists) {
        messages.value.push(data)
    }

    await loadConversations(false)
}

async function sendTyping(value) {
    if (!activeConversation.value) return

    await axios.post(route('chat.typing'), {
        conversation_id: activeConversation.value.id,
        typing: value,
    })
}
async function updateMessage(message, body) {
    const { data } = await axios.patch(route('chat.messages.update', message.id), {
        body,
    })

    const index = messages.value.findIndex(item => item.id === message.id)

    if (index !== -1) {
        messages.value[index] = data
    }

    await loadConversations(false)
}

async function deleteMessage(message, mode = 'me') {
    try {
        const { data } = await axios.delete(route('chat.messages.destroy', message.id), {
            data: { mode },
        })

        if (data.mode === 'me') {
    const index = messages.value.findIndex(item => item.id === message.id)

    if (index !== -1) {
        messages.value[index] = {
            ...messages.value[index],

            body: null,
            ciphertext: null,
            nonce: null,
            encryption_meta: null,

            deleted_for_me: true,
            deleted_at: data.deleted_at || new Date().toISOString(),

            attachments: [],
        }
    }

    return
}

        const index = messages.value.findIndex(item => item.id === message.id)

        if (index !== -1) {
            messages.value[index] = {
                ...messages.value[index],
                body: null,
                ciphertext: null,
                nonce: null,
                encryption_meta: null,
                edited_at: null,
                deleted_at: data.deleted_at || new Date().toISOString(),
                attachments: [],
            }
        }

        await loadConversations(false)
    } catch (error) {
        alert(error.response?.data?.message || 'Message could not be deleted.')
    }
}
async function leaveGroup() {
    await axios.delete(route('chat.conversations.leave', activeConversation.value.id))

    if (activeChannel) {
        window.Echo.leaveChannel(`private-conversation.${activeChannel}`)
    }

    activeConversation.value = null
    messages.value = []

    await loadConversations()
}

async function handleConversationCreated(conversation) {
    showGroupModal.value = false
    await loadConversations(false)
    await selectConversation(conversation)
}
function replaceConversation(updatedConversation) {
    conversations.value = conversations.value.map(item => {
        return item.id === updatedConversation.id ? updatedConversation : item
    })

    if (activeConversation.value?.id === updatedConversation.id) {
        activeConversation.value = updatedConversation
    }
}
async function addGroupMembers(userIds) {
    const { data } = await axios.post(
        route('chat.conversations.members.add', activeConversation.value.id),
        { user_ids: userIds }
    )

    replaceConversation(data)
}

async function removeGroupMember(userId) {
    const { data } = await axios.delete(
        route('chat.conversations.members.remove', [
            activeConversation.value.id,
            userId,
        ])
    )

    replaceConversation(data)
}

async function updateGroupMemberRole(userId, role) {
    const { data } = await axios.patch(
        route('chat.conversations.members.role', [
            activeConversation.value.id,
            userId,
        ]),
        { role }
    )

    replaceConversation(data)
}
onMounted(loadConversations)
</script>

<template>
    <AppLayout title="Chat">
        <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-br from-sky-50 via-indigo-50 to-rose-50 p-3 sm:p-4">
            <div class="mx-auto flex h-[calc(100vh-5.5rem)] max-w-7xl gap-4">
                <Sidebar :conversations="conversations" :active-id="activeConversation?.id"
                    :class="activeConversation ? 'hidden md:flex' : 'flex'" @select="selectConversation"
                    @create-group="showGroupModal = true" />

                <ChatWindow v-if="activeConversation" :key="activeConversation.id" :conversation="activeConversation"
                    :messages="messages" :typing-users="typingUsers"
                    :draft="drafts[activeConversation?.id] || { body: '', files: [] }" class="flex"
                    @back="activeConversation = null" @send="sendMessage" @typing="sendTyping"
                    @draft-change="draft => updateDraft(activeConversation.id, draft)" @leave="leaveGroup"
                    @update-message="updateMessage" @delete-message="deleteMessage" @add-members="addGroupMembers"
                    @remove-member="removeGroupMember" @update-member-role="updateGroupMemberRole" />

                <div v-else
                    class="hidden flex-1 items-center justify-center rounded-3xl border border-white/70 bg-white/70 shadow-xl backdrop-blur md:flex">
                    <div class="max-w-md text-center">
                        <div class="mx-auto mb-4 grid h-20 w-20 place-items-center rounded-3xl bg-indigo-100 text-4xl">
                            💬
                        </div>

                        <h2 class="text-2xl font-black text-slate-900">
                            Select or start a conversation
                        </h2>

                        <p class="mt-2 text-slate-500">
                            Chat with registered users, create groups, share media, and receive realtime updates.
                        </p>

                        <button type="button" @click="showGroupModal = true"
                            class="mt-6 rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700">
                            Start New Chat
                        </button>
                    </div>
                </div>
            </div>

            <CreateGroupModal v-if="showGroupModal" @close="showGroupModal = false"
                @created="handleConversationCreated" />
        </div>
    </AppLayout>
</template>