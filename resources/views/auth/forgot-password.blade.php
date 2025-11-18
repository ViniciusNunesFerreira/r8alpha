<x-guest-layout>

    <div class="card animate-scale-in">

        <div class="card-body space-y-6">

            <div class="text-center">
                <h2 class="card-title justify-center">{{__('Forgot your password?')}}</h2>
                <p class="text-sm text-gray-400">{{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
            </div>



            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')"  class="input-label"/>
                    <div class="input-group">
                        <span class="input-icon-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>                    
                        <x-text-input id="email" class="pl-10" type="email" name="email" :value="old('email')" required autofocus />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>


                <div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                       {{ __('Email Password Reset Link') }}
                    </button>
                </div>
            </form>
        </div>

    </div>

</x-guest-layout>
