<?php

namespace App\Http\Controllers;

use App\Application\Contracts\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, AuditLogger $audit)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau kata sandi salah, atau akun sedang nonaktif.']);
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();
        $audit->log($user->id, 'login', 'auth', $user->id, 'Masuk ke sistem');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request, AuditLogger $audit)
    {
        $audit->log($this->actorId(), 'logout', 'auth', $this->actorId(), 'Keluar dari sistem');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
