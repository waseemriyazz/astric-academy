<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        Log::info('User logged in', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
            'role' => $request->user()->role,
            'ip' => $request->ip(),
        ]);

        if ($request->user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        // It's a student
        return redirect()->intended(route('student.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email;

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Log::info('User logged out', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip' => $request->ip(),
        ]);

        return redirect('/');
    }
}
