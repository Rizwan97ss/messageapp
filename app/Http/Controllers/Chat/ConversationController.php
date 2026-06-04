<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->conversations()
            ->wherePivotNull('left_at')
            ->with([
                'activeUsers:id,name,email,profile_photo_path',
                'messages' => fn($query) => $query
                    ->latest()
                    ->limit(1)
                    ->with('sender:id,name'),
            ])
            ->latest('conversations.updated_at')
            ->get();
    }

    public function users(Request $request)
    {
        $search = $request->string('search')->toString();

        return User::query()
            ->where('id', '!=', $request->user()->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'profile_photo_path')
            ->limit(20)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
            ]);
    }

    public function direct(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        abort_if((int) $data['user_id'] === $request->user()->id, 422);

        $currentUserId = $request->user()->id;
        $otherUserId = (int) $data['user_id'];

        $existing = Conversation::query()
            ->where('type', 'direct')
            ->whereHas('users', fn($q) => $q->where('users.id', $currentUserId))
            ->whereHas('users', fn($q) => $q->where('users.id', $otherUserId))
            ->with('activeUsers:id,name,email,profile_photo_path')
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = DB::transaction(function () use ($currentUserId, $otherUserId) {
            $conversation = Conversation::create([
                'type' => 'direct',
                'created_by' => $currentUserId,
            ]);

            $conversation->users()->attach([
                $currentUserId => [
                    'role' => 'owner',
                    'joined_at' => now(),
                ],
                $otherUserId => [
                    'role' => 'member',
                    'joined_at' => now(),
                ],
            ]);

            return $conversation;
        });

        return $conversation->load('activeUsers:id,name,email,profile_photo_path');
    }

    public function group(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $currentUserId = $request->user()->id;

        $conversation = DB::transaction(function () use ($data, $currentUserId) {
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $data['name'],
                'created_by' => $currentUserId,
            ]);

            collect($data['user_ids'])
                ->push($currentUserId)
                ->unique()
                ->each(function ($userId) use ($conversation, $currentUserId) {
                    $conversation->users()->attach($userId, [
                        'role' => $userId === $currentUserId ? 'owner' : 'member',
                        'joined_at' => now(),
                    ]);
                });

            return $conversation;
        });

        return $conversation->load('activeUsers:id,name,email,profile_photo_path');
    }

    public function leave(Request $request, Conversation $conversation)
    {
        abort_unless(
            $conversation->users()
                ->where('users.id', $request->user()->id)
                ->wherePivotNull('left_at')
                ->exists(),
            403
        );

        $conversation->users()->updateExistingPivot($request->user()->id, [
            'left_at' => now(),
        ]);

        return response()->noContent();
    }

    private function currentRole(Request $request, Conversation $conversation): ?string
    {
        $member = $conversation->users()
            ->where('users.id', $request->user()->id)
            ->wherePivotNull('left_at')
            ->first();

        return $member?->pivot?->role;
    }

    private function canManageMembers(Request $request, Conversation $conversation): bool
    {
        return in_array($this->currentRole($request, $conversation), ['owner', 'admin'], true);
    }

    private function isOwner(Request $request, Conversation $conversation): bool
    {
        return $this->currentRole($request, $conversation) === 'owner';
    }
    public function addMembers(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->type === 'group', 422, 'Members can only be added to groups.');
        abort_unless($this->canManageMembers($request, $conversation), 403);

        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        foreach (collect($data['user_ids'])->unique() as $userId) {
            $existing = $conversation->users()
                ->where('users.id', $userId)
                ->first();

            if ($existing) {
                $conversation->users()->updateExistingPivot($userId, [
                    'left_at' => null,
                    'joined_at' => now(),
                ]);
            } else {
                $conversation->users()->attach($userId, [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        $conversation->touch();

        return $conversation->load('activeUsers:id,name,email,profile_photo_path');
    }

    public function removeMember(Request $request, Conversation $conversation, User $user)
    {
        abort_unless($conversation->type === 'group', 422, 'Members can only be removed from groups.');
        abort_unless($this->canManageMembers($request, $conversation), 403);

        $target = $conversation->users()
            ->where('users.id', $user->id)
            ->wherePivotNull('left_at')
            ->firstOrFail();

        $targetRole = $target->pivot->role;
        $currentRole = $this->currentRole($request, $conversation);

        abort_if($targetRole === 'owner', 403, 'Owner cannot be removed.');
        abort_if($currentRole === 'admin' && $targetRole === 'admin', 403, 'Admins cannot remove other admins.');

        $conversation->users()->updateExistingPivot($user->id, [
            'left_at' => now(),
        ]);

        $conversation->touch();

        return $conversation->load('activeUsers:id,name,email,profile_photo_path');
    }

    public function updateMemberRole(Request $request, Conversation $conversation, User $user)
    {
        abort_unless($conversation->type === 'group', 422, 'Roles can only be changed in groups.');
        abort_unless($this->canManageMembers($request, $conversation), 403);

        $data = $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        $target = $conversation->users()
            ->where('users.id', $user->id)
            ->wherePivotNull('left_at')
            ->firstOrFail();

        $targetRole = $target->pivot->role;

        abort_if($targetRole === 'owner', 403, 'Owner role cannot be changed.');

        // Only owner can demote admins.
        if ($targetRole === 'admin' && $data['role'] === 'member') {
            abort_unless($this->isOwner($request, $conversation), 403);
        }

        // Admin can promote members to admin.
        // Owner can promote/demote everyone except owner.
        $conversation->users()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        $conversation->touch();

        return $conversation->load('activeUsers:id,name,email,profile_photo_path');
    }
}
