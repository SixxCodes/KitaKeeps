@php
use App\Models\Sale;
use App\Models\Attendance;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$userBranches = $user->branches ?? collect();
$currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                ?? $userBranches->sortBy('branch_id')->first();
$currentBranchId = $currentBranch?->branch_id;
$currentBranchName = $currentBranch?->branch_name ?? 'N/A';
$direction = request('direction', 'desc');

/* ------------------ SALES ------------------ */
$saleSearch = request('sale_search');
$saleSortBy = request('sale_sort_by', 'sale_date');
$allowedSaleSorts = ['sale_id', 'total_amount', 'payment_type', 'sale_date', 'customer_name', 'cashier'];
if (!in_array($saleSortBy, $allowedSaleSorts)) $saleSortBy = 'sale_date';

$salesQuery = Sale::with(['salebelongsTocustomer', 'salebelongsToUser', 'salebelongsTobranch']);
switch (strtolower($user->role)) {
    case 'owner':
        $branchIds = $userBranches->pluck('branch_id')->toArray();
        $salesQuery->whereIn('branch_id', $branchIds);
        break;
    case 'admin':
        $salesQuery->where('branch_id', $currentBranchId);
        break;
    case 'cashier':
        $salesQuery->where('created_by', $user->user_id)
                   ->where('branch_id', $currentBranchId);
        break;
    default:
        $salesQuery->where('branch_id', $currentBranchId);
        break;
}
if ($saleSearch) {
    $salesQuery->where(function($q) use ($saleSearch) {
        $q->where('sale_id', 'like', "%{$saleSearch}%")
          ->orWhereHas('salebelongsTocustomer', fn($c) => $c->where('cust_name', 'like', "%{$saleSearch}%"))
          ->orWhereHas('salebelongsToUser', fn($u) => $u->where('username', 'like', "%{$saleSearch}%"));
    });
}
switch ($saleSortBy) {
    case 'customer_name':
        $salesQuery->join('customer', 'sale.customer_id', '=', 'customer.customer_id')
                   ->orderBy('customer.cust_name', $direction)
                   ->select('sale.*');
        break;
    case 'cashier':
        $salesQuery->join('user', 'sale.created_by', '=', 'user.user_id')
                   ->orderBy('user.username', $direction)
                   ->select('sale.*');
        break;
    default:
        $salesQuery->orderBy($saleSortBy, $direction);
        break;
}
$sales = $salesQuery->get();

/* ------------------ ATTENDANCE ------------------ */
$attSearch = request('att_search');
$attSortBy = request('att_sort_by', 'att_date');

$attendanceQuery = \App\Models\Attendance::with(['attendancebelongsToemployee.person', 'attendancebelongsToemployee.branch']);
switch (strtolower($user->role)) {
    case 'owner':
        $branchIds = $userBranches->pluck('branch_id')->toArray();
        $attendanceQuery->whereHas('attendancebelongsToemployee', fn($q) => $q->whereIn('branch_id', $branchIds));
        break;
    case 'admin':
        $attendanceQuery->whereHas('attendancebelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
        break;
    case 'cashier':
        $personId = $user->person_id;
        $attendanceQuery->whereHas('attendancebelongsToemployee', fn($q) => $q->where('person_id', $personId));
        break;
    default:
        $attendanceQuery->whereHas('attendancebelongsToemployee', fn($q) => $q->where('branch_id', $currentBranchId));
        break;
}
if ($attSearch) {
    $attendanceQuery->whereHas('attendancebelongsToemployee.person', fn($q) =>
        $q->where('firstname', 'like', "%{$attSearch}%")
          ->orWhere('lastname', 'like', "%{$attSearch}%")
    );
}
switch ($attSortBy) {
    case 'employee_name':
        $attendanceQuery->join('employee', 'attendance.employee_id', '=', 'employee.employee_id')
                        ->join('person', 'employee.person_id', '=', 'person.person_id')
                        ->orderBy('person.firstname', $direction)
                        ->select('attendance.*');
        break;
    case 'branch_name':
        $attendanceQuery->join('employee', 'attendance.employee_id', '=', 'employee.employee_id')
                        ->join('branch', 'employee.branch_id', '=', 'branch.branch_id')
                        ->orderBy('branch.branch_name', $direction)
                        ->select('attendance.*');
        break;
    default:
        $attendanceQuery->orderBy($attSortBy, $direction);
        break;
}
$attendances = $attendanceQuery->get();

/* ------------------ CREDITS ------------------ */
$user = auth()->user();
$search = request('search'); // from search input
$sortBy = request('sort_by', 'cust_name'); // default sort
$direction = request('direction', 'asc'); // default direction

// Only include customers with at least 1 PAID credit (payment_type = 'Cash')
$creditsQuery = Customer::whereHas('sales', fn($q) => $q->where('payment_type', 'Cash'));

// Role-based filtering
switch (strtolower($user->role)) {
    case 'owner':
        $creditsQuery->whereIn('branch_id', $userBranches->pluck('branch_id'));
        break;
    case 'admin':
        $creditsQuery->where('branch_id', $currentBranchId);
        break;
    case 'cashier':
        $creditsQuery->whereHas('sales', fn($q) => $q->where('created_by', $user->user_id));
        break;
    default:
        $creditsQuery->where('branch_id', $currentBranchId);
        break;
}

// Apply search on customer name
if ($search) {
    $creditsQuery->where('cust_name', 'like', "%{$search}%");
}

// Eager load only PAID sales and related products
$creditsQuery->with([
    'sales' => fn($q) => $q->where('payment_type', 'Cash')
                             ->with('sale_items.sale_itembelongsTobranch_product.product')
]);

// Apply sort (by customer column or aggregate sales column)
if (in_array($sortBy, ['cust_name', 'customer_id'])) {
    $creditsQuery->orderBy($sortBy, $direction);
} elseif ($sortBy === 'total_paid') {
    // Sort by total paid credits (sum of sales.total_amount)
    $creditsQuery->withSum('sales as total_paid', 'total_amount')
                 ->orderBy('total_paid', $direction);
}

$credits = $creditsQuery->get();

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

    <!-- Top: Clock + Date -->
    <div class="flex items-end justify-end">
        <div class="flex flex-col items-end">
            <span id="clock" class="text-xl font-semibold text-blue-600"></span>
            <span id="date" class="text-sm text-gray-500"></span>
        </div>
    </div>
</div>

<!-- Clock Script -->
<script>
    function updateClockAndDate() {
        const now = new Date();

        // Format time as 12-hour HH:MM:SS AM/PM
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12; // convert 0 to 12
        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;

        // Format date as Month Day, Year
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString(undefined, options);

        document.getElementById('clock').textContent = timeString;
        document.getElementById('date').textContent = dateString;
    }

    // Initial call
    updateClockAndDate();

    // Update every second
    setInterval(updateClockAndDate, 1000);
</script>










<!-- Logs Grid -->
<div class="grid grid-cols-1 gap-6 mt-20 sm:grid-cols-1 lg:grid-cols-2">

    <!-- Sales Log -->
    <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
        <div class="flex items-center space-x-3">
            <i class="text-xl text-green-600 fa-solid fa-cart-shopping"></i>
            <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">Sales Log</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">View all sales transactions.</p>
            </div>
        </div>
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'sales-log')" 
            class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Open
        </button>
    </div>

    <!-- Attendance Log -->
    <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
        <div class="flex items-center space-x-3">
            <i class="text-xl text-yellow-600 fa-solid fa-user-check"></i>
            <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">Attendance Log</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Check employee attendance records.</p>
            </div>
        </div>
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'attendance-log-modal')" 
            class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Open
        </button>
    </div>

    <!-- Credits Log -->
    <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
        <div class="flex items-center space-x-3">
            <i class="text-xl text-purple-600 fa-solid fa-coins"></i>
            <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">Credits Log</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review all credit transactions.</p>
            </div>
        </div>
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'credits-log-modal')" 
            class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Open
        </button>
    </div>

    <!-- Payroll Log -->
    <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
        <div class="flex items-center space-x-3">
            <i class="text-xl text-red-600 fa-solid fa-money-bill-wave"></i>
            <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">Payroll Log</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Access payroll and salary details.</p>
            </div>
        </div>
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'payroll-log-modal')" 
            class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Open
        </button>
    </div>

</div>





<!-- Sales Log -->
<!-- Sales Log Modal -->
<x-modal name="sales-log" :show="false" maxWidth="2xl">
    <div class="p-6 overflow-y-auto max-h-[80vh]">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-blue-900 dark:text-gray-100">
                Sales Log — {{ $currentBranchName }}
            </h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'sales-log')">
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Controls: Export + Search -->
        <div class="flex items-center justify-between mb-1">
            <!-- Export -->
            <div class="flex items-center mb-5 space-x-4">
                <button 
                    x-on:click="$dispatch('open-modal', 'export-sales')" 
                    class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                    <i class="fa-solid fa-download"></i>
                    <span class="hidden ml-2 lg:inline">Export</span>
                </button>
            </div>

            <!-- Search Bar --> 
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <div class="flex items-center px-2 py-1 border rounded w-25 sm:px-5 sm:py-1 md:px-3 md:py-2 sm:w-50 md:w-52">
                        <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            onkeydown="if(event.key==='Enter'){ 
                                const params = new URLSearchParams(window.location.search); 
                                params.set('search', this.value); 
                                window.location.href = window.location.pathname + '?' + params.toString(); 
                            }"
                            class="w-full py-0 text-sm bg-transparent border-none outline-none sm:py-0 md:py-1"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <!-- Sale ID -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'sale_id',
                                'direction' => request('sort_by') === 'sale_id' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Sale ID</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'sale_id')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <!-- Customer -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            Customer
                        </th>

                        <!-- Cashier -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            Cashier
                        </th>

                        <!-- Total Amount -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'total_amount',
                                'direction' => request('sort_by') === 'total_amount' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Total Amount</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'total_amount')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <!-- Payment Type -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'payment_type',
                                'direction' => request('sort_by') === 'payment_type' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Payment Type</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'payment_type')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <!-- Date -->
                        <th class="py-8 text-left text-gray-700 px-9 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'sale_date',
                                'direction' => request('sort_by') === 'sale_date' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Date</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'sale_date')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse ($sales as $sale)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $sale->sale_id }}</td>
                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $sale->salebelongsTocustomer?->cust_name ?? 'Walk-in' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $sale->salebelongsToUser?->username ?? 'Unknown' }}
                            </td> <!-- ✅ Added -->
                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                ₱{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $sale->payment_type }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $sale->sale_date?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-2">
                                <button 
                                    x-data 
                                    x-on:click="$dispatch('open-modal', 'view-sale-{{ $sale->sale_id }}')" 
                                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No sales found for this branch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end mt-4">
            <button 
                type="button" 
                class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                x-on:click="$dispatch('close-modal', 'sales-log')">
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Individual Sale Detail Modals -->
@foreach ($sales as $sale)
    <x-modal name="view-sale-{{ $sale->sale_id }}" :show="false" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                    Sale #{{ $sale->sale_id }} — 
                    {{ $sale->salebelongsTocustomer?->cust_name ?? 'Walk-in Customer' }}
                </h3>
                <button 
                    type="button"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    x-on:click="$dispatch('close-modal', 'view-sale-{{ $sale->sale_id }}')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @php
                $saleItems = $sale->sale_items()->with('sale_itembelongsTobranch_product.product')->get();
            @endphp

            <table class="w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Product</th>
                        <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Quantity</th>
                        <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Unit Price</th>
                        <th class="px-3 py-2 text-left text-gray-700 dark:text-gray-200">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach ($saleItems as $item)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item->sale_itembelongsTobranch_product?->product?->prod_name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item->quantity }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex justify-end mt-4">
                <button 
                    type="button"
                    class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                    x-on:click="$dispatch('close-modal', 'view-sale-{{ $sale->sale_id }}')">
                    Close
                </button>
            </div>
        </div>
    </x-modal>
@endforeach

<!-- Export -->
<x-modal name="export-sales" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4">

        <h2 class="text-lg font-semibold text-center text-gray-800">Export As</h2>

        <div class="flex justify-center mt-4 space-x-4">

            <!-- Excel -->
            <a href="{{ route('sales.export', [
                        'search' => request('search'),
                        'sort_by' => request('sort_by', 'sale_date'),
                        'direction' => request('direction', 'desc')
                    ]) }}"
               class="flex flex-col items-center w-24 px-4 py-3 transition bg-green-100 rounded-lg hover:bg-green-200">
                <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                <span class="text-sm text-gray-700">Excel</span>
            </a>

        </div>

        <!-- Cancel -->
        <div class="flex justify-center mt-6">
            <button 
                x-on:click="$dispatch('close-modal', 'export-sales')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>





<!-- Attendance -->
<!-- 🧾 Attendance Log Modal -->
<x-modal name="attendance-log-modal" :show="false" maxWidth="2xl">
    <div class="p-6 overflow-y-auto max-h-[80vh]">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-blue-900 dark:text-gray-100">
                Attendance Log — {{ $currentBranchName }}
            </h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'attendance-log-modal')">
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>
        
        
        <!-- Controls: Export + Search -->
        <div class="flex items-center justify-between mb-1">
            <!-- Export -->
            <div class="flex items-center mb-5 space-x-4">
                <button 
                    x-on:click="$dispatch('open-modal', 'export-attendance')" 
                    class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                    <i class="fa-solid fa-download"></i>
                    <span class="hidden ml-2 lg:inline">Export</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="flex items-center mb-4 space-x-2">
                <div class="flex items-center px-2 py-1 border rounded w-25 sm:px-5 sm:py-1 md:px-3 md:py-2 sm:w-50 md:w-52">
                    <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="att_search"
                        value="{{ request('att_search') }}"
                        placeholder="Search employee..."
                        onkeydown="if(event.key==='Enter'){ window.location.href='?att_search='+this.value+'&att_sort_by={{ request('att_sort_by','att_date') }}&direction={{ request('direction','desc') }}'; }"
                        class="w-full py-0 text-sm bg-transparent border-none outline-none sm:py-0 md:py-1"
                    />
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="?att_sort_by=attendance_id&direction={{ request('direction') === 'asc' ? 'desc' : 'asc' }}">
                                Attendance ID <i class="fa-solid fa-sort"></i>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="?att_sort_by=employee_name&direction={{ request('direction') === 'asc' ? 'desc' : 'asc' }}">
                                Employee <i class="fa-solid fa-sort"></i>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="?att_sort_by=branch_name&direction={{ request('direction') === 'asc' ? 'desc' : 'asc' }}">
                                Branch <i class="fa-solid fa-sort"></i>
                            </a>
                        </th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Date</th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($attendances as $att)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $att->attendance_id }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $att->attendancebelongsToemployee?->person?->firstname ?? '' }}
                                {{ $att->attendancebelongsToemployee?->person?->lastname ?? '' }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $att->attendancebelongsToemployee?->branch?->branch_name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $att->att_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $att->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end mt-4">
            <button 
                type="button" 
                class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                x-on:click="$dispatch('close-modal', 'attendance-log-modal')">
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Export Attendance -->
<x-modal name="export-attendance" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4">

        <h2 class="text-lg font-semibold text-center text-gray-800">Export As</h2>

        <div class="flex justify-center mt-4 space-x-4">

            <!-- Excel -->
            <a href="{{ route('attendance.export', [
                        'att_search' => request('att_search'),
                        'att_sort_by' => request('att_sort_by', 'att_date'),
                        'direction' => request('direction', 'desc')
                    ]) }}"
               class="flex flex-col items-center w-24 px-4 py-3 transition bg-green-100 rounded-lg hover:bg-green-200">
                <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                <span class="text-sm text-gray-700">Excel</span>
            </a>

        </div>

        <!-- Cancel -->
        <div class="flex justify-center mt-6">
            <button 
                x-on:click="$dispatch('close-modal', 'export-attendance')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>





<!-- ======================== CREDITS LOG MODAL ======================== -->
<x-modal name="credits-log-modal" :show="false" maxWidth="2xl">
    <div class="p-6 overflow-y-auto max-h-[80vh]">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-blue-900 dark:text-gray-100">
                Credits Log — {{ $currentBranchName }}
            </h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'credits-log-modal')">
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Controls: Export + Search -->
        <div class="flex items-center justify-between mb-1">
            <!-- Export -->
            <div class="flex items-center mb-5 space-x-4">
                <button 
                    x-on:click="$dispatch('open-modal', 'export-credits')" 
                    class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-purple-300 sm:text-xs md:text-xs lg:text-sm">
                    <i class="fa-solid fa-download"></i>
                    <span class="hidden ml-2 lg:inline">Export</span>
                </button>
            </div>

            <!-- Search Bar --> 
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <div class="flex items-center px-2 py-1 border rounded w-25 sm:px-5 sm:py-1 md:px-3 md:py-2 sm:w-50 md:w-52">
                        <i class="mr-2 text-purple-400 fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search..."
                            onkeydown="if(event.key==='Enter'){ 
                                const params = new URLSearchParams(window.location.search); 
                                params.set('search', this.value); 
                                window.location.href = window.location.pathname + '?' + params.toString(); 
                            }"
                            class="w-full py-0 text-sm bg-transparent border-none outline-none sm:py-0 md:py-1"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>

                        <!-- Customer Name -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'cust_name',
                                'direction' => request('sort_by') === 'cust_name' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Customer</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'cust_name')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <!-- Total Paid Credits -->
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort_by' => 'total_credit',
                                'direction' => request('sort_by') === 'total_credit' && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="flex items-center space-x-1">
                                <span>Total Paid Credits</span>
                                <i class="fa-solid 
                                    @if(request('sort_by') === 'total_credit')
                                        {{ request('direction') === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}
                                    @else
                                        fa-sort
                                    @endif"></i>
                            </a>
                        </th>

                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse ($credits as $customer)
                        @php
                            $totalPaid = $customer->sales->sum('total_amount');
                            $nextDue = $customer->sales->sortBy('due_date')->first()?->due_date;
                        @endphp
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $customer->cust_name }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($totalPaid, 2) }}</td>
                            <td class="px-4 py-2">
                                <button x-data 
                                        x-on:click="$dispatch('open-modal', 'view-credit-{{ $customer->customer_id }}')"
                                        class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No paid credits found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-4">
            <button type="button" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                x-on:click="$dispatch('close-modal', 'credits-log-modal')">
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Export Credits Modal -->
<x-modal name="export-credits" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4">

        <h2 class="text-lg font-semibold text-center text-gray-800">Export Credits As</h2>

        <div class="flex justify-center mt-4 space-x-4">

            <!-- Excel -->
            <a href="{{ route('credits.export', [
                        'search' => request('search'),
                        'sort_by' => request('sort_by', 'sale_date'),
                        'direction' => request('direction', 'desc')
                    ]) }}"
               class="flex flex-col items-center w-24 px-4 py-3 transition bg-green-100 rounded-lg hover:bg-green-200">
                <i class="mb-1 text-2xl text-green-600 fa-solid fa-file-excel"></i>
                <span class="text-sm text-gray-700">Excel</span>
            </a>

        </div>

        <!-- Cancel -->
        <div class="flex justify-center mt-6">
            <button 
                x-on:click="$dispatch('close-modal', 'export-credits')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>

<!-- ======================== INDIVIDUAL CREDIT VIEW MODALS ======================== -->
@foreach ($credits as $customer)
    <x-modal name="view-credit-{{ $customer->customer_id }}" :show="false" maxWidth="2xl">
        <div class="p-6 overflow-y-auto max-h-[80vh]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-blue-800 dark:text-gray-100">
                    {{ $customer->cust_name }} — Paid Credits
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    x-on:click="$dispatch('close-modal', 'view-credit-{{ $customer->customer_id }}')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @foreach ($customer->sales as $sale)
                <h4 class="mt-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Sale #{{ $sale->sale_id }} — ₱{{ number_format($sale->total_amount,2) }} | Date: {{ $sale->sale_date?->format('Y-m-d') }}
                </h4>
                <table class="w-full mb-4 text-sm border-collapse table-auto">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-left">Quantity</th>
                            <th class="px-3 py-2 text-left">Unit Price</th>
                            <th class="px-3 py-2 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach ($sale->sale_items as $item)
                            <tr class="bg-white dark:bg-gray-800">
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item->sale_itembelongsTobranch_product?->product?->prod_name ?? 'N/A' }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            <div class="flex justify-end mt-4">
                <button type="button" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                    x-on:click="$dispatch('close-modal', 'view-credit-{{ $customer->customer_id }}')">
                    Close
                </button>
            </div>
        </div>
    </x-modal>
@endforeach






<!-- Footer Branding -->
<footer class="py-4 text-sm text-center text-gray-400 border-t mt-15">
    © 2025 KitaKeeps. All rights reserved.
</footer>