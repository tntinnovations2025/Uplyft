<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Staff / Student Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('principal.accounts.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Full Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <x-input-label for="role" :value="__('Account Type')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">-- Select Role --</option>
                                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Identifier (Roll Number / Employee ID) -->
                        <div class="mb-4">
                            <x-input-label for="identifier" :value="__('Roll Number / Employee ID')" />
                            <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" required
                                placeholder="e.g. STU-2026/0101, EMP#402, FAC-MATH-01" />
                            <p class="mt-1 text-xs text-gray-500">Letters, numbers, hyphens, slashes, and hashes allowed. 3–50 characters.</p>
                            <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
                        </div>

                        <!-- Email (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email Address (Optional)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                            <p class="mt-1 text-xs text-gray-500">Must contain uppercase, lowercase, number, and special character. Min 8 characters.</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('principal.accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Account') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
