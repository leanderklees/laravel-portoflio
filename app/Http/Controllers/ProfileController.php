<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Mail\EmailUpdated;
use App\Models\TemporaryFile;

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
        $tmp_file = TemporaryFile::where('folder', $request->input('profile-image'))->first();
        $request->user()->fill($request->validated());  

        if ($request->user()->isDirty('email')) {
            Mail::to($oldEmail)->send(new EmailUpdated());
            $request->user()->email_verified_at = null;
        }

        if (!is_null($tmp_file)){
            $request->user()->profile_image = $this->processProfileImage($tmp_file);
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
            // User has a Oauth Provider and therefore needs an alternative method to confirm
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

    /**
     *  Moves the uploaded profile image to its permanent location
     *  @return (string) new path of the file
     */
    private function processProfileImage(TemporaryFile $tmp_file){
        $tmp_path = 'uploads/profile-image/tmp/'.$tmp_file->folder;
        $new_path = 'uploads/profile-image/'.$tmp_file->folder;

        Storage::disk(env('PUBLIC_DISK_NAME'))->move(
            $tmp_path.'/'.$tmp_file->filename,
            $new_path.'/'.$tmp_file->filename
        );

        Storage::disk(env('PUBLIC_DISK_NAME'))->deleteDirectory($tmp_path);

        $tmp_file->delete();
        
        return $new_path.'/'.$tmp_file->filename;        
    }
}
