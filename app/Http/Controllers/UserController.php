<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'TAMBAH_USER',
            'description' => "Menambahkan pengguna {$user->name} ({$user->email}), role: {$user->role}",
            'ip_address'  => request()->ip(),
        ]);

        return back()->with('status', 'User berhasil ditambahkan!');
    }

    public function destroy(User $user)
    {
        // Mencegah owner menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->withErrors('Anda tidak bisa menghapus akun sendiri!');
        }

        $userName  = $user->name;
        $userEmail = $user->email;
        $user->delete();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'HAPUS_USER',
            'description' => "Menghapus pengguna {$userName} ({$userEmail})",
            'ip_address'  => request()->ip(),
        ]);

        return back()->with('status', 'User berhasil dihapus!');
    }
}