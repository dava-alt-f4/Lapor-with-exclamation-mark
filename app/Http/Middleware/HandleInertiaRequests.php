<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'adminUnreadCount' => fn (): int => $request->user()?->role === 'admin'
                ? Message::whereHas('conversation', function (Builder $query): void {
                    $query->whereHas('user', function (Builder $userQuery): void {
                        $userQuery->where('role', '!=', 'admin');
                    })->where(function (Builder $readQuery): void {
                        $readQuery->whereNull('conversations.admin_read_at')
                            ->orWhereColumn('messages.created_at', '>', 'conversations.admin_read_at');
                    });
                })->whereHas('sender', function (Builder $query): void {
                    $query->where('role', '!=', 'admin');
                })->count()
                : 0,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
