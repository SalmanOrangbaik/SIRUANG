<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();

        \App\Models\User::create([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('qwerty'),
            'isAdmin'  => 1,
        ]);

        \App\Models\User::create([
            'name'     => 'User',
            'email'    => 'user@gmail.com',
            'password' => bcrypt('user'),
            'isAdmin'  => 0,
        ]);
    }
}
