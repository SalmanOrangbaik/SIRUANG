<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    public function index()
    {
        $user = User::latest()->get();
        return view('backend.user.index', compact('user'));
    }

    public function create()
    {
        return view('backend.user.create');
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateWithToast($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:0,1',
            'status' => 'required|in:Siswa,Guru',
            'nisn' => 'nullable|string|max:50|required_if:status,Siswa',
            'nip' => 'nullable|string|max:50|required_if:status,Guru',
        ]);

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'isAdmin' => $validatedData['role'],
            'status' => $validatedData['status'],
            'nisn' => $validatedData['status'] === 'Siswa' ? ($validatedData['nisn'] ?? null) : null,
            'nip' => $validatedData['status'] === 'Guru' ? ($validatedData['nip'] ?? null) : null,
        ]);

        toast('Data user berhasil ditambahkan!', 'success')->autoClose(3000);
        return redirect()->route('backend.user.index');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('backend.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:0,1',
            'status' => 'required|in:Siswa,Guru',
            'nisn' => 'nullable|string|max:50|required_if:status,Siswa',
            'nip' => 'nullable|string|max:50|required_if:status,Guru',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'confirmed|min:6';
        }

        $validatedData = $this->validateWithToast($request, $rules);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->isAdmin = $validatedData['role'];
        $user->status = $validatedData['status'];
        $user->nisn = $validatedData['status'] === 'Siswa' ? ($validatedData['nisn'] ?? null) : null;
        $user->nip = $validatedData['status'] === 'Guru' ? ($validatedData['nip'] ?? null) : null;

        if (!empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        $user->save();

        toast('User berhasil diperbarui.', 'success')->autoClose(3000);
        return redirect()->route('backend.user.index');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        toast('User berhasil dihapus.', 'success')->autoClose(3000);
        return redirect()->route('backend.user.index');
    }
}
