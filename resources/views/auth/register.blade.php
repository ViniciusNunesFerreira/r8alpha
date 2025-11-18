<x-guest-layout>
    @section('title', 'Create Account')

    <div class="card animate-scale-in" x-data="{ showPassword: false, showConfirmPassword: false }">
        <div class="card-body space-y-5">
            
            <div class="text-center">
                <h2 class="card-title justify-center">{{__('Create your Account')}}</h2>
                <p class="text-sm text-gray-400">{{ __('Join the crypto revolution with R8-Alpha.') }}</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                @if ($errors->any())
                    <div class="alert-danger" role="alert">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-semibold">{{__('An error occurred.')}}</p>
                            <ul class="list-disc list-inside text-sm mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if (isset($sponsorUser) && optional($sponsorUser)->username)
                    <div class="glass-effect rounded-lg p-4 border border-primary-500/30">
                        <label class="input-label mb-2">{{ __('You were sponsor by:') }}</label>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($sponsorUser->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ $sponsorUser->name }}</p>
                                <p class="text-sm text-gray-400">({!! __('@').$sponsorUser->username !!})</p>
                            </div>
                        </div>
                        <input type="hidden" name="ref" value="{{ $sponsorUser->username }}">
                    </div>
                @endif
                
                <div>
                    <label for="name" class="input-label">{{ __('Full Name') }}</label>
                    <input type="text" id="name" name="name" class="input-crypto" placeholder="{{ __('Your Name') }}" value="{{ old('name') }}" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="email" class="input-label">{{__('Email')}}</label>
                    <input type="email" id="email" name="email" class="input-crypto" placeholder="your@email.com" value="{{ old('email') }}" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="username" class="input-label">{{ __('Username (for login)') }}</label>
                    <input type="text" id="username" name="username" class="input-crypto" placeholder="{{__('your.unique.username')}}" value="{{ old('username') }}" required autocomplete="username">
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="input-label">{{__('Password')}}</label>
                    <div class="input-group">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="input-crypto pr-10" placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" @click="showPassword = !showPassword" class="input-icon-right text-gray-400 hover:text-primary-400">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .94-3.006 3.488-5.32 6.54-6.325m5.4 5.4a3 3 0 11-4.242-4.242M1 1l22 22"></path></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label for="password_confirmation" class="input-label">{{__('Confirm Password')}}</label>
                    <div class="input-group">
                        <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" class="input-crypto pr-10" placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="input-icon-right text-gray-400 hover:text-primary-400">
                            <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .94-3.006 3.488-5.32 6.54-6.325m5.4 5.4a3 3 0 11-4.242-4.242M1 1l22 22"></path></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        {{__('Create Account')}}
                    </button>
                </div>
            </form>

            <div class="divider"></div>

            <p class="text-center text-sm text-gray-400">
                 {{ __('Already registered?') }}
                <a href="{{ route('login') }}" class="font-semibold text-primary-400 hover:text-primary-300 hover:underline transition-colors">
                    {{ __('Login here')}}
                </a>
            </p>

        </div>
    </div>
</x-guest-layout>
