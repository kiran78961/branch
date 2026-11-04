<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('login')->with('status', 'Your email has been verified. You can now sign in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email is already verified. You can sign in.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }
}
