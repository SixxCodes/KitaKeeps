@php
    use App\Models\Branch;

    $user = auth()->user();
    $branches = $user->branches()->get();

    // Determine main branch ID (first branch created by this owner)
    $mainBranchId = $branches->sortBy('branch_id')->first()?->branch_id;
@endphp

@php
    use App\Models\Supplier;
    $suppliers = Supplier::all();
@endphp

@php
    use App\Models\Employee;

    $owner = Auth::user();

    // Determine current branch
    $userBranches = $owner->branches;
    $perPage = request()->query('per_page', 5);
    $search  = request()->query('search');
    $role    = request()->query('role', 'all'); // <-- role filter

    $mainBranch = $userBranches->sortBy('branch_id')->first();
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
        ?? $mainBranch;

    if (!$currentBranch) {
        // handle gracefully in view
        $employees = collect();
    } else {
        $employees = Employee::with('person.user')
            ->where(function($query) use ($currentBranch) {
                // Login employees → filtered via user branches
                $query->whereHas('person.user.branches', function($q) use ($currentBranch) {
                    $q->where('user_branch.branch_id', $currentBranch->branch_id);
                })
                // Non-login employees → filter by branch_id directly
                ->orWhere(function($q) use ($currentBranch) {
                    $q->whereNotIn('position', ['Cashier', 'Admin'])
                    ->where('branch_id', $currentBranch->branch_id);
                });
            })
            ->when($search, function($query, $search) {
                $query->whereHas('person', function($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname',  'like', "%{$search}%")
                    ->orWhere('email',     'like', "%{$search}%");
                });
            })
            ->when($role != 'all', function($query) use ($role) {
                if ($role == 'Other') {
                    $query->whereNotIn('position', ['Cashier', 'Admin']);
                } else {
                    $query->where('position', $role);
                }
            })
            ->orderBy('employee_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }
@endphp


<!-- My Inventory Index -->
@php
    use App\Models\Product;

    $owner = auth()->user();

    // get current branch from session (or fallback to first branch)
    $userBranches = $owner->branches; 
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
        ?? $userBranches->sortBy('branch_id')->first();

    // Get query parameters
    $search = request('search');
    $perPage = request('per_page', 5);
    $status = request('status', 'all');

    $products = Product::with([
            'product_supplier.supplier',
            'branch_products' => function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->branch_id);
            }
        ])
        ->whereHas('branch_products', function($q) use ($currentBranch, $status) {
            $q->where('branch_id', $currentBranch->branch_id);

            // Status filter
            if ($status == 'in_stock') {
                $q->where('stock_qty', '>=', 20);
            } elseif ($status == 'low_stock') {
                $q->whereBetween('stock_qty', [1, 19]);
            } elseif ($status == 'out_of_stock') {
                $q->where('stock_qty', 0);
            }
        })
        ->when($search, function ($q) use ($search) {
            $q->where('prod_name', 'like', "%{$search}%");
        })
        ->orderBy('product_id', 'desc')
        ->paginate($perPage)
        ->withQueryString();
@endphp

@php
    use Illuminate\Support\Facades\Auth;

    $owner = Auth::user();

    // get current branch from session (or fallback to first branch)
    $userBranches = $owner->branches; 
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
        ?? $userBranches->sortBy('branch_id')->first();

    $selectedCategory = request()->query('category');

    if ($currentBranch) {
        // ✅ distinct categories pulled directly from products table
        $categories = Product::whereHas('branch_products', function ($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->branch_id);
            })
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // For POS (all products, no pagination)
        $posProducts = Product::with([
                'product_supplier.supplier',
                'branch_products' => function($q) use ($currentBranch) {
                    $q->where('branch_id', $currentBranch->branch_id);
                }
            ])
            ->whereHas('branch_products', function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->branch_id);
            })
            ->when($selectedCategory, function ($q) use ($selectedCategory) {
                $q->where('category', $selectedCategory);
            })
            ->orderBy('product_id', 'asc')
            ->get();

        // Get suppliers for branches the user owns
        $userSuppliers = Supplier::whereIn('branch_id', $userBranches->pluck('branch_id'))->get();
    } else {
        $categories = collect();
        $posProducts = collect();
        $userSuppliers = collect();
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href="assets/images/logo/logo-removebg-preview.png" type="image/x-icon">

        <title>KitaKeeps</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome’s icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased table-pretty-scrollbar">
    <div id="sidebar-app" class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div id="sidebar" 
            :class="[
                'w-64 overflow-x-hidden overflow-y-auto text-white bg-gray-900 hover-scroll transition-transform duration-300',
                { 
                    '-translate-x-full fixed md:static top-0 left-0 z-40 h-full md:h-screen': !isSidebarOpen && isMobile,
                    'fixed md:static top-0 left-0 z-40 h-full md:h-screen': isSidebarOpen && isMobile
                }
            ]">
            @include('layouts.sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-h-screen overflow-x-hidden overflow-y-auto pb-15">
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Page Content -->
            <main class="flex-1 h-screen mx-10 my-6">
                <!-- HOME Section-->
                <div v-if="currentPage === 'Dashboard'">
                    @include('modules.dashboard') 
                </div>
                <div v-else-if="currentPage === 'POS'">
                    @include('modules.pos') 
                </div>

                <!-- BUSINESS INTELLIGENCE Section -->
                 <div v-else-if="currentPage === 'Reports'">
                    @include('modules.reports') 
                </div>
                <div v-else-if="currentPage === 'Analytics &amp; Forecasting'">
                    @include('modules.reportsAndAnalytics') 
                </div>

                <!-- MANAGEMENT Section -->
                <div v-else-if="currentPage === 'My Hardware'">
                    @include('modules.myHardware') 
                </div>
                <div v-else-if="currentPage === 'My Inventory'">
                    @include('modules.myInventory') 
                </div>
                <div v-else-if="currentPage === 'My Suppliers'">
                    @include('modules.mySuppliers') 
                </div>
                <div v-else-if="currentPage === 'My Employees'">
                    @include('modules.myEmployees') 
                </div>
                <div v-else-if="currentPage === 'My Customers'">
                    @include('modules.myCustomers') 
                </div>

                <!-- Bottom Button(s)-->
                <div v-else-if="currentPage === 'Settings'">
                    @include('modules.settings') 
                </div>
            </main>
        </div>
    </div>
    </body>

</html>
