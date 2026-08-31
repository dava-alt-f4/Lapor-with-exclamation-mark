<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin
     */
    public function index(): Response
    {
        $users = User::latest()->get();

        return Inertia::render('admin/Dashboard', [
            'users' => $users,
        ]);
    }

    /**
     * Masukkan data
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        User::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User added.')]);

        return back();
    }

    /**
     * Tampilkan detail user
     */
    public function show(User $user): Response
    {
        $user = User::findOrFail($user->id);

        return Inertia::render('admin/UserDetail', [
            'user' => $user,
        ]);
    }

    /**
     * Perbarui data user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,user',
        ]);

        $user->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return back();
    }
}
