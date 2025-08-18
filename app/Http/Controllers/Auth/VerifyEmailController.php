<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request, $id, $hash)
    {
        $user = User::find($id);
        if (!$user) {
            // Handle user not found
            return redirect(config('app.frontend_url') . '/sign-in?verified=0');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url') . '/sign-in?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect(config('app.frontend_url') . '/sign-in?verified=1');
    }
}
