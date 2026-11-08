<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // <--- Import Rule Password Laravel

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.user', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email|max:255',
            'password' => ['required', Password::min(6)->numbers()],
            'role'     => 'required|in:admin,petugas',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect('/admin/user')->with('success', 'User baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'role'  => 'required|in:admin,petugas',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => [Password::min(6)->numbers()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect('/admin/user')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        # Mencegah Admin menghapus akun sendiri
        if (auth()->id() == $user->id) {
            return redirect('/admin/user')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();
        return redirect('/admin/user')->with('success', 'User berhasil dihapus!');
    }
}