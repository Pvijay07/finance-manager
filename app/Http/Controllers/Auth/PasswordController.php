<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordController extends Controller
{
    /**
     * Update the authenticated user's password (used in Admin, Manager, CA portals)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated user. Please log in.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password entered is incorrect.');
                    }
                }
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'confirmed',
                'different:current_password'
            ],
            'password_confirmation' => 'required|string'
        ], [
            'password.different' => 'The new password must be different from your current password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min'       => 'The new password must be at least 8 characters long.'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Log the event in ActivityLog without recording secrets
        try {
            ActivityLog::create([
                'user_id'    => $user->id,
                'action'     => 'Changed account password',
                'model_type' => User::class,
                'model_id'   => $user->id,
                'details'    => ['role' => $user->role],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            // Log non-blocking error
            \Log::warning('Failed to record password change activity log: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been changed successfully!'
            ]);
        }

        return back()->with('success', 'Your password has been changed successfully!');
    }

    /**
     * Display the Forgot Password form
     */
    public function showForgotForm(Request $request, $role = 'manager')
    {
        if (!in_array($role, ['admin', 'manager', 'ca'])) {
            $role = 'manager';
        }
        return view('auth.forgot_password', compact('role'));
    }

    /**
     * Send password reset link to user's email
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'role'  => 'nullable|string|in:admin,manager,ca'
        ], [
            'email.exists' => 'We could not find an account registered with that email address.'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($request->filled('role') && $user->role !== $request->role) {
            $errorMsg = 'This account belongs to the ' . ucfirst($user->role) . ' portal. Please use the appropriate portal reset.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => ['email' => [$errorMsg]]
                ], 422);
            }
            return back()->withErrors(['email' => $errorMsg])->withInput();
        }

        // Generate secure 64-char token
        $token = Str::random(64);

        // Store or update token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($email));

        // Attempt sending email
        $mailSent = false;
        try {
            Mail::send('emails.password_reset', [
                'user'     => $user,
                'resetUrl' => $resetUrl,
                'token'    => $token
            ], function ($message) use ($email, $user) {
                $message->to($email, $user->name)
                        ->subject('Password Reset Request - FinCorp Finance Management');
            });
            $mailSent = true;
        } catch (\Exception $e) {
            \Log::error('Password reset email sending failed: ' . $e->getMessage());
        }

        $msg = $mailSent
            ? 'A password reset link has been emailed to you. Please check your inbox.'
            : 'Password reset request generated. Please check your email for the reset instructions.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'resetUrl' => app()->environment('local') ? $resetUrl : null
            ]);
        }

        return back()->with('status', $msg);
    }

    /**
     * Display the Password Reset Form with token
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email', '');
        return view('auth.reset_password', compact('token', 'email'));
    }

    /**
     * Reset the user password with validated token
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'                 => 'required',
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|string|min:8|max:128|confirmed',
            'password_confirmation' => 'required|string'
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min'       => 'The new password must be at least 8 characters long.'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Verify token from database
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            $error = 'Invalid password reset request or token has expired.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return back()->withErrors(['email' => $error])->withInput();
        }

        // Check token expiration (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            $error = 'This password reset link has expired. Please request a new one.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return back()->withErrors(['email' => $error])->withInput();
        }

        // Verify token match (supports both plain and hashed tokens)
        $tokenValid = Hash::check($request->token, $record->token) || $request->token === $record->token;

        if (!$tokenValid) {
            $error = 'Invalid token. Please request a fresh reset link.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return back()->withErrors(['email' => $error])->withInput();
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log the activity
        try {
            ActivityLog::create([
                'user_id'    => $user->id,
                'action'     => 'Reset account password via email link',
                'model_type' => User::class,
                'model_id'   => $user->id,
                'details'    => ['role' => $user->role],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            \Log::warning('Activity log for reset password failed: ' . $e->getMessage());
        }

        $redirectRole = in_array($user->role, ['admin', 'manager', 'ca']) ? $user->role : 'manager';
        $redirectUrl = url('/' . $redirectRole . '/login');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Your password has been successfully reset! You can now sign in with your new password.',
                'redirectUrl' => $redirectUrl
            ]);
        }

        return redirect($redirectUrl)->with('success', 'Your password has been reset successfully! Please sign in.');
    }
}
