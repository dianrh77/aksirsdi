@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- PANEL KIRI -->
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Profil Saya</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Perbarui informasi akun Anda di bawah ini.
                        </p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-20">
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- GRID FORM -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" name="name" class="form-input w-full"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- <!-- Jabatan -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Jabatan</label>
                            <select name="position" class="form-select w-full">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($positions as $p)
                                    <option value="{{ $p }}"
                                        {{ old('position', $user->position) == $p ? 'selected' : '' }}>
                                        {{ $p }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div> --}}

                        <!-- Email -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" class="form-input w-full"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Password Baru</label>
                            <input type="password" name="password" class="form-input w-full"
                                placeholder="Kosongkan jika tidak ingin diubah">
                            @error('password')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-input w-full"
                                placeholder="Ulangi password baru jika diisi">
                        </div>

                        <!-- No Whatsapp -->
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">No Whatsapp</label>
                            <input type="text" name="phone_number" class="form-input w-full" placeholder="08xxxxxxxxx"
                                value="{{ old('phone_number', $user->phone_number) }}">
                            @error('phone_number')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- FOTO PROFIL -->
                    <div class="mt-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">Foto Profil</label>
                        <div class="flex items-center gap-4">
                            <img id="previewImage"
                                src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/default-avatar.png') }}"
                                class="w-20 h-20 rounded-full border border-gray-300 object-cover" alt="Preview">
                            <input type="file" name="avatar" id="photoInput" class="form-input w-full" accept="image/*">
                        </div>
                        <small class="text-gray-500 text-sm block mt-2">
                            Format: JPG atau PNG. Maksimal 2MB.
                        </small>
                        @error('avatar')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- BUTTON -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn btn-success">Update Profil</button>
                    </div>
                </form>
            </div>

            <!-- PANEL KANAN -->
            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Edit Profil</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 text-sm space-y-2">
                    <li>Ubah hanya data yang perlu diperbarui.</li>
                    <li>Kosongkan password jika tidak ingin mengganti.</li>
                    <li>Foto profil baru akan menggantikan foto lama.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 text-center text-sm text-gray-400">
                    <p>Profil Saya v1.0</p>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT PREVIEW FOTO -->
    <script>
        document.getElementById('photoInput').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                document.getElementById('previewImage').src = URL.createObjectURL(file);
            }
        });
    </script>
@endsection
