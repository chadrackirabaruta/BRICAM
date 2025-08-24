<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;
    public bool $passwordVisible = false;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function togglePasswordVisibility(): void
    {
        $this->passwordVisible = !$this->passwordVisible;
    }
}; ?>

<div> <!-- Single root element wrapper -->
    @livewireStyles
    <div class="min-h-screen bg-gray-5 flex flex-col justify-center py-12 sm:px-6 lg:px-8" >
        <!-- Increased max-width from md (448px) to lg (512px) -->
        <div class="mx-auto w-full max-w-lg"> 
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">BRICAM</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to access your account</p>
            </div>

            <!-- Increased horizontal padding from px-4 to px-6 -->
            <div class="mt-8 bg-white py-8 px-6 shadow sm:rounded-lg">
                <form class="space-y-6" wire:submit.prevent="login">
                    <!-- Email Input - Increased padding -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1 relative">
                            <input 
                                wire:model="form.email"
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150"
                                placeholder="you@example.com"
                            >
                        </div>
                    </div>

                    <!-- Password Input - Increased padding and icon size -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input 
                                wire:model="form.password"
                                id="password"
                                name="password"
                                :type="$passwordVisible ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 pr-12"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button"
                                wire:click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-500 transition duration-150"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <!-- Remember Me - Larger checkbox -->
                        <div class="flex items-center">
                            <input 
                                wire:model="form.remember"
                                id="remember"
                                name="remember"
                                type="checkbox"
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition duration-150"
                            >
                            <label for="remember" class="ml-3 block text-sm text-gray-700">Remember</label>
                        </div>

                        <!-- Forgot Password -->
                        <div class="text-sm">
                            <a 
                                href="{{ route('password.request') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-150"
                                wire:navigate
                            >
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button - Larger -->
                    <div>
                        <button 
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150"
                        >
                            <span wire:loading.remove>Sign in</span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>
                    </div>
                </form>

    
            </div>
        </div>
    </div>
    @livewireScripts
</div>
    @livewireScripts
</div>