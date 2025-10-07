@php
    $user = auth()->user();

    // Get all branch IDs the user owns
    $userBranchIds = $user->branches()->pluck('branch.branch_id')->toArray();

    // Top 5 products by total qty sold (across all user's branches)
    $topProducts = \App\Models\BranchProduct::with('product')
        ->withSum('branch_producthasManysale_item as total_sold', 'quantity')
        ->whereIn('branch_id', $userBranchIds)
        ->orderByDesc('total_sold')
        ->take(5)
        ->get();

    // Top 5 branches by total sales amount
    $topBranches = \App\Models\Branch::selectRaw('branch.branch_id, branch.branch_name, COALESCE(SUM(sale.total_amount), 0) as total_sales')
        ->leftJoin('sale', 'branch.branch_id', '=', 'sale.branch_id')
        ->whereIn('branch.branch_id', $userBranchIds) // only branches of this user
        ->groupBy('branch.branch_id', 'branch.branch_name')
        ->orderByDesc('total_sales')
        ->take(5)
        ->get();
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





<div class="flex mb-10 space-x-5">
    {{-- Top 5 Products --}}
    <div class="flex-1 p-6 mt-10 bg-white rounded-lg shadow min-w-[350px]">
        <h2 class="mb-6 text-xl font-semibold text-gray-700">Top 5 Products by Sales</h2>
        <div class="space-y-6">
            @foreach ($topProducts as $product)
            @php
                $percentage = $topProducts->first()->total_sold > 0 
                    ? round($product->total_sold / $topProducts->first()->total_sold * 100)
                    : 0;
            @endphp
            <div class="flex items-center space-x-4">
                <span class="w-24 text-gray-600">{{ $product->product->prod_name ?? 'Unknown' }}</span>
                <div class="relative flex-1 h-6 bg-blue-200 rounded-full">
                    <div class="h-6 bg-blue-600 rounded-full" style="width: {{ $percentage }}%;"></div>
                </div>
                <span class="w-12 text-right text-gray-700">{{ $product->total_sold }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top 5 Stores/Branches --}}
    <div class="flex-1 p-6 mt-10 bg-white rounded-lg shadow min-w-[350px]">
        <h2 class="mb-6 text-xl font-semibold text-gray-700">Top 5 Stores by Sales</h2>
        <div class="space-y-3">
            @foreach ($topBranches as $branch)
            @php
                $maxSales = $topBranches->first()->total_sales ?? 1;
                $widthPercentage = round($branch->total_sales / $maxSales * 100);
            @endphp
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $branch->branch_name }}</span>
                    <span class="text-sm font-medium text-gray-700">{{ number_format($branch->total_sales) }}</span>
                </div>
                <div class="w-full h-4 bg-gray-200 rounded-full">
                    <div class="h-4 bg-blue-500 rounded-full" style="width: {{ $branch->total_sales ? ($branch->total_sales / $topBranches->first()->total_sales * 100) : 0 }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="p-6 bg-white rounded shadow">
    <h2 class="mb-4 text-xl font-semibold">AI Forecast Summary</h2>

    <pre class="text-gray-700 whitespace-pre-wrap"></pre>
</div>


<!-- Footer Branding -->
<footer class="py-4 text-sm text-center text-gray-400 border-t mt-15">
    © 2025 KitaKeeps. All rights reserved.
</footer>