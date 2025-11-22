<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Referral;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ReferralRegisterController extends Controller
{
    public function show($username)
    {
        $sponsorUser = User::where('username', $username)->select(['username', 'name'])->firstOrFail();
        return view('auth.register', compact('sponsorUser'));
    }


}
