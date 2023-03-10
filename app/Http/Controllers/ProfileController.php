<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Mail\EmailUpdated;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $oldEmail = $request->user()->email;
        $request->user()->fill($request->validated());
        if ($request->user()->isDirty('email')) {
            Mail::to($oldEmail)->send(new EmailUpdated());
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', trans('messages.profile-updated'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if(!Auth::User()->password){
            // User has a Oauth Provider and therefore needs an alternative method
            $confirmed = 'Delete:'.Auth::User()->name;
            $request->validateWithBag('userDeletion', [
                'confirm-delete' => [
                    'required',
                    function ($attribute, $value, $fail) use ($confirmed)  {
                        if ($value !== $confirmed) {
                            $fail(__('Please type :confirmed to delete the account', ['confirmed' => $confirmed]));
                        }
                    },
                ],
            ]);
        } else {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current-password'],
            ]);
        }

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
