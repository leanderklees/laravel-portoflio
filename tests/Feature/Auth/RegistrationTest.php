<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use App\Mail\UserProfileCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {   
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'challenge_ts' => '2023-03-01T12:34:56Z',
                'hostname' => 'example.com',
            ]),
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'g-recaptcha-response' => 'valid-captcha-response',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_new_users_get_email_notification(): void
    {
        Mail::fake();
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200)
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'g-recaptcha-response' => 'valid-captcha-response',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        Mail::assertSent(UserProfileCreated::class);
    }

}