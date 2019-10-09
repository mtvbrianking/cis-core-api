<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::first();

        $user = new User();
        $user->alias = 'jdoe';
        $user->name = 'John Doe';
        $user->email = 'jdoe@example.com';
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->password = Hash::make('12345678');
        $user->role()->associate($role);
        $user->facility()->associate($role->facility);

        $user->save();
    }
}
