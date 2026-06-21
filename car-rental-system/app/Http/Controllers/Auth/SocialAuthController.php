<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Social auth InvalidStateException: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['social' => 'Session expired. Please try again.']);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Social auth ClientException: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['social' => 'Could not connect to Google. Please try again.']);

        } catch (\Exception $e) {
            Log::error('Social auth Exception: ' . get_class($e) . ' — ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['social' => 'Authentication failed: ' . $e->getMessage()]);
        }

        if (!$socialUser->getEmail()) {
            return redirect()->route('login')
                ->withErrors(['social' => 'Could not get email from Google. Please try again.']);
        }

        // Find by social ID first, then by email
        $user = User::where('social_provider', $provider)
            ->where('social_id', $socialUser->getId())
            ->first();

        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                    'avatar' => $user->avatar ?? $socialUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(32)),
                    'role' => 'customer',
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            abort(404, "Social provider [{$provider}] is not supported.");
        }
    }
}