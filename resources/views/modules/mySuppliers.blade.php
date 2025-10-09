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
            <!-- <button x-on:click="$dispatch('open-modal', 'switch-branch')" 
                    class="text-gray-600 hover:text-black">
                <i class="fa-solid fa-caret-down"></i>
            </button> -->
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

        <!-- Add Supplier -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'add-supplier')"  class="flex items-center px-5 py-2 text-xs text-white transition-colors bg-blue-600 rounded-md shadow hover:bg-blue-800 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-person-circle-plus"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Add Supplier</span>
            </button>
        </div>
    </div>
</div>

<!-- Export -->
<x-modal name="export-options" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4">

        <h2 class="text-lg font-semibold text-center text-gray-800">Export As</h2>

        <div class="flex justify-center mt-4 space-x-4">

            <!-- Excel -->
            <form method="GET" action="{{ route('suppliers.export') }}" class="flex flex-col items-center space-y-2">
                <button 
                    type="submit" 
                    class="flex flex-col items-center justify-center w-24 px-4 py-3 transition bg-green-100 rounded-lg shadow hover:bg-green-200 focus:ring-2 focus:ring-green-400 focus:outline-none"
                >
                    <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                    <span class="text-sm text-gray-700">Excel</span>
                </button>

                <label class="flex items-center justify-center text-xs text-gray-600">
                    <input 
                        type="checkbox" 
                        name="cloud_sync" 
                        value="1"
                        class="mr-1 text-green-600 border-gray-300 rounded focus:ring-green-400"
                    >
                    Sync to Cloud
                </label>
                <!-- Subtext -->
                <p class="text-[11px] text-gray-400 leading-tight">
                    If unchecked, the file will only be downloaded locally.
                </p>
            </form>

            <!-- DOCX -->
            <!-- <button 
                class="flex flex-col items-center w-24 px-4 py-3 transition bg-blue-100 rounded-lg hover:bg-blue-200"
                x-on:click="exportData('docx')"
            >
                <i class="mb-1 text-2xl text-blue-600 fa-solid fa-file-word"></i>
                <span class="text-sm text-gray-700">DOCX</span>
            </button> -->

            <!-- PDF -->
            <!-- <button 
                class="flex flex-col items-center w-24 px-4 py-3 transition bg-red-100 rounded-lg hover:bg-red-200"
                x-on:click="exportData('pdf')"
            >
                <i class="mb-1 text-2xl text-red-600 fa-solid fa-file-pdf"></i>
                <span class="text-sm text-gray-700">PDF</span>
            </button> -->

        </div>

        <!-- Cancel -->
        <div class="flex justify-center mt-6">
            <button 
                x-on:click="$dispatch('close-modal', 'export-options')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>

<!-- Add Supplier Modal -->
<x-modal name="add-supplier" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="mr-2 fa-solid fa-truck-field"></i>
                <h2 class="text-xl font-semibold">Add New Supplier</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-supplier')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        <!-- Form -->
        <form method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm">
            @csrf
            
            <!-- Supplier Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="preview-supp" src="assets/images/logo/logo-removebg-preview.png" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="Supplier photo">

                    <!-- Upload button -->
                    <label for="supp_image" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-blue-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>
                    <input type="file" id="supp_image" name="supp_image" class="hidden" accept="image/*"
                        onchange="document.getElementById('preview-supp').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <p class="mt-2 text-sm text-gray-500">Add profile photo</p>
            </div>

            <!-- Supplier Info -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Supplier Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Supplier Name -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Supplier Name</label>
                        <input required type="text" name="supp_name" placeholder="KitaKeeps Warehouse" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Contact Number -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input maxlength="11"
                                pattern="\d{11}"
                                title="Contact number must be exactly 11 digits" 
                                required 
                                type="text" 
                                name="supp_contact" 
                                placeholder="09123456789" 
                                class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input required type="text" name="supp_address" placeholder="123 Supplier St, City" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-supplier')"
                class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>

                <button type="submit" 
                class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Save</button>
            </div>
        </form>

    </div>
</x-modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'success-modal' }));
        @endif

        @if(session('error'))
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
        @endif
    });
</script>












<!-- All Branches -->
<h3 class="mt-8 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">All Suppliers</h3>

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
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Supplier Name</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Contact Number</th>
                    <th class="px-3 py-2 text-left border">Address</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                
                <!-- Supplier Rows -->
                <tr class="hover:bg-gray-50">
                    <!-- Auto-incrementing count -->
                    <!-- <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td> -->
                    <td class="px-3 py-2 border bg-blue-50">
                        {{ $suppliers->firstItem() + $loop->index }}
                    </td>

                    <!-- Supplier ID -->
                    <td class="px-3 py-2 border">{{ $supplier->supplier_id }}</td>

                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <!-- Circle image or placeholder -->
                            @if($supplier->supp_image_path)
                                <img 
                                    src="{{ asset('storage/' . $supplier->supp_image_path) }}" 
                                    alt="{{ $supplier->supp_name }}" 
                                    class="object-cover w-8 h-8 rounded-full"
                                >
                            @else
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            @endif
                            <!-- Name -->
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                {{ $supplier->supp_name }}
                            </span>
                        </div>
                    </td>

                    <!-- Supplier Contact -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        {{ $supplier->supp_contact }}
                    </td>

                    <!-- Supplier Location -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        {{ $supplier->supp_address }}
                    </td>

                    <!-- Actions -->
                    <td class="flex justify-center gap-2 px-3 py-3 border">
                        <button  x-data
                            x-on:click="$dispatch('open-modal', 'view-supplier-{{ $supplier->supplier_id }}')" 
                            x-on:click="$dispatch('open-modal', 'view-supplier')" class="px-2 py-1 text-white bg-blue-500 rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button x-data 
                        x-on:click="$dispatch('open-modal', 'edit-supplier-{{ $supplier->supplier_id }}')" class="px-2 py-1 text-white bg-green-500 rounded">
                            <i class="fa-solid fa-user-pen"></i>
                        </button>
                        <button x-data 
                        x-on:click="$dispatch('open-modal', 'delete-supplier-{{ $supplier->supplier_id }}')" 
                        x-on:click="$dispatch('open-modal', 'delete-supplier')" class="px-2 py-1 text-white bg-red-500 rounded">
                            <i class="fa-solid fa-user-minus"></i>
                        </button>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-2 text-center text-gray-500 border">
                            Nothing to see here yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
        <p class="text-sm">
            Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} entries
        </p>
        <!-- Previous / Next -->
        <div class="flex gap-2">
            <!-- Previous button -->
            <a 
                href="{{ $suppliers->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $suppliers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>

            <!-- Next button -->
            <a 
                href="{{ $suppliers->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $suppliers->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

@foreach($suppliers as $supplier)
<!-- View Supplier Modal -->
<x-modal name="view-supplier-{{ $supplier->supplier_id }}" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Profile Section -->
        <div class="flex items-center space-x-4">
            <!-- Supplier Icon / Image -->
            <div class="flex items-center justify-center w-20 h-20 overflow-hidden text-3xl text-white bg-blue-500 rounded-full">
                @if($supplier->supp_image_path)
                    <img src="{{ asset('storage/' . $supplier->supp_image_path) }}" alt="{{ $supplier->supp_name }}" class="object-cover w-full h-full rounded-full">
                @else
                    <i class="fa-solid fa-truck-field"></i>
                @endif
            </div>

            <!-- Supplier Name -->
            <div>
                <p class="text-lg font-semibold text-gray-800">{{ $supplier->supp_name }}</p>
                <p class="text-sm text-gray-500">Supplier since {{ $supplier->created_at->format('m-d-Y') }}</p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t"></div>

        <!-- Supplier Details -->
        <div class="space-y-2 text-sm text-gray-700">
            <p><span class="font-medium">Contact Number:</span> {{ $supplier->supp_contact ?? 'N/A' }}</p>
            <p><span class="font-medium">Address:</span> {{ $supplier->supp_address ?? 'N/A' }}</p>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end pt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'view-supplier-{{ $supplier->supplier_id }}')"
                class="px-4 py-2 text-white transition bg-gray-500 rounded hover:bg-gray-600"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>
@endforeach

@foreach($suppliers as $supplier)
<!-- Edit Supplier Modal -->
<x-modal name="edit-supplier-{{ $supplier->supplier_id }}" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="mr-2 fa-solid fa-truck-field"></i>
                <h2 class="text-xl font-semibold">Edit Supplier Details</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'edit-supplier-{{ $supplier->supplier_id }}')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        <!-- Form -->
        <form class="space-y-4 text-sm" 
            method="POST" 
            action="{{ route('suppliers.update', $supplier->supplier_id) }}" 
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="supplierImagePreview-{{ $supplier->supplier_id }}" 
                        src="{{ $supplier->supp_image_path ? asset('storage/' . $supplier->supp_image_path) : 'assets/images/logo/logo-removebg-preview.png' }}" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="{{ $supplier->supp_name }}">

                    <!-- Edit image button -->
                    <label for="supp_image_{{ $supplier->supplier_id }}" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>

                    <input type="file" name="supp_image" id="supp_image_{{ $supplier->supplier_id }}" class="hidden" 
                        onchange="previewSupplierImage(event, {{ $supplier->supplier_id }})">
                </div>
                <p class="mt-2 text-sm text-gray-500">Change supplier photo</p>
            </div>

            <!-- Supplier Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Supplier Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Supplier Name -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Supplier Name</label>
                        <input type="text" name="supp_name" value="{{ $supplier->supp_name }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Contact Number -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input type="text" name="supp_contact" value="{{ $supplier->supp_contact }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input type="text" name="supp_address" value="{{ $supplier->supp_address }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-supplier-{{ $supplier->supplier_id }}')"
                        class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Update</button>
            </div>
        </form>
    </div>
</x-modal>
@endforeach

<script>
    function previewSupplierImage(event, supplierId) {
        const input = event.target;
        const preview = document.getElementById('supplierImagePreview-' + supplierId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@foreach($suppliers as $supplier)
<!-- Delete Supplier Modal -->
<x-modal name="delete-supplier-{{ $supplier->supplier_id }}" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <!-- Red warning icon -->
        <i class="mx-auto text-4xl text-red-500 fa-solid fa-triangle-exclamation"></i>

        <h2 class="text-lg font-semibold text-gray-800">Delete Supplier?</h2>
        <p class="text-sm text-gray-500">
            This action will permanently remove <span class="font-medium">{{ $supplier->supp_name }}</span> from the system. This cannot be undone.
        </p>

        <div class="flex justify-center mt-4 space-x-3">
            <button
                x-on:click="$dispatch('close-modal', 'delete-supplier-{{ $supplier->supplier_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >
                Cancel
            </button>

            <form method="POST" action="{{ route('suppliers.destroy', $supplier->supplier_id) }}">
                @csrf
                @method('DELETE') <!-- tells Laravel this is a DELETE request -->
                <button type="submit" 
                    class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                    Yes, Delete
                </button>
            </form>
        </div>

    </div>
</x-modal>
@endforeach

<!-- Feedback Modals -->
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
<footer class="py-4 text-sm text-center text-gray-400 border-t">
    © 2025 KitaKeeps. All rights reserved.
</footer>