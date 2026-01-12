<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('positions')->insert([
            [
                'id' => 1,
                'name' => 'Direktur Utama',
                'parent_id' => null,
            ],
            [
                'id' => 2,
                'name' => 'Direktur Pelayanan',
                'parent_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Direktur Keuangan & Umum',
                'parent_id' => 1,
            ],

            // === Pelayanan ===
            [
                'id' => 4,
                'name' => 'Manager Pelayanan dan Penunjang',
                'parent_id' => 2, // ke Direktur Pelayanan
            ],
            [
                'id' => 5,
                'name' => 'Kasi Pelayanan & Penunjang Medik',
                'parent_id' => 4,
            ],

            // === Keperawatan ===
            [
                'id' => 6,
                'name' => 'Manager Keperawatan',
                'parent_id' => 2, // ke Direktur Pelayanan
            ],
            [
                'id' => 7,
                'name' => 'Kasi Keperawatan',
                'parent_id' => 6,
            ],

            // === SDI & AIK ===
            [
                'id' => 8,
                'name' => 'Manajer SDI, AIK & Pengembangan SDI',
                'parent_id' => 3, // ikut Direktur Keuangan & Umum
            ],
            [
                'id' => 9,
                'name' => 'Kasi SDI, AIK & Pengembangan SDI',
                'parent_id' => 8,
            ],

            // === Keuangan ===
            [
                'id' => 10,
                'name' => 'Manager Keuangan & IT',
                'parent_id' => 3,
            ],
            [
                'id' => 11,
                'name' => 'Kasi Keuangan & Perbendaharaan',
                'parent_id' => 10,
            ],

            // === Umum ===
            [
                'id' => 12,
                'name' => 'Manager Umum dan Pemasaran',
                'parent_id' => 3,
            ],
            [
                'id' => 13,
                'name' => 'Kasi Umum & PSRS',
                'parent_id' => 12,
            ],

            // === Admin (kedudukan khusus) ===
            [
                'id' => 14,
                'name' => 'Admin Kesekretariatan',
                'parent_id' => null, // tidak dalam rantai atasan
            ],
        ]);
    }
}
