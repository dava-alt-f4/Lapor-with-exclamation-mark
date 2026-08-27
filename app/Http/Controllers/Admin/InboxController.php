<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InboxController extends Controller
{
    public function index()
    {
        $conversations = Conversation::whereHas('user', function ($query) {
            $query->where('role', '!=', 'admin');
        })->with(['user', 'messages' => function ($query) {
            $query->latest()->take(1);
        }])->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        )->get();

        return Inertia::render('admin/Inbox', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $conversations = Conversation::whereHas('user', function ($query) {
            $query->where('role', '!=', 'admin');
        })->with(['user', 'messages' => function ($query) {
            $query->latest()->take(1);
        }])->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        )->get();

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return Inertia::render('admin/Inbox', [
            'conversations' => $conversations,
            'activeConversation' => $conversation->load('user'),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, Conversation $conversation)
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
