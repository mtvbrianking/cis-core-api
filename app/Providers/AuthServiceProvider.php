<?php

namespace App\Providers;

use App\Models\Token;
use App\Models\Client;
use App\Models\AuthCode;
use Laravel\Passport\Passport;
use App\Models\PersonalAccessClient;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        // ...

        Passport::tokensCan([
            'authenticate-user' => 'Login, logout a user.',
            'confirm-email' => 'Indicate that a user has verified their email.',
            'reset-password' => "Change a user's forgot password.",
            'validate-email' => 'Check if a user account exists for given email.',
        ]);

        // ...

        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);

        // ...

        // Passport::loadKeysFrom('/secret-keys/oauth');

        // ...

        Passport::tokensExpireIn(now()->addDays(15));

        Passport::refreshTokensExpireIn(now()->addDays(30));

        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
