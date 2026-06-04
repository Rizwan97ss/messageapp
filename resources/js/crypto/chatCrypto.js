import sodium from 'libsodium-wrappers'

export async function getConversationKey(conversationId) {
    await sodium.ready

    const demoKey = 'LOCALHOST_DEMO_CHAT_SECRET_KEY_32B'

    return sodium.crypto_generichash(
        sodium.crypto_secretbox_KEYBYTES,
        demoKey + '_' + conversationId
    )
}

export async function encryptMessage(conversationId, text) {
    await sodium.ready

    const key = await getConversationKey(conversationId)
    const nonce = sodium.randombytes_buf(sodium.crypto_secretbox_NONCEBYTES)

    const ciphertext = sodium.crypto_secretbox_easy(text, nonce, key)

    return {
        ciphertext: sodium.to_base64(ciphertext),
        nonce: sodium.to_base64(nonce),
        encryption_meta: {
            version: 1,
            algorithm: 'secretbox-demo',
        },
    }
}

export async function decryptMessage(conversationId, message) {
    await sodium.ready

    if (!message.ciphertext || !message.nonce) {
        return message.body || ''
    }

    try {
        const key = await getConversationKey(conversationId)
        const ciphertext = sodium.from_base64(message.ciphertext)
        const nonce = sodium.from_base64(message.nonce)

        const decrypted = sodium.crypto_secretbox_open_easy(ciphertext, nonce, key)

        return sodium.to_string(decrypted)
    } catch (error) {
        console.error('Decrypt failed:', error, message)

        return 'Failed to decrypt'
    }
}