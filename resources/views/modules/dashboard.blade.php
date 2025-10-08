@php
    use Carbon\Carbon;
    use App\Models\BranchProduct;
    use App\Models\Product;
    use App\Models\User;
    use App\Models\Sale;
    use App\Models\Employee;
    use App\Models\Attendance;
    use App\Models\SaleItem;

    // --- Branch-awareness ---
    $owner = auth()->user();
    $userBranches = $owner ? $owner->branches : collect();
    $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first()
                ?? $userBranches->sortBy('branch_id')->first();
    $branchId = $currentBranch ? $currentBranch->branch_id : null;

    // --- Date ranges ---
    $now = Carbon::now();
    $startOfWeek = $now->copy()->startOfWeek();
    $startOfLastWeek = $startOfWeek->copy()->subWeek();
    $endOfLastWeek = $startOfWeek->copy()->subDay();

    // --- Helper for percentage change ---
    function percentChange($current, $previous) {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    // --- Metrics ---

    // Total inventory value (from BranchProduct × Product)
    $totalInventoryValue = BranchProduct::where('branch_id', $branchId)
        ->join('product', 'branch_product.product_id', '=', 'product.product_id')
        ->sum(\DB::raw('branch_product.stock_qty * product.selling_price'));

    // Low stock count (1–19)
    $lowStockCount = BranchProduct::where('branch_id', $branchId)
        ->whereBetween('stock_qty', [1, 19])
        ->count();

    // Sold out count
    $soldOutCount = BranchProduct::where('branch_id', $branchId)
        ->where('stock_qty', 0)
        ->count();

    // Total number of products (for percentage calculations)
    $totalProducts = BranchProduct::where('branch_id', $branchId)->count();

    // Avoid division by zero
    $lowStockPercent = $totalProducts > 0 ? ($lowStockCount / $totalProducts) * 100 : 0;
    $soldOutPercent = $totalProducts > 0 ? ($soldOutCount / $totalProducts) * 100 : 0;

    // --- Weekly percentage changes (simulated or derived) ---
    $currentWeekSales = Sale::where('branch_id', $branchId)
        ->whereBetween('sale_date', [$startOfWeek, $now])
        ->sum('total_amount');

    $lastWeekSales = Sale::where('branch_id', $branchId)
        ->whereBetween('sale_date', [$startOfLastWeek, $endOfLastWeek])
        ->sum('total_amount');

    $inventoryChange = percentChange($currentWeekSales, $lastWeekSales);

    // --- Today's date ---
    $today = Carbon::today()->toDateString();

    // --- Active employees today ---
    $activeEmployeesToday = Employee::where('branch_id', $branchId)
        ->whereHas('attendance', function ($query) use ($today) {
            $query->whereDate('att_date', $today)
                ->where('status', 'present');
        })
        ->count();

    // --- Active employees this week ---
    $startOfWeek = Carbon::now()->startOfWeek();
    $endOfWeek = Carbon::now()->endOfWeek();

    $activeEmployeesThisWeek = Employee::where('branch_id', $branchId)
        ->whereHas('attendance', function ($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('att_date', [$startOfWeek, $endOfWeek])
                ->where('status', 'present');
        })
        ->distinct('employee_id')
        ->count();

    // --- Inventory totals ---
    $totalStockQty = BranchProduct::where('branch_id', $branchId)->sum('stock_qty');
    $totalSoldQty = SaleItem::whereHas('sale_itembelongsTosale', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->sum('quantity');

    // --- Calculate percentages ---
    $totalUnits = $totalStockQty + $totalSoldQty;
    $soldPercent = $totalUnits > 0 ? round(($totalSoldQty / $totalUnits) * 100, 1) : 0;
    $availablePercent = 100 - $soldPercent;

    // Graph 
    // --- Prepare last 7 days ---
    $dates = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));

    // --- Collect data for each day ---
    $salesData = $dates->map(function ($date) use ($branchId) {
        // Get all sales for this branch and date
        $sales = Sale::where('branch_id', $branchId)
            ->whereDate('sale_date', $date)
            ->get();

        $saleIds = $sales->pluck('sale_id');

        // Total sales amount
        $totalSales = $sales->sum('total_amount');

        // Calculate income (profit)
        $items = SaleItem::whereIn('sale_id', $saleIds)
            ->with('sale_itembelongsTobranch_product.product') // ✅ chain to reach product through branch_product
            ->get();

        $income = $items->sum(function ($item) {
            $product = $item->sale_itembelongsTobranch_product?->product;
            if (!$product) return 0;

            $selling = $product->selling_price ?? 0;
            $cost = $product->unit_cost ?? 0;
            return ($selling - $cost) * ($item->quantity ?? 0);
        });

        return [
            'date' => $date->format('M d'),
            'sales' => $totalSales,
            'income' => $income,
        ];
    });

    // --- Prepare data for chart ---
    $labels = $salesData->pluck('date')->toArray();
    $salesValues = $salesData->pluck('sales')->toArray();
    $incomeValues = $salesData->pluck('income')->toArray();
@endphp

<!-- Module Header -->
<div class="flex items-center justify-between">

    <!-- Top: Current Branch -->
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










<!-- Summary -->
<div class="overflow-x-auto table-pretty-scrollbar">
    <div class="flex gap-6 p-6 mt-1 min-w-max">

        <!-- Total Inventory Value -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[200px]">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Total Inventory Value</span>
        </div>
        <h2 class="text-2xl font-bold text-blue-500">₱{{ number_format($totalInventoryValue, 2) }}</h2>
        <p class="mt-1 text-sm {{ $inventoryChange >= 0 ? 'text-green-500' : 'text-red-500' }}">
            {{ $inventoryChange >= 0 ? '▲' : '▼' }} {{ abs($inventoryChange) }}%
            <span class="text-gray-500">this week</span>
        </p>
        </div>

        <!-- Low Stock -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[200px]">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Low Stock</span>
        </div>
        <h2 class="text-2xl font-bold text-yellow-500">{{ $lowStockCount }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ round($lowStockPercent, 1) }}%
            <span class="text-gray-500">of inventory</span>
        </p>
        </div>

        <!-- Sold Out -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[200px]">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Sold Out</span>
        </div>
        <h2 class="text-2xl font-bold text-red-500">{{ $soldOutCount }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ round($soldOutPercent, 1) }}%
            <span class="text-gray-500">of inventory</span>
        </p>
        </div>

        <!-- Active Employees -->
        <div class="flex flex-col p-5 bg-white shadow-md rounded-2xl min-w-[200px]">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Active Employees</span>
        </div>
        <h2 class="text-2xl font-bold text-green-500">{{ $activeEmployeesToday }}</h2>
        <p class="mt-1 text-sm">
            {{ $activeEmployeesThisWeek }}
            <span class="text-gray-500">this week</span>
        </p>
        </div>
    </div>
</div>








<!-- Charts & Graphs -->
<div class="flex flex-col mt-5 mb-20 space-x-0 space-y-5 lg:justify-center lg:flex-row lg:space-x-5 lg:space-y-0">

    <!-- Pie Chart -->
    <div class="order-2 lg:w-[250px] p-5 bg-white shadow-md rounded-2xl lg:order-1">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">Inventory Values</h3>

        <div class="flex items-center justify-center">
            <!-- Pie Chart -->
            <div class="relative h-24 rounded-full w-28"
                style="background: conic-gradient(#1e3a8a 0% {{ $availablePercent }}%, #cbd5e1 {{ $availablePercent }}% 100%);">
                <!-- Percent Labels -->
                <span class="absolute text-xs font-semibold text-white bottom-[25%] right-[30%]">
                    {{ $availablePercent }}%
                </span>
                <span class="absolute text-xs font-semibold text-gray-800 top-[25%] left-[20%]">
                    {{ $soldPercent }}%
                </span>
            </div>

            <!-- Legend -->
            <div class="ml-6 space-y-2 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded bg-slate-300"></div>
                    <span class="text-xs text-gray-600">Sold units</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-blue-900 rounded"></div>
                    <span class="text-xs text-gray-600">Available units</span>
                </div>
            </div>
        </div>

        <p class="mt-5 text-sm text-gray-500">
            {{ $soldPercent }}% of total units have been sold, leaving {{ $availablePercent }}% still available.
        </p>
    </div>

    <!-- Graph -->
    <div class="p-4 bg-white shadow-md rounded-2xl w-full lg:w-[500px] order-3 lg:order-2">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-700">Sales vs Income</h3>
            <span class="text-xs text-gray-500">Last 7 Days</span>
        </div>

        <!-- Chart -->
        <div class="relative w-full h-48">
            @php
                // Normalize chart values for SVG scaling
                $maxValue = max(max($salesValues ?: [0]), max($incomeValues ?: [0]), 1);
                $width = 450;
                $height = 160;

                $xStep = $width / max(count($labels) - 1, 1);

                $salesPoints = '';
                $incomePoints = '';

                foreach ($salesValues as $i => $value) {
                    $x = $i * $xStep;
                    $y = $height - (($value / $maxValue) * $height);
                    $salesPoints .= "$x,$y ";
                }

                foreach ($incomeValues as $i => $value) {
                    $x = $i * $xStep;
                    $y = $height - (($value / $maxValue) * $height);
                    $incomePoints .= "$x,$y ";
                }
            @endphp

            <svg viewBox="0 0 {{ $width }} {{ $height + 40 }}" class="w-full h-full">
                <!-- Grid Lines -->
                <g stroke="#e5e7eb" stroke-width="1">
                    @for ($i = 1; $i <= 4; $i++)
                        <line x1="0" y1="{{ $i * ($height / 4) }}" x2="{{ $width }}" y2="{{ $i * ($height / 4) }}" />
                    @endfor
                </g>

                <!-- Sales Line (Orange) -->
                <polyline
                    fill="none"
                    stroke="#f97316"
                    stroke-width="2"
                    points="{{ trim($salesPoints) }}" />
                
                <!-- Income Line (Violet) -->
                <polyline
                    fill="none"
                    stroke="#7c3aed"
                    stroke-width="2"
                    points="{{ trim($incomePoints) }}" />

                <!-- Labels -->
                @foreach ($labels as $i => $label)
                    <text x="{{ $i * $xStep }}" y="{{ $height + 20 }}" font-size="10" fill="#000000ff" text-anchor="middle">
                        {{ $label }}
                    </text>
                @endforeach
            </svg>

            <!-- Y-axis labels -->
            <div class="absolute top-0 left-0 flex flex-col justify-between text-xs text-gray-500" style="height: {{ $height }}px;">
                <span>{{ number_format($maxValue, 0) }}</span>
                <span>{{ number_format($maxValue * 0.75, 0) }}</span>
                <span>{{ number_format($maxValue * 0.5, 0) }}</span>
                <span>{{ number_format($maxValue * 0.25, 0) }}</span>
                <span>0</span>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex justify-center gap-4 mt-2 text-xs text-gray-600">
            <div class="flex items-center space-x-1">
                <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                <span>Sales</span>
            </div>
            <div class="flex items-center space-x-1">
                <span class="w-3 h-3 rounded-full bg-violet-600"></span>
                <span>Income</span>
            </div>
        </div>
    </div>

    <!-- Quick Access Buttons -->
    <div class="order-1 p-3 bg-white rounded-lg shadow-md lg:order-3">
        <div class="flex flex-row space-x-5 lg:flex-col lg:space-x-0 lg:space-y-5">

            <button x-on:click="$dispatch('open-modal', 'add-product')" class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                <i class="fa-solid fa-clipboard-list"></i>
            </button>
            <button x-on:click="$dispatch('open-modal', 'add-supplier')" class="w-full px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700">
                <i class="fa-solid fa-truck"></i>
            </button>
            <button x-on:click="$dispatch('open-modal', 'add-employee')" class="w-full px-4 py-2 text-white bg-purple-600 rounded-md hover:bg-purple-700">
                <i class="fa-solid fa-users"></i>
            </button>
            <button x-on:click="$dispatch('open-modal', 'add-customer')" class="w-full px-4 py-2 text-white bg-orange-600 rounded-md hover:bg-orange-700">
                <i class="fa-solid fa-users-line"></i>
            </button>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<x-modal name="add-product" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Modal Header -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-box"></i>
                <h2 class="text-xl font-semibold">Add New Product</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-product')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm">
            @csrf

            <!-- Product Image (Circle Placeholder) -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="preview-product" src="assets/images/logo/logo-removebg-preview.png" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="Product photo">

                    <!-- Upload button -->
                    <label for="product_image" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>
                    <input type="file" id="product_image" name="product_image" class="hidden" accept="image/*"
                        onchange="document.getElementById('preview-product').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <p class="mt-2 text-sm text-gray-500">Add product photo</p>
            </div>

            <!-- Basic Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Product Information</legend>
                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Product Name -->
                    <div>
                        <label class="block mb-1 text-gray-800">Product Name</label>
                        <input required name="prod_name" type="text" placeholder="Paint" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500"/>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block mb-1 text-gray-800">Category</label>
                        <select required name="category" id="category" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500">
                            <option value="">Select category</option>
                            <option value="Building Materials">Building Materials</option>
                            <option value="Construction Materials">Construction Materials</option>
                            <option value="Decor">Decor</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Garden & Landscaping">Garden & Landscaping</option>
                            <option value="Paints & Finishes">Paints & Finishes</option>
                            <option value="Plumbing & Sanitary">Plumbing & Sanitary</option>
                            <option value="Security & Safety">Security & Safety</option>
                            <option value="Tools">Tools</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Description</label>
                        <textarea name="prod_description" rows="3" placeholder="Write product details..." class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500"></textarea>
                    </div>

                    <!-- Product Supplier -->
                    <div>
                        <label for="supplier" class="block mb-1 text-gray-800">Product Supplier</label>
                        <select required name="supplier" id="supplier" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500">
                            <option value="">Select a supplier</option>
                            @foreach($userSuppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}">{{ $supplier->supp_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Quantity -->
                    <div>
                        <label class="block mb-1 text-gray-800">Stock Quantity</label>
                        <input required type="number" name="quantity" placeholder="143" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500"/>
                    </div>

                </div>
            </fieldset>

            <!-- Pricing -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Pricing</legend>
                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Unit Cost -->
                    <div>
                        <label class="block mb-1 text-gray-800">Unit Cost</label>
                        <input required type="number" name="unit_cost" placeholder="100" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500"/>
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label class="block mb-1 text-gray-800">Selling Price</label>
                        <input required type="number" name="selling_price" placeholder="150" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500"/>
                    </div>

                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-product')"
                class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>

                <button type="submit" 
                class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Save</button>
            </div>
        </form>
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
                        <input required type="text" name="supp_contact" placeholder="+63 912 345 6789" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
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

<!-- Hire Employee Modal -->
<x-modal name="add-employee" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="mr-2 fa-solid fa-user-plus"></i>
                <h2 class="text-xl font-semibold">Hire Employee</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-employee')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        @if ($errors->any())
            <div class="p-2 mb-2 text-red-700 bg-red-100 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Form -->
        <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm"
            x-data="{ position: '' }">
            @csrf <!-- Laravel CSRF -->
            
            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="preview-employee" src="assets/images/logo/logo-removebg-preview.png" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="Employee photo">

                    <!-- Upload button -->
                    <label for="employee_image" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-blue-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>
                    <input type="file" id="employee_image" name="employee_image_path" class="hidden" accept="image/*"
                        onchange="document.getElementById('preview-employee').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <p class="mt-2 text-sm text-gray-500">Add profile photo</p>
            </div>

            <!-- Personal Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Personal Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                <!-- First Name -->
                <div>
                    <label class="block mb-1 text-gray-800">First Name</label>
                    <input required type="text" name="firstname" placeholder="Kita" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block mb-1 text-gray-800">Last Name</label>
                    <input required type="text" name="lastname" placeholder="Keeper" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block mb-1 text-gray-800">Gender</label>
                    <select required name="gender" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block mb-1 text-gray-800">Contact Number</label>
                    <input required name="contact_number" type="text" placeholder="+63 912 345 6789" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Email -->
                <div class="sm:col-span-2">
                    <label class="block mb-1 text-gray-800">Email</label>
                    <input required type="email" name="email" placeholder="example@email.com" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label class="block mb-1 text-gray-800">Address</label>
                    <input required type="text" name="address" placeholder="123 Main St, City" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                </div>
            </fieldset>

            <!-- Job Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Job Information</legend>
                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Position -->
                    <div>
                        <label class="block mb-1 text-gray-800">Position</label>
                        <input required type="text" name="position" placeholder="Cashier"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                            x-model="position">
                    </div>

                    <!-- Daily Salary -->
                    <div>
                        <label class="block mb-1 text-gray-800">Daily Salary</label>
                        <input required type="number" name="daily_rate" placeholder="500" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
                
                <div x-show="position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin'" class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Username -->
                    <div>
                        <label class="block mb-1 text-gray-800">Username</label>
                        <input type="text" name="username" placeholder="e.g., john.doe"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-1 text-gray-800">Password</label>
                        <input type="password" name="password" placeholder="Enter password"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-employee')"
                class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>

                <button type="submit" 
                class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Save</button>
            </div>
        </form>

    </div>
</x-modal>

<!-- Add Customer -->
<x-modal name="add-customer" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center space-x-2">
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
                        <input required type="text" name="cust_contact" placeholder="+63 912 345 6789"
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
<footer class="py-4 text-sm text-center text-gray-400 border-t mt-15">
    © 2025 KitaKeeps. All rights reserved.
</footer>