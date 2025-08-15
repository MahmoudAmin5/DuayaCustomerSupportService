<?php
    // app/Http/Controllers/AgentAuthController.php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AgentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.login');
    }

   public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Rate limiting
        $rateLimitKey = 'login-attempt:' . $request->email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()
                ->withErrors(['error' => "Too many login attempts. Please try again in {$seconds} seconds."])
                ->withInput();
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($rateLimitKey, 300);
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput();
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($rateLimitKey, 300);
            return back()
                ->withErrors(['password' => 'Invalid credentials.'])
                ->withInput();
        }

        // Attempt login
        if (Auth::guard('agent')->attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::clear($rateLimitKey);
            $request->session()->regenerate();

            return redirect()->intended(route('agent.dashboard'));
        }

        RateLimiter::hit($rateLimitKey, 300);
        return back()
            ->withErrors(['error' => 'Authentication failed. Please try again.'])
            ->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('agent.login');
    }
}
