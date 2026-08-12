<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Details') }} — {{ $user->name }}
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

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Full Name</dt>
                            <dd class="text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Role</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Identifier</dt>
                            <dd class="text-gray-900 font-mono">{{ $user->identifier ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="text-gray-900">{{ $user->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Created At</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Created By</dt>
                            <dd class="text-gray-900">{{ $user->creator->name ?? 'System' }}</dd>
                        </div>
                        @if($user->isTeacher())
                            <div>
                                <dt class="font-medium text-gray-500">Delegated Admin</dt>
                                <dd>
                                    @if($user->is_delegated_admin)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Yes — Active</span>
                                    @else
                                        <span class="text-gray-400">No</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-6 flex space-x-4">
                        @can('update', $user)
                            <a href="{{ route('principal.accounts.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition text-sm">
                                Edit Account
                            </a>
                        @endcan

                        @if($user->isTeacher() && auth()->user()->isPrincipal())
                            <form method="POST" action="{{ route('principal.delegation.toggle', $user) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 {{ $user->is_delegated_admin ? 'bg-red-500 hover:bg-red-600' : 'bg-purple-500 hover:bg-purple-600' }} text-white rounded-md transition text-sm">
                                    {{ $user->is_delegated_admin ? 'Revoke Delegation' : 'Grant Delegation' }}
                                </button>
                            </form>
                        @endif

                        @can('delete', $user)
                            <form method="POST" action="{{ route('principal.accounts.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to deactivate this account?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition text-sm">
                                    Deactivate
                                </button>
                            </form>
                        @endcan
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
