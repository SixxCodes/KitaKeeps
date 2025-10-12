@php
    $userBranches = Auth::user()->branches;

    // The first registered hardware (main branch) = lowest ID
    $mainBranch = $userBranches->sortBy('branch_id')->first();

    // Current active branch from session (fallback to first branch if not set)
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
        ?? $mainBranch;
@endphp

@php
    $currentBranch = Auth::user()->branches->where('branch_id', session('current_branch_id'))->first()
        ?? Auth::user()->branches->first(); // fallback if no session
@endphp

<!-- Module Header -->
<div class="flex items-center justify-between">
    
    <div class="flex flex-col mr-5">
        <div class="flex items-center space-x-2">
            <h2 class="text-black sm:text-sm md:text-sm lg:text-lg">
                {{ $currentBranch->branch_name ?? 'No Branch' }}
            </h2>
            
            <!-- Caret Button to Open Modal -->
            <button x-on:click="$dispatch('open-modal', 'switch-branch')" 
                    class="px-1 text-xs text-gray-800 border border-gray-300 rounded hover:text-black hover:border hover:shadow-sm sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-caret-down"></i> Switch Branch
            </button>
        </div>

        <span class="text-[10px] text-gray-600 sm:text-[10px] md:text-[10px] lg:text-xs">
            {{ $currentBranch->branch_id == $mainBranch->branch_id ? 'Main Branch' : 'Branch' }} • 
            {{ $currentBranch->location ?? '' }}
        </span>
    </div>
    
    <div class="flex space-x-3">
        <!-- Export -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'export-options')" class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-download"></i>
                <span class="hidden ml-2 lg:inline">Export</span>
            </button>
        </div>

        <!-- Add Employee -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'add-branch')"  class="flex items-center px-5 py-2 text-xs text-white transition-colors bg-blue-600 rounded-md shadow hover:bg-blue-800 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-warehouse"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Add Branch</span>
            </button>
        </div>
    </div>
</div>

<!-- Switch Branch Modal -->
<x-modal name="switch-branch" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Header -->
        <h2 class="mb-4 text-lg font-semibold text-center text-gray-800">
            Switch Branch
        </h2>

        <!-- Branch List -->
        <div class="space-y-2">
            @foreach(Auth::user()->branches as $branch)
                <form method="POST" action="{{ route('branches.switch', $branch->branch_id) }}">
                    @csrf
                    <button type="submit" 
                        class="flex items-center justify-between w-full px-4 py-2 text-sm text-left transition border rounded-lg hover:bg-blue-100">
                        <div>
                            <span class="font-semibold">{{ $branch->branch_name }}</span>
                            <span class="text-xs text-gray-500"> • {{ $branch->location }}</span>
                        </div>

                        <!-- Check if current -->
                        @if(session('current_branch_id') == $branch->branch_id)
                            <i class="text-green-600 fa-solid fa-check"></i>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>

        <!-- Cancel -->
        <div class="flex justify-end mt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'switch-branch')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </div>
</x-modal>

<!-- Export Modal -->
<x-modal name="export-options" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <h2 class="text-lg font-semibold text-gray-800">Export As</h2>

        <form method="GET" action="{{ route('branches.export') }}" class="space-y-2">
            <!-- Export Button -->
            <button 
                type="submit"
                class="flex flex-col items-center w-24 px-4 py-3 mx-auto transition bg-green-100 rounded-lg shadow hover:bg-green-200"
            >
                <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                <span class="text-sm text-gray-700">Excel</span>
            </button>

            <!-- Upload to Cloud Option -->
            <label class="flex items-center justify-center text-xs text-gray-600">
                <input 
                    type="checkbox" 
                    name="cloud_sync" 
                    value="1"
                    class="mr-2 text-green-600 border-gray-300 rounded focus:ring-green-400"
                >
                Sync to Cloud
            </label>

            <!-- Subtext -->
            <p class="text-[11px] text-gray-400 leading-tight">
                If unchecked, the file will only be downloaded locally.
            </p>
        </form>

        <!-- Cancel -->
        <div class="flex justify-center">
            <button 
                x-on:click="$dispatch('close-modal', 'export-options')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>

<!-- Add Branch -->
<x-modal name="add-branch" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-code-branch"></i>
            <h2 class="text-xl font-semibold">Add New Branch</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-branch')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('branches.store') }}" class="space-y-4 text-sm">
            @csrf
            <!-- Branch Info -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Branch Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Branch Name -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Branch Name</label>
                        <input type="text" name="branch_name" placeholder="Davao City Branch" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                            required/>
                    </div>

                    <!-- Location -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Location</label>
                        <input type="text" name="location" placeholder="123 Main St, Davao City" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                            required/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                    x-on:click="$dispatch('close-modal', 'add-branch')"
                    class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>

                <button type="submit" 
                    class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Save</button>
            </div>
        </form>

    </div>
</x-modal>










<!-- All Branches -->
<h3 class="mt-8 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">All Branches</h3>

<div class="p-4 mt-3 bg-white rounded-lg shadow">
    <!-- Search + Entries -->
    <div class="flex items-center justify-between mb-4 whitespace-nowrap">
        <div>
            <label class="mr-2 text-sm text-ellipsis sm:text-base">Show</label>
            <select onchange="window.location.href='?per_page='+this.value" class="py-1 text-sm border rounded">
                <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
            </select>
            <span class="ml-2 text-sm">entries</span>
        </div>

        <!-- Search Bar --> 
        <div class="flex items-center space-x-2">
            <div class="flex items-center px-2 py-1 border rounded w-25 sm:px-5 sm:py-1 md:px-3 md:py-2 sm:w-50 md:w-52">
                <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search..."
                    onkeydown="if(event.key==='Enter'){ window.location.href='?per_page={{ request('per_page',5) }}&search='+this.value; }"
                    class="w-full py-0 text-sm bg-transparent border-none outline-none sm:py-0 md:py-1"
                />
            </div>
        </div>
    </div>

    <div class="overflow-x-auto table-pretty-scrollbar">
        <!-- Table -->
        <table class="min-w-full text-sm border">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 text-left border">#</th>
                    <th class="px-3 py-2 text-left border">ID</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Branch Name</th>
                    <th class="px-3 py-2 text-left border">Location</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <!-- Employee Rows -->
                <tr class="hover:bg-gray-50">
                    <!-- Count -->
                    <!-- <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td> -->
                    <td class="px-3 py-2 border bg-blue-50">
                        {{ $branches->firstItem() + $loop->index }}
                    </td>

                    <!-- ID -->
                    <td class="px-3 py-2 border">{{ $branch->branch_id }}</td>

                    <!-- Branch Name -->
                    <td class="px-3 py-2 border">
                        <div class="flex items-center gap-2">
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">{{ $branch->branch_name }}</span>
                        </div>
                    </td>

                    <!-- Location -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        {{ $branch->location }}
                    </td>

                    <!-- Actions -->
                    <td class="flex justify-center gap-2 px-3 py-3 border">
                        <!-- View btn -->
                        <button 
                            x-on:click="$dispatch('open-modal', 'view-branch-{{ $branch->branch_id }}')"  
                            class="px-2 py-1 text-white bg-blue-500 rounded"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- Edit btn -->
                        <button 
                            x-on:click="$dispatch('open-modal', 'edit-branch-{{ $branch->branch_id }}')"  
                            class="px-2 py-1 text-white bg-green-500 rounded"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        
                        <!-- Delete btn (only if not main branch) -->
                        @if($branch->branch_id != $mainBranch->branch_id)
                        <button 
                            x-on:click="$dispatch('open-modal', 'delete-branch-{{ $branch->branch_id }}')" 
                            class="px-2 py-1 text-white bg-red-500 rounded"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-center text-gray-500 border">Nothing to see here yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
        <p class="text-sm">
            Showing {{ $branches->firstItem() ?? 0 }} to {{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }} entries
        </p>
        <!-- Previous / Next -->
        <div class="flex gap-2">
            <!-- Previous button -->
            <a 
                href="{{ $branches->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $branches->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>

            <!-- Next button -->
            <a 
                href="{{ $branches->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $branches->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

@foreach($branches as $branch)
<!-- View Branch Details Modal -->
<x-modal name="view-branch-{{ $branch->branch_id }}" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Profile Section -->
        <div class="flex items-center space-x-4">
            <!-- Branch Icon -->
            <div class="flex items-center justify-center w-20 h-20 text-3xl text-white bg-blue-600 rounded-full">
                <i class="fa-solid fa-code-branch"></i>
            </div>

            <!-- Branch Name -->
            <div>
                <p class="text-lg font-semibold text-gray-800">{{ $branch->branch_name }}</p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t"></div>

        <!-- Branch Details -->
        <div class="space-y-2 text-sm text-gray-700">
            <p><span class="font-medium">Location:</span> {{ $branch->location }}</p>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end pt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'view-branch-{{ $branch->branch_id }}')"
                class="px-4 py-2 text-white transition bg-gray-500 rounded hover:bg-gray-600"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>
@endforeach

@foreach($branches as $branch)
<!-- Edit Branch Details Modal -->
<x-modal name="edit-branch-{{ $branch->branch_id }}" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-code-branch"></i>
                <h2 class="text-xl font-semibold">Edit Branch Details</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'edit-branch-{{ $branch->branch_id }}')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('branches.update', $branch->branch_id) }}" class="space-y-4 text-sm">
            @csrf
            @method('PUT')

            <!-- Branch Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Branch Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Branch Name</label>
                        <input type="text" name="branch_name" value="{{ $branch->branch_name }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Location</label>
                        <input type="text" name="location" value="{{ $branch->location }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-branch-{{ $branch->branch_id }}')" class="px-3 py-1 ...">Cancel</button>
                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700">Update</button>
            </div>
        </form>
    </div>
</x-modal>
@endforeach

@foreach($branches as $branch)
<!-- Delete Branch Modal -->
<x-modal name="delete-branch-{{ $branch->branch_id }}" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <!-- Red warning icon -->
        <i class="mx-auto text-4xl text-red-500 fa-solid fa-triangle-exclamation"></i>

        <h2 class="text-lg font-semibold text-gray-800">Delete Branch?</h2>
        <p class="text-sm text-gray-500">
            This action will permanently remove the branch <span class="text-red-600"><strong>{{ $branch->branch_name }} and all its related records</strong></span>.
            This cannot be undone.
        </p>

        <div class="flex justify-center mt-4 space-x-3">
            <button
                x-on:click="$dispatch('close-modal', 'delete-branch-{{ $branch->branch_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >
                Cancel
            </button>

            <form method="POST" action="{{ route('branches.destroy', $branch->branch_id) }}">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700"
                >
                    Yes, Delete
                </button>
            </form>
        </div>

    </div>
</x-modal>
@endforeach










<!-- Feedback Modals -->
@if(session('feedback'))
    @php
        $feedback = session('feedback');
        $modalName = $feedback['type'] === 'success' ? 'success-modal' : 'error-modal';
        $message = $feedback['message'];
    @endphp

    <script>
        document.addEventListener('alpine:init', () => {
            // Open modal when Alpine is ready
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: '{{ $modalName }}' }));
                // Update message dynamically
                const modal = document.querySelector(`[name="{{ $modalName }}"]`);
                if(modal){
                    modal.querySelector('h2').innerText = '{{ ucfirst($feedback["type"]) }}!';
                    modal.querySelector('p').innerText = '{{ $message }}';
                }
            }, 100); // small delay to ensure modal exists
        });
    </script>
@endif

<!-- Success Modal -->
<x-modal name="success-modal" :show="false" maxWidth="sm">
    <div class="p-6 text-center">
        <i class="text-green-600 fa-solid fa-circle-check fa-2x"></i>
        <h2 class="mt-3 text-lg font-semibold text-gray-800">Success!</h2>
        <p class="mt-1 text-gray-600">Operation completed successfully.</p>
        <button type="button"
            class="px-4 py-2 mt-4 text-white bg-green-600 rounded hover:bg-green-700"
            x-on:click="$dispatch('close-modal', 'success-modal')">
            Yay!
        </button>
    </div>
</x-modal>

<!-- Error Modal -->
<x-modal name="error-modal" :show="false" maxWidth="sm">
    <div class="p-6 text-center">
        <i class="text-red-600 fa-solid fa-circle-xmark fa-2x"></i>
        <h2 class="mt-3 text-lg font-semibold text-gray-800">Error!</h2>
        <p class="mt-1 text-gray-600">Something went wrong. Please try again.</p>
        <button type="button"
            class="px-4 py-2 mt-4 text-white bg-red-600 rounded hover:bg-red-700"
            x-on:click="$dispatch('close-modal', 'error-modal')">
            Try Again
        </button>
    </div>
</x-modal>

<!-- Footer Branding -->
<footer class="flex flex-col items-center justify-between py-4 text-sm text-gray-400 border-t sm:flex-row">
    <!-- Left / Center Text -->
    <p class="mb-2 sm:mb-0">© 2025 KitaKeeps. All rights reserved.</p>

    <!-- Right / Social Icons -->
    <div class="flex space-x-3">
        <a href="https://www.facebook.com/profile.php?id=61581974522036" target="_blank" 
           class="transition hover:text-blue-600">
            <i class="text-lg fa-brands fa-facebook"></i>
        </a>
        <a href="https://www.tiktok.com/@kitakeeps" target="_blank" 
           class="transition hover:text-black">
            <i class="text-lg fa-brands fa-tiktok"></i>
        </a>
    </div>
</footer>