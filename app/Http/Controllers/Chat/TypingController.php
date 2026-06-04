<?php

namespace App\Http\Controllers\Chat;

use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'typing' => ['required', 'boolean'],
        ]);

        $conversation = Conversation::findOrFail($data['conversation_id']);

        abort_unless(
            $conversation->users()
                ->where('users.id', $request->user()->id)
                ->wherePivotNull('left_at')
                ->exists(),
            403
        );

        broadcast(new UserTyping(
            $conversation->id,
            $request->user(),
            $data['typing']
        ))->toOthers();

        return response()->noContent();
    }
}