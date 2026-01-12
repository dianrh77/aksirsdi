@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">

        <div class="flex flex-col xl:flex-row gap-6">

            <!-- PANEL KIRI -->
            <div class="panel flex-1 px-6 py-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Jabatan</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Ubah data jabatan di bawah.</p>
                    </div>
                    <img src="{{ asset('assets/images/logo.png') }}" class="w-20" />
                </div>

                <form action="{{ route('positions.update', $position->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Nama Jabatan -->
                    <div class="mb-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">Nama Jabatan</label>
                        <input type="text" name="name" class="form-input w-full"
                            value="{{ old('name', $position->name) }}" required>
                    </div>

                    <!-- Atasan Langsung -->
                    <div class="mb-6">
                        <label class="block mb-1 text-gray-700 dark:text-gray-300">Atasan Langsung</label>
                        <select name="parent_id" class="form-select w-full">
                            <option value="">— Tidak punya atasan —</option>

                            @foreach ($allPositions as $pos)
                                @if ($pos->id !== $position->id)
                                    <option value="{{ $pos->id }}"
                                        {{ $position->parent_id == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('positions.index') }}" class="btn btn-outline-danger ltr:mr-3">Batal</a>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>

            </div>

            <!-- PANEL KANAN -->
            <div class="panel w-full xl:w-96">
                <h4 class="text-lg font-semibold mb-4">Panduan Edit Jabatan</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 text-sm space-y-2">
                    <li>Pastikan nama jabatan sesuai struktur organisasi.</li>
                    <li>Pengubahan nama akan otomatis berlaku untuk semua user yang memakai jabatan ini.</li>
                </ul>
            </div>

        </div>
    </div>
@endsection
