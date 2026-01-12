@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div class="panel border-[#e0e6ed] px-6 py-5 dark:border-[#1b2e4b]">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-5">
                Tambah Disposisi Induk
            </h2>

            <form action="{{ route('disposisi.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Nomor Disposisi -->
                <div>
                    <label class="block text-sm font-medium mb-1">Nomor Disposisi</label>
                    <input type="text" id="no_disposisi" name="no_disposisi" class="form-input w-full bg-gray-100" readonly>
                </div>

                <!-- Surat Terkait -->
                <!-- Surat Terkait + Pengirim -->
                <div x-data="suratDetail()" x-init="init()">

                    <label class="block text-sm font-medium mb-1">Surat Terkait</label>
                    <select name="surat_id" class="form-select w-full" x-model="selectedId" @change="fetchSurat()" required>
                        <option value="">-- Pilih Surat --</option>
                        @foreach ($suratMasuk as $s)
                            <option value="{{ $s->id }}">{{ $s->no_surat }}</option>
                        @endforeach
                    </select>

                    <!-- Detail Surat -->
                    <template x-if="surat">
                        <div
                            class="p-4 mt-3 rounded-lg border bg-gray-50 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <p><strong>📄 Nomor Surat:</strong> <span x-text="surat.no_surat"></span></p>
                            <p><strong>📝 Perihal:</strong> <span x-text="surat.perihal"></span></p>
                            <p><strong>🏢 Asal Surat:</strong> <span x-text="surat.asal_surat"></span></p>
                            <p><strong>📅 Tanggal Surat:</strong> <span x-text="formatDate(surat.tgl_surat)"></span></p>
                        </div>
                    </template>

                    <!-- Pengirim -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Pengirim</label>
                        <input type="text" class="form-input w-full bg-gray-100" :value="surat?.pembuat_nama ?? '-'"
                            readonly>
                        <input type="hidden" name="pengirim_id" :value="surat?.created_by ?? ''">
                    </div>

                </div>



                <div>
                    <label class="block text-sm font-medium mb-1" for="jenis_disposisi">Jenis Disposisi</label>
                    <select name="jenis_disposisi" id="jenis_disposisi" class="form-select w-full" required>
                        <option value="biasa">Biasa</option>
                        <option value="penting">Penting</option>
                        <option value="rahasia">Rahasia</option>
                    </select>
                </div>


                <!-- Catatan -->
                <div>
                    <label class="block text-sm font-medium mb-1">Catatan / Ringkasan</label>
                    <textarea name="catatan" rows="3" class="form-textarea w-full" required></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('disposisi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('{{ route('disposisi.generateNo') }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('no_disposisi').value = data.no_disposisi;
                })
                .catch(error => console.error('Error generate nomor:', error));
        });
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('suratDetail', () => ({
                selectedId: '{{ $selectedSuratId ?? '' }}',
                surat: @json($selectedSurat ?? null),

                init() {
                    if (this.selectedId && !this.surat) this.fetchSurat();
                },

                async fetchSurat() {
                    if (!this.selectedId) {
                        this.surat = null;
                        return;
                    }

                    try {
                        const res = await fetch(`/disposisi/surat/${this.selectedId}/detail`);
                        if (!res.ok) throw new Error('Gagal memuat data');
                        this.surat = await res.json();
                    } catch (e) {
                        console.error(e);
                        this.surat = null;
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                }
            }));
        });
    </script>
@endsection
