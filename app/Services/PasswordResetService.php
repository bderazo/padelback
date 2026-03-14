<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class PasswordResetService
{
    public function sendResetLink($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $token = Str::random(60);
        $resetUrl = 'https://padelfront-3624.web.app/auth/reset-password?token='.$token.'&email='.urlencode($user->email);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => Carbon::now()]
        );

        Mail::to($user->email)->send(new PasswordResetMail($resetUrl));

        return true;
    }
}