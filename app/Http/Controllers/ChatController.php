<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id]
        );

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return Inertia::render('Dashboard', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if ($user->role === 'admin') {
            return abort(403);
        }

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message));

        return back();
    }
}
