<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Process Password Reset') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Request Details -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3">Request Details</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500">User Name</dt>
                                <dd class="text-gray-900">{{ $notification->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Identifier</dt>
                                <dd class="text-gray-900">{{ $notification->user->identifier ?? $notification->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Role</dt>
                                <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($notification->user->role) }}</span></dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Institute</dt>
                                <dd class="text-gray-900">{{ $notification->institute->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Requested At</dt>
                                <dd class="text-gray-900">{{ $notification->created_at->format('M d, Y h:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Status</dt>
                                <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ ucfirst($notification->status) }}</span></dd>
                            </div>
                        </dl>
                    </div>

                    @if($notification->isPending())
                        <!-- Reset Password Form -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">Set New Password</h3>
                            <form method="POST" action="{{ route(request()->user()->isGlobalAdmin() ? 'global-admin.password-resets.execute' : 'principal.password-resets.execute', $notification) }}">
                                @csrf

                                <div class="mb-4">
                                    <x-input-label for="new_password" :value="__('New Password')" />
                                    <x-text-input id="new_password" class="block mt-1 w-full" type="password" name="new_password" required />
                                    <p class="mt-1 text-xs text-gray-500">Must contain uppercase, lowercase, number, and special character. Min 8 characters.</p>
                                    <x-input-error :messages="$errors->get('new_password')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="new_password_confirmation" :value="__('Confirm New Password')" />
                                    <x-text-input id="new_password_confirmation" class="block mt-1 w-full" type="password" name="new_password_confirmation" required />
                                </div>

                                <x-primary-button>
                                    {{ __('Reset Password') }}
                                </x-primary-button>
                            </form>
                        </div>

                        <!-- Deny Form -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-3 text-red-600">Deny Request</h3>
                            <form method="POST" action="{{ route(request()->user()->isGlobalAdmin() ? 'global-admin.password-resets.deny' : 'principal.password-resets.deny', $notification) }}">
                                @csrf

                                <div class="mb-4">
                                    <x-input-label for="notes" :value="__('Reason (Optional)')" />
                                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                        placeholder="Enter reason for denying this request...">{{ old('notes') }}</textarea>
                                </div>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                    {{ __('Deny Request') }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-4 bg-gray-100 rounded-lg text-center text-gray-600">
                            This request has already been {{ $notification->status }}.
                            @if($notification->processedBy)
                                <br><span class="text-sm">Processed by {{ $notification->processedBy->name }} on {{ $notification->processed_at->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
