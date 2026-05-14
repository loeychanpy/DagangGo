<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'in:owner,staff'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return back()->with('status', 'User berhasil ditambahkan!');
    }

    public function destroy(User $user)
    {
        // Mencegah owner menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return back()->with('status', 'User berhasil dihapus!');
    }
}