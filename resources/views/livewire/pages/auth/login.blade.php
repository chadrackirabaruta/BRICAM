<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 px-4 py-12">
        <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-3xl p-8 sm:p-12 w-full max-w-lg flex flex-col items-center transform transition-transform duration-500 hover:-translate-y-1 hover:shadow-3xl">
            
            <!-- Logo -->
            <div class="mb-6 flex justify-center w-full">
                <img src="{{ asset('img/logo.png') }}" alt="BRICAM" class="h-16 sm:h-20 mx-auto transition-transform duration-300 hover:scale-105">
            </div>

            <!-- Title -->
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 dark:text-gray-100 mb-6">
                Welcome to BRICAM
            </h2>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4 text-center text-sm text-green-600 dark:text-green-400" :status="session('status')" />

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="w-full space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-700 dark:text-gray-200"/>
                    <x-text-input id="email" 
                                  class="mt-1 block w-full px-5 py-4 rounded-xl border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-900 dark:text-gray-100 transition duration-200 text-base" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  required 
                                  autofocus 
                                  autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700 dark:text-gray-200"/>
                    <x-text-input id="password" 
                                  class="mt-1 block w-full px-5 py-4 rounded-xl border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-900 dark:text-gray-100 transition duration-200 text-base" 
                                  type="password" 
                                  name="password" 
                                  required 
                                  autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition duration-200" name="remember">
                    <label for="remember_me" class="ml-2 block text-base text-gray-700 dark:text-gray-300">
                        {{ __('Remember me') }}
                    </label>
                </div>

                <!-- Forgot Password & Submit -->
                <div class="flex flex-col sm:flex-row items-center justify-between mt-6 space-y-3 sm:space-y-0">
                    @if (Route::has('password.request'))
                        <a class="text-base text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 transition-colors duration-200" 
                           href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-xl transition-all duration-300 text-base">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Signup Link -->
            <p class="mt-6 text-center text-base text-gray-500 dark:text-gray-400">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 font-semibold transition-colors duration-200">
                    Sign Up
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
