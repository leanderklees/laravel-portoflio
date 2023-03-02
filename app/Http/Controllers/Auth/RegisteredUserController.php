<?php

namespace App\Http\Controllers\Auth;

use Closure;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Mail\UserProfileCreated;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 
                Rule::unique(User::class),
                function ($attribute, $value, $fail) {
                    $existingUser = User::where('email', $value)
                        ->whereNotNull('provider')
                        ->first();
                    if ($existingUser) {
                        $fail("Please try logging in with " . ucfirst($existingUser->provider) . ".");
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => ['required', 
                function (string $attribute, mixed $value, Closure $fail) {
                    $g_response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => config('services.recaptcha.secret_key'),
                        'response' => $value,
                    ]);
                    if (!$g_response->json('success')) {
                        $fail("The {$attribute} is invalid.");
                    }
                },
            ],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));


        Auth::login($user);
        $test = new UserProfileCreated();
        $authy = Auth::User();
        Mail::to($authy)->send($test);

        return redirect(RouteServiceProvider::HOME);
    }
}
