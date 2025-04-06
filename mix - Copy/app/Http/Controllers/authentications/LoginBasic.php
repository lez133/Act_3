<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LoginBasic extends Controller
{
  // Show the login page
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  // Redirect to Google for authentication
  public function redirectToGoogle()
  {
    return Socialite::driver('google')->redirect();
  }

  // Handle the Google callback and log the user in
  public function handleGoogleCallback()
  {
    try {
      $googleUser = Socialite::driver('google')->user();

      if (!$googleUser || !$googleUser->getId()) {
        return redirect()->route('auth-login-basic')->with('error', 'Unable to retrieve Google user data.');
      }

      $user = User::firstOrCreate(
        ['google_id' => $googleUser->getId()],
        [
          'name' => $googleUser->getName(),
          'email' => $googleUser->getEmail(),
          'password' => bcrypt(Str::random(16)),
        ]
      );


      Auth::login($user);


      return redirect()->route('dashboard-analytics');
    } catch (\Exception $e) {

      Log::error('Google login error: ' . $e->getMessage());
      return redirect()->route('auth-login-basic')->with('error', 'There was an error during the login process.');
    }
  }
}
