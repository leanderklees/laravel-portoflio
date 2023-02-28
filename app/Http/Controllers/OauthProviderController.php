<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class OauthProviderController extends Controller
{
    protected $OauthProviders = [
        'google',
        'github',
    ];

    public function redirect(string $provider){
        $this->validateProvider($provider);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider){
        $this->validateProvider($provider);
        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $user = User::updateOrCreate([
                'email' => $socialiteUser->email,
            ], [
                'provider_id' => $socialiteUser->id,
                'provider' => $provider,
                'name' => $socialiteUser->name,
                'provider_token' => $socialiteUser->token,
                'email_verified_at' => now(),
            ]);

            Auth::login($user);

            return redirect('/dashboard');

        } catch (\Socialite\Two\InvalidStateException $e) {
            return redirect('/')->with('error', 'Invalid state. Please try again.');
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Unable to retrieve user details from provider.');
        }
    }

    private function validateProvider($provider){
        if (!in_array($provider, $this->OauthProviders)) {
            throw new InvalidArgumentException("Invalid provider: {$provider}");
        }
    }
}