<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'nama'              => 'Super Administrator',
            'email'             => 'superadmin@cpns.test',
            'password'          => password_hash('Admin@1234', PASSWORD_BCRYPT),
            'role'              => 'super_admin',
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}
