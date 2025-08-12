<?php
    // app/Http/Controllers/AgentAuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(array_merge($credentials, ['role' => 'agent']))) {
            return redirect()->route('agent.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials or not an agent']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('agent.login');
    }
}
