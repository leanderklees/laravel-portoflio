<?php

namespace Tests\Feature;

use Mockery;

use App\Models\User;
use Tests\TestCase;
use App\Http\Controllers\OauthProviderController;
use \InvalidArgumentException;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class OauthVerificationTest extends TestCase
{
    private $oauthProviderController;

    public function setUp(): void
    {
        parent::setUp();

        $this->oauthProviderController = new OauthProviderController;
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function test_redirect_with_google()
    {
        $provider = 'google';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('redirect')->andReturn(new RedirectResponse('/oauth/callback'));

        $response = $this->oauthProviderController->redirect($provider);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/oauth/callback', $response->getTargetUrl());
    }

    public function test_redirect_with_github()
    {
        $provider = 'github';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('redirect')->andReturn(new RedirectResponse('/oauth/callback'));

        $response = $this->oauthProviderController->redirect($provider);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/oauth/callback', $response->getTargetUrl());
    }

    public function test_redirect_with_unknown_provider()
    {
        $provider = 'unknownprovider';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('redirect')->andReturn(new RedirectResponse('/oauth/callback'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid provider: {$provider}");

        $controller = new OauthProviderController();
        $controller->redirect($provider);
    }

    
    public function test_login_with_google()
    {

        $provider = 'google';

        $user = Mockery::mock('Laravel\Socialite\Two\User');
        $user->id = 123;
        $user->email = 'john@example.com';
        $user->name = 'John Doe';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('user')->andReturn($user);

        $response = $this->oauthProviderController->callback($provider);

        $this->assertAuthenticated();
        $this->assertEquals($user->email, auth()->user()->email);
        $this->assertEquals($user->name, auth()->user()->name);
    }

    public function test_user_can_update_email_after_oauth_login_1()
    {
        // 1. Log in using oauth
        $provider = 'google';
        // $provider = 'google';

        $user = Mockery::mock('Laravel\Socialite\Two\User');
        $user->id = 123;
        $user->email = 'john@example.com';
        $user->name = 'John Doe';
        $user->provider = $provider;
        // $user->provider_id = '123456789';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('user')->andReturn($user);

        $response = $this->oauthProviderController->callback($provider);

        // 2. Check if user is authenticated
        $this->assertAuthenticated();
        $this->assertEquals($user->email, auth()->user()->email);
        $this->assertEquals($user->name, auth()->user()->name);

        // 3. Navigate to /profile page and update email
        $newEmail = 'newemail@example.com';
        $user->email = $newEmail;
     
        // 4. Log out
        $this->post('/logout');

        // 5. Log in using oauth
        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('user')->andReturn($user);
        $response = $this->oauthProviderController->callback($provider);

        // 6. Check if user is authenticated
        $this->assertAuthenticated();

        // 7. Check if email is still the same
        $this->assertEquals($newEmail, $user->email);
    }
}
