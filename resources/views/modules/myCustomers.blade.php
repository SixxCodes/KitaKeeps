@php
    use App\Models\Customer;

    $owner = auth()->user();
    $userBranches = $owner ? $owner->branches : collect();

    // current branch (session fallback to first branch)
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                    ?? $userBranches->sortBy('branch_id')->first();

    // request params
    $perPage = (int) request('per_page', 5);
    $search  = trim(request('search', ''));

    // build query
    $query = Customer::query();

    // scope to branch when available
    if ($currentBranch) {
        $query->where('branch_id', $currentBranch->branch_id);
    }

    // search across name / contact / address
    if ($search !== '') {
        $query->where(function($q) use ($search) {
            $q->where('cust_name', 'like', "%{$search}%")
              ->orWhere('cust_contact', 'like', "%{$search}%")
              ->orWhere('cust_address', 'like', "%{$search}%");
        });
    }

    // paginate and keep query string for links, descending order
    $customers = $query->orderBy('customer_id', 'desc')->paginate($perPage)->appends(request()->query());
@endphp

@php
    use App\Models\Sale;

    $owner = auth()->user();
    $userBranches = $owner ? $owner->branches : collect();

    // Current branch (session fallback to first branch)
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                    ?? $userBranches->sortBy('branch_id')->first();

    // Request params
    $perPage = (int) request('per_page', 5);
    $search  = trim(request('search', ''));

    // Build query for customers who have at least one credit sale
    $query = Customer::query();

    // Scope to branch when available
    if ($currentBranch) {
        $query->where('branch_id', $currentBranch->branch_id);
    }

    // Search across name / contact / address
    if ($search !== '') {
        $query->where(function($q) use ($search) {
            $q->where('cust_name', 'like', "%{$search}%")
            ->orWhere('cust_contact', 'like', "%{$search}%")
            ->orWhere('cust_address', 'like', "%{$search}%");
        });
    }

    // Only customers with at least one sale of type Credit
    $query->whereHas('sales', function($q) {
        $q->where('payment_type', 'Credit');
    });

    $creditCustomers = Customer::with(['sales' => function($q) use ($currentBranch) {
        $q->where('payment_type', 'Credit');
        if ($currentBranch) {
            $q->where('branch_id', $currentBranch->branch_id);
        }
        $q->orderBy('due_date', 'asc');
    }])
    ->whereHas('sales', function($q) use ($currentBranch) {
        $q->where('payment_type', 'Credit');
        if ($currentBranch) {
            $q->where('branch_id', $currentBranch->branch_id);
        }
    })
    ->paginate($perPage);

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
            <button x-on:click="$dispatch('open-modal', 'export-options')"  class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-download"></i>
                <span class="hidden ml-2 lg:inline">Export</span>
            </button>
        </div>

        <!-- Add Customer -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'add-customer')"  class="flex items-center px-5 py-2 text-xs text-white transition-colors bg-blue-600 rounded-md shadow hover:bg-blue-800 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-user-plus"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Add Customer</span>
            </button>
        </div>
    </div>
</div>

<!-- Export -->
<x-modal name="export-options" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4">

        <h2 class="text-lg font-semibold text-center text-gray-800">Export As</h2>

        <!-- Info about sheets -->
        <p class="text-sm text-center text-gray-500">
            Credits and Customer details will be exported on <strong>separate sheets</strong> in the Excel file.
        </p>

        <div class="flex justify-center mt-4 space-x-4">

            <!-- Excel -->
            <a href="{{ route('customers.export') }}"
            class="flex flex-col items-center px-4 py-3 transition bg-blue-100 rounded-lg w-28 hover:bg-blue-200">
                <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                <span class="text-sm text-gray-700">
                    Excel
                </span>
            </a>

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

<!-- Add Customer -->
<x-modal name="add-customer" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="fa-solid fa-user-plus"></i>
            <h2 class="text-xl font-semibold">Add New Customer</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-customer')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        <!-- Form -->
        <form action="{{ route('customers.store') }}" enctype="multipart/form-data" method="POST" class="space-y-4 text-sm">
            @csrf

            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="customerImagePreview"
                        src="assets/images/logo/logo-removebg-preview.png"
                        class="object-cover w-24 h-24 border rounded-full shadow"
                        alt="Customer photo">

                    <!-- Hidden File Input -->
                    <input type="file" name="cust_image_path" id="cust_image_path"
                        class="hidden" accept="image/*"
                        onchange="previewCustomerImage(event)">

                    <!-- Edit image button -->
                    <button type="button"
                        onclick="document.getElementById('cust_image_path').click();"
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">Add customer photo</p>
            </div>

            <!-- Customer Info -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Customer Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Customer Name -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Customer Name</label>
                        <input required type="text" name="cust_name" placeholder="Juan Dela Cruz"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" required />
                    </div>

                    <!-- Contact Number -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input placeholder="09123456789"
                                maxlength="11"
                                pattern="\d{11}"
                                title="Contact number must be exactly 11 digits" 
                                required 
                                type="text" 
                                name="cust_contact"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input required type="text" name="cust_address" placeholder="123 Main St, City"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                    </div>

                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-customer')"
                class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                    Cancel
                </button>

                <button type="submit" 
                class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</x-modal>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        window.previewCustomerImage = function(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('customerImagePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>














@php
    $currentBranchId = session('current_branch_id');

    if (!$currentBranchId) {
        $currentBranchId = auth()->user()
            ->branches()
            ->orderBy('branch.branch_id')
            ->value('branch.branch_id');

        session(['current_branch_id' => $currentBranchId]);
    }

    // Customers with Credit (current branch)
    $customersWithCredit = \App\Models\Customer::whereHas('sales', function($query) use ($currentBranchId) {
        $query->where('payment_type', 'Credit')
            ->where('branch_id', $currentBranchId);
    })->count();

    // Customers with Credit last week (for % change)
    $lastWeekCustomers = \App\Models\Customer::whereHas('sales', function($query) use ($currentBranchId) {
        $query->where('payment_type', 'Credit')
            ->where('branch_id', $currentBranchId)
            ->whereBetween('due_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
    })->count();

    // Percentage change for Customers with Credit
    $customersPercent = $lastWeekCustomers
        ? round((($customersWithCredit - $lastWeekCustomers) / $lastWeekCustomers) * 100, 1)
        : 0;

    // Credit sales due this week
    $dueThisWeek = \App\Models\Sale::where('payment_type', 'Credit')
        ->where('branch_id', $currentBranchId)
        ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
        ->count();

    // Credit sales due last week
    $dueLastWeek = \App\Models\Sale::where('payment_type', 'Credit')
        ->where('branch_id', $currentBranchId)
        ->whereBetween('due_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
        ->count();

    // Percentage change for Due This Week
    $duePercent = $dueLastWeek ? round((($dueThisWeek - $dueLastWeek) / $dueLastWeek) * 100, 1) : 0;

    // Total receivables (current branch)
    $totalReceivables = \App\Models\Sale::where('payment_type', 'Credit')
        ->where('branch_id', $currentBranchId)
        ->sum('total_amount');

    // Total receivables last week (for % change)
    $lastWeekReceivables = \App\Models\Sale::where('payment_type', 'Credit')
        ->where('branch_id', $currentBranchId)
        ->whereBetween('due_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
        ->sum('total_amount');

    // Percentage change for Total Receivables
    $totalPercent = $lastWeekReceivables
        ? round((($totalReceivables - $lastWeekReceivables) / $lastWeekReceivables) * 100, 1)
        : 0;
@endphp

<!-- Customer Summary -->
<div class="overflow-x-auto table-pretty-scrollbar">
    <div class="flex gap-6 p-6 mt-1 min-w-max">
        <!-- Customers with Credit -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[270px]">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Customers with Credit</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $customersWithCredit }}</h2>
            <!-- <p class="mt-1 text-sm {{ $customersPercent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $customersPercent >= 0 ? '▲' : '▼' }} {{ abs($customersPercent) }}% 
                <span class="text-gray-500">this week</span>
            </p> -->
        </div>

        <!-- Due This Week -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[270px]">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Due This Week</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $dueThisWeek }}</h2>
            <!-- <p class="mt-1 text-sm {{ $duePercent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $duePercent >= 0 ? '▲' : '▼' }} {{ abs($duePercent) }}% 
                <span class="text-gray-500">this week</span>
            </p> -->
        </div>

        <!-- Total Receivables -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[270px]">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Total Receivables</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">₱{{ number_format($totalReceivables, 2) }}</h2>
            <!-- <p class="mt-1 text-sm {{ $totalPercent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $totalPercent >= 0 ? '▲' : '▼' }} {{ abs($totalPercent) }}% 
                <span class="text-gray-500">this week</span>
            </p> -->
        </div>
    </div>
</div>











<!-- ALL CUSTOMERS w/ CREDIT -->
<h3 class="mt-2 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">Customer Credits</h3>

<div class="p-4 mt-3 bg-white rounded-lg shadow">
    <!-- Search + Entries -->
    <div class="flex items-center justify-between mb-4 whitespace-nowrap">
        <div>
            <label class="mr-2 text-sm">Show</label>
            <select onchange="window.location.href='?per_page='+this.value+'&search={{ request('search') }}'" 
                class="py-1 text-sm border rounded">
                <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
            </select>
            <span class="ml-2 text-sm">entries</span>
        </div>

        <!-- Search Bar -->
        <div class="flex items-center space-x-2">
            <div class="flex items-center px-2 py-1 border rounded w-52">
                <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search..."
                    onkeydown="if(event.key==='Enter'){ window.location.href='?per_page={{ request('per_page',5) }}&search='+this.value; }"
                    class="w-full py-0 text-sm bg-transparent border-none outline-none"
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
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Customer Name</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Total Credit</th>
                    <th class="px-3 py-2 text-left border ellipses whitespace-nowrap">Next Due Date</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditCustomers as $customer)
                    @php
                        $creditSales = $customer->sales; // already loaded
                        $totalCredit = $creditSales->sum('total_amount');
                        $nextDueSale = $creditSales->first(); // earliest due
                    @endphp
                    <tr class="hover:bg-gray-50">
                        
                        <!-- Auto-increment row number -->
                        <td class="px-3 py-2 border bg-blue-50">
                            {{ $customers->firstItem() + $loop->index }}
                        </td>

                        <td class="px-3 py-2 border">{{ $customer->customer_id }}</td>

                        <td class="px-3 py-2 border">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <span class="overflow-hidden whitespace-nowrap text-ellipsis">{{ $customer->cust_name }}</span>
                            </div>
                        </td>

                        <td class="px-3 py-2 border ellipses whitespace-nowrap">
                            P{{ number_format($totalCredit, 2) }}
                        </td>

                        <td class="px-3 py-2 border ellipses whitespace-nowrap">
                            {{ $nextDueSale?->due_date?->format('m-d-y') ?? '-' }}
                        </td>

                        <td class="flex justify-center gap-2 px-3 py-3 border">
                            <button 
                                x-on:click="$dispatch('open-modal', 'customer-credits-{{ $customer->customer_id }}')"
                                class="px-2 py-1 text-white bg-blue-500 rounded"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-center text-gray-500 border">
                            No credit customers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
        <p class="text-sm">
            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
        </p>
        <div class="flex gap-2">
            <a href="{{ $customers->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $customers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>
            <a href="{{ $customers->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $customers->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

<!-- View Customer Credit Modal -->
@foreach($creditCustomers as $customer)
    <x-modal name="customer-credits-{{ $customer->customer_id }}" :show="false" maxWidth="2xl">
        <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
            <!-- Title -->
            <div class="flex items-center mb-4 space-x-1 text-blue-900">
                <i class="fa-solid fa-credit-card"></i>
                <h2 class="text-xl font-semibold">{{ $customer->cust_name }}'s Credits</h2>
            </div>

            <!-- Credits Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-3 py-2 text-left border">Credit ID</th>
                            <th class="px-3 py-2 text-left border whitespace-nowrap">Due Date</th>
                            <th class="px-3 py-2 text-left border whitespace-nowrap">Sale Date</th>
                            <th class="px-3 py-2 text-left border whitespace-nowrap">Amount</th>
                            <th class="px-3 py-2 text-center border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach($customer->sales as $sale)
                            @php $totalAmount += $sale->total_amount; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">{{ $sale->sale_id }}</td>
                                <td class="px-3 py-2 border">{{ $sale->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-3 py-2 border">{{ $sale->sale_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-3 py-2 border whitespace-nowrap">₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="flex justify-center gap-2 px-3 py-2 border">

                                    <!-- Pay Form -->
                                        <button type="submit" 
                                            class="px-2 py-1 text-white bg-green-500 rounded hover:bg-green-600"
                                                x-on:click="$dispatch('open-modal', 'confirm-pay-{{ $sale->sale_id }}')"                                        >
                                            <i class="fa-solid fa-peso-sign"></i> Pay
                                        </button>

                                    <!-- Delete Form -->
                                        <button type="submit" 
                                            class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                            x-on:click="$dispatch('open-modal', 'confirm-delete-{{ $sale->sale_id }}')"
                                        >
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>

                                </td>
                            </tr>
                        @endforeach

                        <!-- Total Row -->
                        <tr class="font-semibold bg-gray-100">
                            <td colspan="3" class="px-3 py-2 text-right border">Total Amount:</td>
                            <td class="px-3 py-2 border">₱{{ number_format($totalAmount, 2) }}</td>
                            <td class="flex justify-center gap-2 px-3 py-2 border">

                                <!-- Pay All Form -->
                                    <button type="submit" 
                                        class="px-2 py-1 text-white bg-green-600 rounded hover:bg-green-700"
                                        x-on:click="$dispatch('open-modal', 'confirm-pay-all-{{ $customer->customer_id }}')"
                                    >
                                        <i class="fa-solid fa-money-bill-wave"></i> Pay All
                                    </button>

                                <!-- Delete All Form -->
                                    <button type="submit" 
                                        class="px-2 py-1 text-white bg-red-600 rounded hover:bg-red-700"
                                        x-on:click="$dispatch('open-modal', 'confirm-delete-all-{{ $customer->customer_id }}')"
                                    >
                                        <i class="fa-solid fa-trash"></i> Delete All
                                    </button>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Button -->
            <div class="flex justify-end mt-4">
                <button 
                    x-on:click="$dispatch('close-modal', 'customer-credits-{{ $customer->customer_id }}')"
                    class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700"
                >
                    Close
                </button>
            </div>
        </div>
    </x-modal>
@endforeach

<!-- Pay -->
@foreach($creditCustomers as $customer)
    @foreach($customer->sales as $sale)
        <x-modal name="confirm-pay-{{ $sale->sale_id }}" :show="false" maxWidth="sm">
            <div class="p-6 text-center">
                <i class="mx-auto text-4xl text-yellow-400 fa-solid fa-triangle-exclamation"></i>
                <h2 class="mt-3 text-lg font-semibold text-gray-800">Confirm Payment</h2>
                <p class="mt-1 text-gray-600">Pay credit for Sale #{{ $sale->sale_id }}?</p>

                <div class="flex justify-center mt-4 space-x-3">
                    <button 
                        x-on:click="$dispatch('close-modal', 'confirm-pay-{{ $sale->sale_id }}')"
                        class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
                    >
                        Cancel
                    </button>

                    <form action="{{ route('sales.pay', $sale->sale_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-white transition bg-green-600 rounded hover:bg-green-700">
                            Yes, Pay
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    @endforeach
@endforeach

<!-- Delete -->
@foreach($creditCustomers as $customer)
    @foreach($customer->sales as $sale)
        <x-modal name="confirm-delete-{{ $sale->sale_id }}" :show="false" maxWidth="sm">
            <div class="p-6 text-center">
                <i class="mx-auto text-4xl text-red-500 fa-solid fa-trash"></i>
                <h2 class="mt-3 text-lg font-semibold text-gray-800">Confirm Deletion</h2>
                <p class="mt-1 text-gray-600">Delete credit for Sale #{{ $sale->sale_id }}?</p>

                <div class="flex justify-center mt-4 space-x-3">
                    <button 
                        x-on:click="$dispatch('close-modal', 'confirm-delete-{{ $sale->sale_id }}')"
                        class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
                    >
                        Cancel
                    </button>

                    <form action="{{ route('sales.destroy', $sale->sale_id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                            Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    @endforeach
@endforeach

<!-- Pay All Credits -->
@foreach($creditCustomers as $customer)
    <x-modal name="confirm-pay-all-{{ $customer->customer_id }}" :show="false" maxWidth="sm">
        <div class="p-6 text-center">
            <i class="mx-auto text-4xl text-yellow-400 fa-solid fa-coins"></i>
            <h2 class="mt-3 text-lg font-semibold text-gray-800">Confirm Pay All</h2>
            <p class="mt-1 text-gray-600">
                Pay all credits for <strong>{{ $customer->cust_name }}</strong>?
            </p>

            <div class="flex justify-center mt-4 space-x-3">
                <button 
                    x-on:click="$dispatch('close-modal', 'confirm-pay-all-{{ $customer->customer_id }}')"
                    class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
                >
                    Cancel
                </button>

                <form action="{{ route('sales.payAll', $customer->customer_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-white transition bg-green-600 rounded hover:bg-green-700">
                        Yes, Pay All
                    </button>
                </form>
            </div>
        </div>
    </x-modal>
@endforeach

<!-- Delete All Credits -->
@foreach($creditCustomers as $customer)
    <x-modal name="confirm-delete-all-{{ $customer->customer_id }}" :show="false" maxWidth="sm">
        <div class="p-6 text-center">
            <i class="mx-auto text-4xl text-red-500 fa-solid fa-trash"></i>
            <h2 class="mt-3 text-lg font-semibold text-gray-800">Confirm Delete All</h2>
            <p class="mt-1 text-gray-600">
                Delete all unpaid credits for <strong>{{ $customer->cust_name }}</strong>?
            </p>

            <div class="flex justify-center mt-4 space-x-3">
                <button 
                    x-on:click="$dispatch('close-modal', 'confirm-delete-all-{{ $customer->customer_id }}')"
                    class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
                >
                    Cancel
                </button>

                <form action="{{ route('sales.destroyAll', $customer->customer_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                        Yes, Delete All
                    </button>
                </form>
            </div>
        </div>
    </x-modal>
@endforeach
















<!-- ALL CUSTOMERS -->
<h3 class="mt-8 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">All Customers</h3>

<div class="p-4 mt-3 bg-white rounded-lg shadow">
    <!-- Search + Entries -->
    <div class="flex items-center justify-between mb-4 whitespace-nowrap">
        <div>
            <label class="mr-2 text-sm">Show</label>
            <select onchange="window.location.href='?per_page='+this.value+'&search={{ request('search') }}'" 
                class="py-1 text-sm border rounded">
                <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
            </select>
            <span class="ml-2 text-sm">entries</span>
        </div>

        <!-- Search Bar -->
        <div class="flex items-center space-x-2">
            <div class="flex items-center px-2 py-1 border rounded w-52">
                <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search..."
                    onkeydown="if(event.key==='Enter'){ window.location.href='?per_page={{ request('per_page',5) }}&search='+this.value; }"
                    class="w-full py-0 text-sm bg-transparent border-none outline-none"
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
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Customer Name</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Contact Number</th>
                    <th class="px-3 py-2 text-left border">Address</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50">
                    <!-- Auto-increment row number -->
                    <td class="px-3 py-2 border bg-blue-50">
                        {{ $customers->firstItem() + $loop->index }}
                    </td>

                    <!-- Customer ID -->
                    <td class="px-3 py-2 border">{{ $customer->customer_id }}</td>

                    <!-- Customer Name with Image -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            @if($customer->cust_image_path)
                                <img 
                                    src="{{ asset('storage/' . $customer->cust_image_path) }}" 
                                    alt="{{ $customer->cust_name }}" 
                                    class="object-cover w-8 h-8 rounded-full"
                                >
                            @else
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            @endif
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                {{ $customer->cust_name }}
                            </span>
                        </div>
                    </td>

                    <!-- Contact Number -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        {{ $customer->cust_contact }}
                    </td>

                    <!-- Address -->
                    <td class="px-3 py-2 border ellipsis whitespace-nowrap">
                        {{ $customer->cust_address }}
                    </td>

                    <!-- Actions -->
                    <td class="flex justify-center gap-2 px-3 py-3 border">

                        <button x-data
                            x-on:click="$dispatch('open-modal', 'view-customer-{{ $customer->customer_id }}')" 
                            class="px-2 py-1 text-white bg-blue-500 rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        @if (Auth::user()->role !== 'Cashier')
                            <button x-data
                                x-on:click="$dispatch('open-modal', 'edit-customer-{{ $customer->customer_id }}')" 
                                class="px-2 py-1 text-white bg-green-500 rounded">
                                <i class="fa-solid fa-user-pen"></i>
                            </button>

                            <button x-data
                                x-on:click="$dispatch('open-modal', 'delete-customer-{{ $customer->customer_id }}')" 
                                class="px-2 py-1 text-white bg-red-500 rounded">
                                <i class="fa-solid fa-user-minus"></i>
                            </button>
                        @endif
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
            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
        </p>
        <div class="flex gap-2">
            <a href="{{ $customers->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $customers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>
            <a href="{{ $customers->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $customers->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

@foreach($customers as $customer)
<!-- View Customer Details Modal -->
<x-modal name="view-customer-{{ $customer->customer_id }}" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Profile Section -->
        <div class="flex items-center space-x-4">
            <!-- Customer Icon / Image -->
            <div class="flex items-center justify-center w-20 h-20 overflow-hidden text-3xl text-white bg-blue-400 rounded-full">
                @if($customer->cust_image_path)
                    <img src="{{ asset('storage/' . $customer->cust_image_path) }}" 
                         alt="{{ $customer->cust_name }}" 
                         class="object-cover w-full h-full rounded-full">
                @else
                    <i class="fa-solid fa-user"></i>
                @endif
            </div>

            <!-- Name -->
            <div>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $customer->cust_name }}
                </p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t"></div>

        <!-- Customer Details -->
        <div class="space-y-4 text-sm text-gray-700">
            <div>
                <h3 class="mb-2 text-xs font-semibold tracking-wide text-green-500 uppercase">Customer Information</h3>
                <div class="flex flex-col space-y-2">
                    <p><span class="font-medium">Contact:</span> {{ $customer->cust_contact ?? 'N/A' }}</p>
                    <p><span class="font-medium">Address:</span> {{ $customer->cust_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end pt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'view-customer-{{ $customer->customer_id }}')"
                class="px-4 py-2 text-white transition bg-gray-600 rounded hover:bg-gray-700"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>
@endforeach

@foreach($customers as $customer)
<!-- Edit Customer Modal -->
<x-modal name="edit-customer-{{ $customer->customer_id }}" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">

        <!-- Title -->
        <div class="flex justify-between mb-4 text-blue-900">
            <div class="flex items-center space-x-1">
                <i class="fa-solid fa-user-pen"></i>
                <h2 class="text-xl font-semibold">Edit Customer</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'edit-customer-{{ $customer->customer_id }}')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        <!-- Form -->
        <form action="{{ route('customers.update', $customer->customer_id) }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="space-y-4 text-sm">
            @csrf
            @method('PUT')

            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="customerImagePreviewEdit-{{ $customer->customer_id }}"
                         src="{{ $customer->cust_image_path ? asset('storage/' . $customer->cust_image_path) : asset('assets/images/logo/logo-removebg-preview.png') }}"
                         class="object-cover w-24 h-24 border rounded-full shadow" 
                         alt="Customer photo">

                    <!-- Hidden File Input -->
                    <input type="file" 
                           name="cust_image_path" 
                           id="cust_image_path_{{ $customer->customer_id }}" 
                           class="hidden" 
                           accept="image/*"
                           onchange="previewCustomerImageEdit(event, '{{ $customer->customer_id }}')">

                    <!-- Edit image button -->
                    <button type="button"
                        onclick="document.getElementById('cust_image_path_{{ $customer->customer_id }}').click();"
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">Change profile photo</p>
            </div>

            <!-- Customer Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Customer Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Name</label>
                        <input type="text" 
                               name="cust_name" 
                               value="{{ $customer->cust_name }}" 
                               class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Contact Number -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input type="text" 
                               name="cust_contact" 
                               value="{{ $customer->cust_contact }}" 
                               class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input type="text" 
                               name="cust_address" 
                               value="{{ $customer->cust_address }}" 
                               class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                    x-on:click="$dispatch('close-modal', 'edit-customer-{{ $customer->customer_id }}')"
                    class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                    Cancel
                </button>

                <button type="submit" 
                    class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</x-modal>
@endforeach

<script>
    function previewCustomerImageEdit(event, id) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(`customerImagePreviewEdit-${id}`).src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>

@foreach($customers as $customer)
<!-- Delete Customer Modal -->
<x-modal name="delete-customer-{{ $customer->customer_id }}" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <!-- Red warning icon -->
        <i class="mx-auto text-4xl text-red-500 fa-solid fa-triangle-exclamation"></i>

        <h2 class="text-lg font-semibold text-gray-800">Delete {{ $customer->cust_name }}?</h2>
        <p class="text-sm text-gray-500">
            This action will permanently remove <span class="font-semibold text-red-600">{{ $customer->cust_name }}</span> 
            and all of their related records from the system. This cannot be undone.
        </p>

        <div class="flex justify-center mt-4 space-x-3">
            <!-- Cancel -->
            <button
                x-on:click="$dispatch('close-modal', 'delete-customer-{{ $customer->customer_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >
                Cancel
            </button>

            <!-- Delete -->
            <form action="{{ route('customers.destroy', $customer->customer_id) }}" method="POST">
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

<!-- script for feedback modals -->
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