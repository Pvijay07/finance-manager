<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm($role = 'manager')
    {
        if (!in_array($role, ['admin', 'manager', 'ca'])) {
            abort(404);
        }
        return view('auth.login', compact('role'));
    }

    public function login(Request $request, $role = 'manager')
    {
        if (!in_array($role, ['admin', 'manager', 'ca'])) {
            abort(404);
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            $this->logLoginAttempt($request, 'user_not_found');
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Enforce role-based portal access
        if ($user->role !== $role) {
            $this->logLoginAttempt($request, 'wrong_portal');
            throw ValidationException::withMessages([
                'email' => __('You are not authorized to login to the ' . ucfirst($role) . ' portal. Please use the correct portal.'),
            ]);
        }

        // Check if user is active (if you have status field)
        if (property_exists($user, 'status') && $user->status !== 'active') {
            $this->logLoginAttempt($request, 'account_inactive');
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact administrator.'),
            ]);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful login
            $this->logLoginAttempt($request, 'success', $user);
            // dd($user);
            // Redirect based on role
            return $this->authenticated($request, $user);
        }

        // Log failed attempt
        $this->logLoginAttempt($request, 'invalid_credentials', $user);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // Redirect based on user role
        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
            case 'manager':
                return redirect()->intended('/manager/dashboard');
            case 'ca':
                return redirect()->intended('/ca/dashboard');
            case 'user':
                return redirect()->intended('/ca/dashboard');
            default:
                return redirect()->intended('/dashboard');
        }
    }

    public function logout(Request $request)
    {
        $role = null;
        if (Auth::check()) {
            $role = Auth::user()->role;
            ActivityLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'Logged out',
                'model_type' => User::class,
                'model_id'   => Auth::id(),
                'details'    => [],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirectPath = $role ? '/' . $role . '/login' : '/manager/login';
        return redirect($redirectPath);
    }

    protected function logLoginAttempt(Request $request, $status, $user = null)
    {
        // Log login attempts to laravel.log
        \Log::info('Login attempt', [
            'email' => $request->email,
            'status' => $status,
            'user_id' => $user ? $user->id : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        // Explicitly record it in our ActivityLog model
        ActivityLog::create([
            'user_id'    => $user ? $user->id : 1, // Fallback to 1 if missing for failed attempts
            'action'     => $status === 'success' ? 'Login' : 'Failed Login (' . $status . ')',
            'model_type' => User::class,
            'model_id'   => $user ? $user->id : 0,
            'details'    => ['email' => $request->email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}
