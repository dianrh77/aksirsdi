<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class UsersController extends Controller
{
    /** 🔹 Tampilkan profil */
    public function profile()
    {
        return view('users.profile');
    }

    /** 🔹 Pengaturan akun */
    public function accountSettings()
    {
        return view('users.account-settings');
    }

    /** 🔹 Daftar semua user */
    public function index()
    {
        // load positions untuk tampilan
        $users = User::with('positions')->orderBy('id', 'desc')->get();
        return view('users.index', compact('users'));
    }

    /** 🔹 Form tambah user */
    public function create()
    {
        $positions = Position::orderBy('name')->get();
        $positionsHierarchy = $this->buildHierarchy($positions);

        return view('users.create', compact('positions', 'positionsHierarchy'));
    }

    private function buildHierarchy($positions, $parentId = null, $level = 0)
    {
        $result = [];

        foreach ($positions->where('parent_id', $parentId) as $pos) {
            $result[] = (object) [
                'id'   => $pos->id,
                'name' => str_repeat('— ', $level) . $pos->name,
            ];

            $children = $this->buildHierarchy($positions, $pos->id, $level + 1);
            $result   = array_merge($result, $children);
        }

        return $result;
    }

    /** 🔹 Simpan user baru */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'required|string|email|max:255|unique:users',
                'password'     => 'required|confirmed|min:6',
                'role_name'    => 'required|string',
                'positions'    => 'required|array',
                'positions.*'  => 'exists:positions,id',
                'phone_number' => 'nullable|string|max:255',
                'avatar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status'       => 'required|string',
            ]);

            /** 🔹 Upload foto */
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            /** 🔹 Simpan user */
            $user = User::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'join_date'    => now(),
                'role_name'    => $request->role_name,
                'phone_number' => $request->phone_number,
                'status'       => $request->status,
                'password'     => Hash::make($request->password),
                'avatar'       => $avatarPath,
            ]);

            /** 🔹 Attach multi-position */
            $positions = $request->positions;
            $pivotData = [];
            $firstId   = $positions[0];

            foreach ($positions as $pid) {
                $pivotData[$pid] = [
                    'is_primary' => $pid == $firstId,
                ];
            }

            $user->positions()->sync($pivotData);

            Alert::success('Berhasil!', 'User baru berhasil ditambahkan.');
            return redirect()->route('users.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Alert::error('Gagal!', 'Periksa kembali inputan Anda.');
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Alert::error('Terjadi Kesalahan!', $e->getMessage());
            return back()->withInput();
        }
    }

    /** 🔹 Form edit user */
    public function edit($id)
    {
        $title = 'Hapus User!';
        $text  = "Apakah kamu yakin ingin menghapus user ini?";
        confirmDelete($title, $text);

        $positions  = Position::orderBy('name')->get();
        $user       = User::with('positions')->findOrFail($id);
        $positionsHierarchy = $this->buildHierarchy($positions);

        return view('users.edit', compact('user', 'positions', 'positionsHierarchy'));
    }

    /** 🔹 Update data user */
    public function update(Request $request, $id)
    {
        try {
            $user = User::with('positions')->findOrFail($id);

            $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => "required|email|unique:users,email,{$user->id}",
                'role_name'    => 'required|string',
                'status'       => 'required|string',
                'positions'    => 'required|array',
                'positions.*'  => 'exists:positions,id',
                'phone_number' => 'nullable|string|max:255',
                'avatar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $data = $request->only(['name', 'email', 'role_name', 'phone_number', 'status']);

            // 🔥 Update password bila diisi
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'confirmed|min:6',
                ]);

                $data['password'] = Hash::make($request->password);
            }

            // 🔥 Update avatar bila diupload
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($data);

            /** 🔹 Sync posisi */
            $positions = $request->positions;
            $pivotData = [];
            $firstId   = $positions[0];

            foreach ($positions as $pid) {
                $pivotData[$pid] = [
                    'is_primary' => $pid == $firstId,
                ];
            }

            $user->positions()->sync($pivotData);

            Alert::success('Berhasil!', 'Data user berhasil diperbarui.');
            return redirect()->route('users.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Alert::error('Gagal!', 'Periksa kembali inputan Anda.');
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Alert::error('Terjadi Kesalahan!', $e->getMessage());
            return back()->withInput();
        }
    }

    /** 🔹 Hapus user */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            Alert::success('Berhasil!', 'User berhasil dihapus.');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::error('Gagal Menghapus!', $e->getMessage());
            return back();
        }
    }
}
