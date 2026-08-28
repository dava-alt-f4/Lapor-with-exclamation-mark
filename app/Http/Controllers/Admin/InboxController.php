<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InboxController extends Controller
{
    public function index(): Response
    {
        $conversations = $this->conversationQuery()->get();

        return Inertia::render('admin/Inbox', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation): Response
    {
        $this->ensureAccessibleConversation($conversation);
        $this->markConversationAsRead($conversation);

        $conversations = $this->conversationQuery()->get();

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return Inertia::render('admin/Inbox', [
            'conversations' => $conversations,
            'activeConversation' => $conversation->load('user'),
            'messages' => $messages,
        ]);
    }

    public function markRead(Conversation $conversation): RedirectResponse
    {
        $this->ensureAccessibleConversation($conversation);
        $this->markConversationAsRead($conversation);

        return back();
    }

    private function ensureAccessibleConversation(Conversation $conversation): void
    {
        abort_unless($conversation->user()->where('role', '!=', 'admin')->exists(), 404);
    }

    private function markConversationAsRead(Conversation $conversation): void
    {
        $latestMessageAt = $conversation->messages()->latest('created_at')->value('created_at');

        if ($latestMessageAt !== null) {
            $conversation->update(['admin_read_at' => $latestMessageAt]);
        }
    }

    /**
     * @return Builder<Conversation>
     */
    private function conversationQuery(): Builder
    {
        return Conversation::whereHas('user', function (Builder $query): void {
            $query->where('role', '!=', 'admin');
        })->with(['user', 'messages' => function ($query): void {
            $query->latest()->take(1);
        }])->withCount(['messages as unread_count' => function (Builder $query): void {
            $query->whereHas('sender', function (Builder $senderQuery): void {
                $senderQuery->where('role', '!=', 'admin');
            })->where(function (Builder $readQuery): void {
                $readQuery->whereNull('conversations.admin_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversations.admin_read_at');
            });
        }])->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        );
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message));

        return back();
    }
}
