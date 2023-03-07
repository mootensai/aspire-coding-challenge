<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@aspireapp.com',
                'password' => bcrypt('Aspireadmin123!'),
                'is_admin' => true,
                'created_at' => '2023-03-01 00:00:00',
                'updated_at' => '2023-03-01 00:00:00',
            ],
            [
                'name' => 'User 1',
                'email' => 'user1@gmail.com',
                'password' => bcrypt('User1Password!'),
                'is_admin' => false,
                'created_at' => '2023-03-01 00:00:00',
                'updated_at' => '2023-03-01 00:00:00',
            ],
            [
                'name' => 'User 2',
                'email' => 'user2@gmail.com',
                'password' => bcrypt('User2Password!'),
                'is_admin' => false,
                'created_at' => '2023-03-01 00:00:00',
                'updated_at' => '2023-03-01 00:00:00',
            ]
        ];
        User::insert($users);
    }
}
