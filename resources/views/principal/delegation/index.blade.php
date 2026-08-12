<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Delegation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-6 p-4 bg-blue-50 rounded-lg text-sm text-blue-800">
                        <strong>What is Admin Delegation?</strong>
                        <p class="mt-1">When you delegate admin rights to a teacher, they can create and manage student and teacher accounts on your behalf within this institute.</p>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Identifier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delegation Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($teachers as $teacher)
                                <tr class="{{ $teacher->is_delegated_admin ? 'bg-purple-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teacher->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $teacher->identifier }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $teacher->email ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($teacher->is_delegated_admin)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                ✓ Delegated
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                Not Delegated
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form method="POST" action="{{ route('principal.delegation.toggle', $teacher) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium transition
                                                {{ $teacher->is_delegated_admin ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-purple-100 text-purple-700 hover:bg-purple-200' }}">
                                                {{ $teacher->is_delegated_admin ? 'Revoke' : 'Grant' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No teachers found in this institute.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $teachers->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
