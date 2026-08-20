<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin
     *
     * @return Response
     */
    public function index(): Response
    {
        $users = User::latest()->get();

        return Inertia::render("admin/Index", [
            "users"=> $users,
        ]);
    }

    /**
     * Masukkan data
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name"=> "required|string|max:255",
            "email"=> "required|string|email|max:255|unique:users",
            "password"=> "required|string|min:8",
            "role" => "required|in:admin,user",
        ]);

        User::create($validated);

        return back()->with('success', 'User added successfully');
    }

    /**
     * Perbarui data user
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            "name"=> "required|string|max:255",
            "email"=> "required|string|email|max:255|unique:users,email," . $user->id,
            "role" => "required|in:admin,user"
        ]);

        $user->update($validated);

        return back()->with("success","User updated successfully");
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('success','User deleted successfully');
    }
}
