<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class OtpController extends Controller
{
    /**
     * Menampilkan Halaman Login
     */
    public function showLogin(): Response
    {
        return Inertia::render('auth/OtpLogin');
    }

    /**
     * Buat kode otp
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->email;
        $otpCode = (string) rand(100000, 999999);

        Cache::put('otp_code_'.$email, Hash::make($otpCode), now()->addMinutes(5));
        session(['otp_email' => $email]);
        session()->save();

        Mail::to($email)->send(new SendOtpMail($otpCode));

        return redirect()->route('otp.verify');
    }

    /**
     * Menampilkan halaman verify
     *
     * @return Response
     */
    public function showVerify(): Response|RedirectResponse
    {
        $email = session('otp_email');

        if (! $email || ! Cache::has('otp_code_'.$email)) {
            Log::error('OTP Verify Failed', [
                'session_email' => $email,
                'cache_exists' => $email ? Cache::has('otp_code_'.$email) : false,
            ]);

            return redirect()->route('otp.login')->withErrors(['email' => 'The OTP session is invalid or has expired. Please request a new code.']);
        }

        return Inertia::render('auth/OtpVerify', [
            'email' => $email,
        ]);
    }

    /**
     * verifikasi kode OTP
     */
    public function verify(Request $request): RedirectResponse
    {
        $email = session('otp_email');

        if (! $email || ! Cache::has('otp_code_'.$email)) {
            return redirect()->route('otp.login')->withErrors(['email' => 'The OTP session is invalid or has expired. Please request a new code.']);
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $cachedOtp = Cache::get('otp_code_'.$email);
        $otp = $request->otp;

        if (!($cachedOtp && Hash::check($otp, $cachedOtp))) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // Login user
        $user = User::where('email', $email)->firstOrFail();
        Auth::Login($user);

        session()->forget('otp_email');
        Cache::forget('otp_code_'.$email);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
