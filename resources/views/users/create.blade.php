@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="flex flex-col xl:flex-row gap-6">

            <!-- PANEL KIRI -->
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Tambah User Baru</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Lengkapi data user baru di bawah ini.
                        </p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-20">
                </div>

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- GRID FORM -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" name="name" class="form-input w-full"
                                placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Jabatan</label>
                            <select name="positions[]" id="positions" class="form-select w-full" multiple>
                                @foreach ($positionsHierarchy as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" class="form-input w-full" placeholder="Masukkan email user"
                                value="{{ old('email') }}" required>
                            @error('email')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">No Whatsapp</label>
                            <input type="text" name="phone_number" class="form-input w-full" placeholder="62xxxxxxxxx"
                                value="{{ old('phone_number') }}" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Password</label>
                            <input type="password" name="password" class="form-input w-full" placeholder="Masukkan password"
                                required>
                            @error('password')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-input w-full"
                                placeholder="Ulangi password" required>
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Role</label>
                            <select name="role_name" class="form-select w-full" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="kesekretariatan"
                                    {{ old('role_name') == 'kesekretariatan' ? 'selected' : '' }}>Kesekretariatan</option>
                                <option value="direktur_utama"
                                    {{ old('role_name') == 'direktur_utama' ? 'selected' : '' }}>
                                    Direktur Utama
                                </option>
                                <option value="direktur_umum" {{ old('role_name') == 'direktur_umum' ? 'selected' : '' }}>
                                    Direktur Umum
                                </option>
                                <option value="user" {{ old('role_name') == 'user' ? 'selected' : '' }}>User</option>
                            </select>
                            @error('role_name')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" class="form-select w-full">
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Aktif</option>
                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- FOTO PROFIL -->
                    <div class="mt-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">Foto Profil</label>
                        <div class="flex items-center gap-4">
                            <img id="previewImage" src="{{ asset('assets/images/default-avatar.png') }}"
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
                        <a href="{{ route('users.index') }}" class="btn btn-outline-danger ltr:mr-3 rtl:ml-3">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>

            <!-- PANEL KANAN -->
            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Tambah User</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 text-sm space-y-2">
                    <li>Pastikan nama dan email sesuai dengan identitas user.</li>
                    <li>Role menentukan hak akses di sistem.</li>
                    <li>Password minimal 8 karakter, disarankan kombinasi huruf dan angka.</li>
                    <li>Foto profil membantu identifikasi user di daftar disposisi.</li>
                </ul>
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 text-center text-sm text-gray-400">
                    <p>Manajemen User v1.0</p>
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

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#positions').select2({
                placeholder: "Pilih satu atau lebih jabatan",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
