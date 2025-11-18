<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class AuthController extends Controller
{
    public function showRegister($username = null)
    {
        $sponsor = $username ? User::where('username', $username)->first() : null;

        return view('auth.register', compact('sponsor'));
        
    }
}
