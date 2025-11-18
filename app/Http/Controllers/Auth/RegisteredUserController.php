<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Models\Referral;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

         $sponsor = $request->ref ? User::where('username', $request->ref)->first() : null;

        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'referred_by' => $sponsor?->id,
            'referral_code' => Str::random(16),
        ]);

        $user->wallets()->createMany([
            ['type' => 'deposit', 'balance' => 0.00],
            ['type' => 'referral', 'balance' => 0.00], 
            ['type' => 'investment', 'balance' => 0.00],
        ]);

        event(new Registered($user));

        if ($sponsor) {
            $this->buildReferralChain($sponsor, $user);
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function buildReferralChain($sponsor, $user, $level = 1)
    {
        if ($level > 4 || !$sponsor) return;

        Referral::create([
            'sponsor_id' => $sponsor->id,
            'user_id' => $user->id,
            'level' => $level,
        ]);

        $this->buildReferralChain($sponsor->sponsor, $user, $level + 1);
    }
}
