<?php

namespace Tests\Feature;

use Mockery;


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

    public function testRedirectWithGoogle()
    {
        $provider = 'google';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('redirect')->andReturn(new RedirectResponse('/oauth/callback'));

        $response = $this->oauthProviderController->redirect($provider);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/oauth/callback', $response->getTargetUrl());
    }

    public function testRedirectWithGithub()
    {
        $provider = 'github';

        $socialiteMock = Mockery::mock('overload:' . Socialite::class);
        $socialiteMock->shouldReceive('driver')->with($provider)->andReturn($socialiteMock);
        $socialiteMock->shouldReceive('redirect')->andReturn(new RedirectResponse('/oauth/callback'));

        $response = $this->oauthProviderController->redirect($provider);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/oauth/callback', $response->getTargetUrl());
    }

    public function testRedirectWithUnknownProvider()
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
}