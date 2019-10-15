<?php

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Seeder;
use App\Models\PersonalAccessClient;

class OauthClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default user

        $user = User::first();

        Client::insert([
            // Authorization Code grant client

            [
                'id' => '229f9dfd-8a91-4dfa-90db-00ba966ed1ef',
                'user_id' => $user->id,
                'name' => 'dev-auth-code-grant-client',
                'secret' => 'BZQVA2FBPNPhAc0BtqCjndQVSA1TQUJMzJADJrdt',
                'redirect' => '',
                'personal_access_client' => false,
                'password_client' => false,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Password grant client

            [
                'id' => '1bf0b03e-1c62-45e3-bf18-c5989cb43dde',
                'user_id' => $user->id,
                'name' => 'dev-password-grant-client',
                'secret' => 'S8xqNQxus0L4cCJA8lQ4nKLayIQjfc4YOXz9MSWp',
                'redirect' => '',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Personal access token client

        $client = new Client();
        // $client->id = '84031037-7d29-491a-99ac-340ff14e1001';
        $client->user_id = $user->id;
        $client->name = 'dev-personal-client';
        $client->secret = 'ViSqJ6if7ZgUK6ysaBZF61MKb8bYPRIdjOTzK7oT';
        $client->redirect = '';
        $client->personal_access_client = true;
        $client->password_client = false;
        $client->revoked = false;
        $client->save();

        $personal_client = new PersonalAccessClient();
        $personal_client->client()->associate($client);
        $client->save();
    }
}
