<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use Illuminate\Http\Request;
use App\Events\MessageReadEvent;
use Illuminate\Support\Facades\DB;
use App\Events\MessageUpdated;
use App\Models\MessageDeletion;
use App\Events\MessageDeleted;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation)
    {
        $this->authorizeMember($request, $conversation);

        return $conversation->messages()
            ->whereDoesntHave('deletions', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with(['sender:id,name,profile_photo_path', 'attachments', 'reads'])
            ->latest()
            ->paginate(30);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $this->authorizeMember($request, $conversation);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'ciphertext' => ['nullable', 'string'],
            'nonce' => ['nullable', 'string'],
            'encryption_meta' => ['nullable', 'array'],
            'type' => ['required', 'in:text,image,video,audio,file'],
            'attachments.*' => ['file', 'max:51200'],
        ]);

        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,

                // Do not store plaintext
                'body' => null,

                'ciphertext' => $data['ciphertext'] ?? null,
                'nonce' => $data['nonce'] ?? null,
                'encryption_meta' => $data['encryption_meta'] ?? null,

                'type' => $data['type'],
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('chat-media', 'public');

                $message->attachments()->create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $conversation->touch();

            return $message->load(['sender:id,name,profile_photo_path', 'attachments', 'reads']);
        });

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function read(Request $request, Message $message)
    {
        $this->authorizeMember($request, $message->conversation);

        MessageRead::updateOrCreate(
            [
                'message_id' => $message->id,
                'user_id' => $request->user()->id,
            ],
            ['read_at' => now()]
        );

        broadcast(new MessageReadEvent($message, $request->user()))->toOthers();

        return response()->noContent();
    }

    private function authorizeMember(Request $request, Conversation $conversation): void
    {
        abort_unless(
            $conversation->users()
                ->where('users.id', $request->user()->id)
                ->wherePivotNull('left_at')
                ->exists(),
            403
        );
    }

    public function update(Request $request, Message $message)
    {
        $this->authorizeMember($request, $message->conversation);

        abort_unless($message->sender_id === $request->user()->id, 403);

        abort_if($message->deleted_at, 422, 'Deleted messages cannot be edited.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message->update([
            'body' => $data['body'],
            'edited_at' => now(),
        ]);

        $message->refresh()->load(['sender:id,name,profile_photo_path', 'attachments', 'reads']);

        broadcast(new MessageUpdated($message))->toOthers();

        return response()->json($message);
    }
    public function destroy(Request $request, Message $message)
    {
        $this->authorizeMember($request, $message->conversation);

        $data = $request->validate([
            'mode' => ['required', 'in:me,everyone'],
        ]);

        if ($data['mode'] === 'me') {
            MessageDeletion::updateOrCreate(
                [
                    'message_id' => $message->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'deleted_at' => now(),
                ]
            );

            return response()->json([
                'message_id' => $message->id,
                'mode' => 'me',
                'deleted_at' => now()->toISOString(),
            ]);
        }

        abort_unless($message->sender_id === $request->user()->id, 403, 'Only the sender can delete this message for everyone.');

        abort_if($message->deleted_at, 422, 'This message is already deleted.');

        $deleteLimitMinutes = 60 * 24 * 2; // 2 days like WhatsApp-style limit

        abort_if(
            $message->created_at->lt(now()->subMinutes($deleteLimitMinutes)),
            422,
            'You can only delete messages for everyone within 2 days.'
        );

        DB::transaction(function () use ($message) {
            $message->load('attachments');

            foreach ($message->attachments as $attachment) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            $message->attachments()->delete();

            $message->update([
                'body' => null,
                'ciphertext' => null,
                'nonce' => null,
                'encryption_meta' => null,
                'edited_at' => null,
                'deleted_at' => now(),
            ]);
        });

        $message->refresh()->load(['sender:id,name,profile_photo_path', 'attachments', 'reads']);

        broadcast(new MessageDeleted($message))->toOthers();

        return response()->json([
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'mode' => 'everyone',
            'deleted_at' => $message->deleted_at?->toISOString(),
        ]);
    }
}
