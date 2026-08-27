<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use App\Events\MessageSent;

test('user can view their chat dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $this->assertDatabaseHas('conversations', [
        'user_id' => $user->id,
    ]);
});

test('user can send a message', function () {
    Event::fake([MessageSent::class]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('chat.store'), [
        'body' => 'Hello Admin!',
    ]);

    $response->assertRedirect();

    $conversation = Conversation::where('user_id', $user->id)->first();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
        'body' => 'Hello Admin!',
    ]);

    Event::assertDispatched(MessageSent::class);
});

test('admin can view inbox', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    Conversation::create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get(route('admin.inbox.index'));

    $response->assertOk();
});

test('admin can view specific conversation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $conversation = Conversation::create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get(route('admin.inbox.show', $conversation));

    $response->assertOk();
});

test('admin can reply to a conversation', function () {
    Event::fake([MessageSent::class]);

    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $conversation = Conversation::create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->post(route('admin.inbox.store', $conversation), [
        'body' => 'Hello User!',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $admin->id,
        'body' => 'Hello User!',
    ]);

    Event::assertDispatched(MessageSent::class);
});

test('non-admin cannot access inbox', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get(route('admin.inbox.index'));

    $response->assertForbidden();
});
