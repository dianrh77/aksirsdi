<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'positions' => ['Admin Kesekretariatan'],
                'name' => 'Firda Pambudi',
                'role_name' => 'kesekretariatan',
                'email' => 'admin@rsdi.com',
            ],
            [
                'positions' => ['Direktur Utama'],
                'name' => 'dr M Arif Rida M.M.R',
                'role_name' => 'direktur_utama',
                'email' => 'dirutama@rsdi.com',
            ],
            [
                'positions' => ['Direktur Keuangan & Umum'],
                'name' => 'Miftachul Izzah S.E M.Kes',
                'role_name' => 'direktur_umum',
                'email' => 'dirkeuum@rsdi.com',
            ],
            [
                'positions' => ['Manager Keuangan & IT'],
                'name' => 'Liestian A Legowo S.E M.Si',
                'role_name' => 'user',
                'email' => 'mankeu@rsdi.com',
            ],
            [
                'positions' => ['Manager Pelayanan dan Penunjang'],
                'name' => 'dr Princ Aisha A',
                'role_name' => 'user',
                'email' => 'manpelayanan@rsdi.com',
            ],
            [
                'positions' => ['Manager Keperawatan'],
                'name' => 'Mahzunatul Aini S.Kep Ns',
                'role_name' => 'user',
                'email' => 'mankep@rsdi.com',
            ],
            [
                'positions' => ['Manager Umum dan Pemasaran'],
                'name' => 'Suad Laili S.Sos M.M',
                'role_name' => 'user',
                'email' => 'manumum@rsdi.com',
            ],
            [
                'positions' => ['Kasi SDI, AIK & Pengembangan SDI'],
                'name' => 'Diah Ariefiana S.Kom M.M',
                'role_name' => 'kesekretariatan',
                'email' => 'kasisdi@rsdi.com',
            ],
            [
                'positions' => ['Kasi Pelayanan & Penunjang Medik'],
                'name' => 'dr Junaedi',
                'role_name' => 'user',
                'email' => 'kasipelyan@rsdi.com',
            ],
            [
                'positions' => ['Kasi Keperawatan'],
                'name' => 'Rinna Triyana S.Kep,Ns',
                'role_name' => 'user',
                'email' => 'kasikep@rsdi.com',
            ],
            [
                'positions' => ['Kasi Umum & PSRS'],
                'name' => 'Fahri Nuha M R',
                'role_name' => 'user',
                'email' => 'kasiumum@rsdi.com',
            ],
            [
                'positions' => ['Kasi Keuangan & Perbendaharaan'],
                'name' => 'Dhani Renane Tiwi S.E',
                'role_name' => 'user',
                'email' => 'kasikeuangan@rsdi.com',
            ],
        ];

        foreach ($users as $data) {

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role_name' => $data['role_name'],
                'status' => 'Active',
            ]);

            // Attach multi-position
            foreach ($data['positions'] as $posName) {
                $position = Position::where('name', $posName)->first();

                if (!$position) {
                    dump("Position tidak ditemukan: {$posName}");
                    continue;
                }

                $user->positions()->attach($position->id);
            }
        }
    }
}
