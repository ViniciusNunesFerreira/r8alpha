<x-guest-layout>
    @section('title', 'Secure Login')

    <div class="card animate-scale-in" x-data="{ showPassword: false }">
        <div class="card-body space-y-6">
            
            <div class="text-center">
                <h2 class="card-title justify-center">{{__('Welcome back')}}</h2>
                <p class="text-sm text-gray-400">{{__('Log in to access your R8-Alpha account.')}}</p>
            </div>

             <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="alert-danger" role="alert">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-semibold">Ocorreu um erro</p>
                            <ul class="list-disc list-inside text-sm mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="username" class="input-label">{{__('Username or Email')}}</label>
                    <div class="input-group">
                        <span class="input-icon-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        <input type="text" id="username" name="username" class="input-crypto pl-10" placeholder="{{__('username or email')}}" value="{{ old('username') }}" required autofocus autocomplete="username">
                        
                    </div>
                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="input-label">{{__('Password')}}</label>
                    <div class="input-group">
                        <span class="input-icon-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="input-crypto pl-10 pr-10" placeholder="" required>
                        <button type="button" @click="showPassword = !showPassword" class="input-icon-right text-gray-400 hover:text-primary-400">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .94-3.006 3.488-5.32 6.54-6.325m5.4 5.4a3 3 0 11-4.242-4.242M1 1l22 22"></path></svg>
                        </button>
                    </div>

                     <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">


                    <label for="remember" class="flex items-center gap-2 cursor-pointer" 
                        x-data="{ isRemembered: {{ old('remember', false) ? 'true' : 'false' }} }"
                    >
                        <div class="toggle-switch">
                           
                            <input type="checkbox" id="remember" name="remember" class="sr-only" 
                                x-model="isRemembered" 
                            >
                            <div class="toggle-slider">
                                <div class="toggle-thumb" :class="{'translate-x-full': isRemembered}">
                                    
                                </div>
                            </div>
                        </div>
                        <span class="text-sm text-gray-400 select-none">{{ __('Remember me') }}</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-sm text-primary-400 hover:text-primary-300 hover:underline transition-colors">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>

                <div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                       {{__('Access Platform')}}
                    </button>
                </div>
            </form>

            <div class="divider"></div>

            <p class="text-center text-sm text-gray-400">
                {{__("Don't have an account?")}}
                <a href="{{ route('register') }}" class="font-semibold text-primary-400 hover:text-primary-300 hover:underline transition-colors">
                    {{__('Create account now')}}
                </a>
            </p>

        </div>
    </div>
</x-guest-layout>
