<!-- Products -->
@php
    use App\Models\Product;
    use App\Models\Customer;

    // Authenticated user
    $owner = auth()->user();
    $userBranches = $owner ? $owner->branches : collect();

    // Determine current branch: session -> first branch -> null
    $currentBranch = null;

    if (session()->has('current_branch_id')) {
        $currentBranch = $userBranches->where('branch_id', session('current_branch_id'))->first();
    }

    // Fallback to first branch if session missing or invalid
    if (!$currentBranch) {
        $currentBranch = $userBranches->sortBy('branch_id')->first();
    }

    // Selected category from query string (?category=...)
    $selectedCategory = request('category');

    // ------------------- Products -------------------
    // Only query if a branch is available
    $posProducts = collect();
    $categories  = collect();

    if ($currentBranch) {
        // Distinct categories for this branch
        $categories = Product::whereHas('branch_products', function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->branch_id);
            })
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Products for POS, filtered by branch + optional category + search
        $posProducts = Product::with([
                'product_supplier.supplier',
                'branch_products' => function($q) use ($currentBranch) {
                    $q->where('branch_id', $currentBranch->branch_id);
                }
            ])
            ->whereHas('branch_products', function($q) use ($currentBranch) {
                $q->where('branch_id', $currentBranch->branch_id);
            })
            ->when($selectedCategory, function($q) use ($selectedCategory) {
                $q->where('category', $selectedCategory);
            })
            ->when(request('pos_search'), function ($q, $posSearch) {
                $q->where('prod_name', 'like', "%{$posSearch}%");
            })
            ->orderBy('product_id', 'asc')
            ->get();
    }

    // ------------------- Customers -------------------
    $perPage = (int) request('per_page', 5);
    $search  = trim(request('search', ''));

    $query = Customer::query();

    // Only scope to branch if branch exists
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

    // Paginate customers
    $customers = $query->orderBy('customer_id', 'desc')
        ->paginate($perPage)
        ->appends(request()->query());
@endphp

<!-- Module Header -->
<div class="flex items-center justify-between">

    @if($errors->any())
        <div class="p-2 mt-2 text-sm text-red-700 bg-red-100 rounded">
            {{ $errors->first() }}
        </div>
    @endif

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

    <!-- Search Bar --> 
    <form method="GET" action="{{ url()->current() }}" class="flex items-center space-x-2">
        <i class="text-blue-800 fa-solid fa-filter"></i>
        <div class="flex items-center px-2 py-1 bg-white rounded shadow w-25 sm:px-5 sm:py-1 md:px-3 md:py-2 sm:w-50 md:w-52">
            <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
            <input
                type="text" 
                name="pos_search"
                value="{{ request('pos_search') }}"
                placeholder="Search..." 
                class="w-full py-0 text-sm bg-white border-none outline-none sm:py-0 md:py-1"
            />
        </div>
    </form>
</div>








<!-- POS (Point of Sale) -->
<div class="flex flex-col mt-8 space-y-6 lg:flex-row lg:space-x-2 lg:space-y-0">

    <!-- Left: Product Listing -->
    <div class="w-full lg:w-2/3">
        <!-- Categories -->
        <div class="flex pb-2 space-x-2 overflow-x-auto whitespace-nowrap">
            <a href="{{ url()->current() }}" 
            class="whitespace-nowrap px-4 py-1 text-sm rounded-full {{ request('category') ? 'bg-white' : 'bg-blue-500 text-white' }}">
                All
            </a>

            @foreach($categories as $category)
                <a href="{{ url()->current() }}?category={{ urlencode($category) }}" 
                class="whitespace-nowrap px-4 py-1 text-sm rounded-full {{ request('category') == $category ? 'bg-blue-500 text-white' : 'bg-white' }}">
                    {{ $category }}
                </a>
            @endforeach
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 gap-4 mt-4 overflow-y-auto sm:grid-cols-3 md:grid-cols-4" style="max-height: 75vh;">
            @forelse($posProducts as $product)

                @php
                    $branchProduct = $product->branch_products->first();
                @endphp

                <!-- Card -->
                <div 
                    class="relative p-4 bg-white rounded shadow hover:shadow-lg hover:bg-gray-100
                        {{ ($branchProduct->stock_qty ?? 0) <= 0 ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}"
                    @if(($branchProduct->stock_qty ?? 0) > 0)
                        onclick="addToCart(
                            {{ $branchProduct->branch_product_id }},
                            '{{ $product->prod_name }}',
                            {{ $branchProduct->stock_qty ?? 0 }},
                            {{ $product->selling_price }},
                            '{{ $product->prod_image_path ? asset('storage/'.$product->prod_image_path) : '' }}',
                            '{{ $product->product_supplier->first()?->supplier?->supp_name ?? 'N/A' }}'
                        )"
                    @endif
                >
                    <div class="relative flex items-center justify-center w-full h-24 bg-blue-300 rounded">
                        @if($product->prod_image_path)
                            <img src="{{ asset('storage/'.$product->prod_image_path) }}" 
                                alt="{{ $product->product_name }}" 
                                class="object-cover w-full h-full rounded">
                        @else
                            <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                <i class="fa-solid fa-box fa-2x"></i>
                            </div>
                        @endif

                        {{-- Sold Out Overlay --}}
                        @if(($branchProduct->stock_qty ?? 0) <= 0)
                            <div class="absolute inset-0 flex items-center justify-center bg-black rounded bg-opacity-60">
                                <span class="text-sm font-bold text-white">SOLD OUT</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-2 text-sm font-bold">{{ $product->prod_name }}</div>
                    <div class="text-xs text-gray-500">{{ $product->product_supplier->first()?->supplier?->supp_name ?? 'N/A' }}</div>
                    <div class="text-xs text-gray-500">Stocks: {{ $product->branch_products->first()?->stock_qty ?? 0 }}</div>
                    <div class="mt-1 font-semibold">₱{{ number_format($product->selling_price, 2) }}</div>
                </div>
            @empty
            <div class="col-span-4 px-3 py-2 text-center">
                <span class="text-gray-500">Nothing to see here yet.</span>
            </div>
            @endforelse

        </div>
    </div>
    
    <!-- Right: Order Details -->
    <div class="flex flex-col justify-between w-full p-4 bg-white rounded shadow lg:w-1/3">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Order Details</h2>
                <div class="flex justify-end">
                    <button 
                        class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                        onclick="clearAllCart()">
                        Clear All
                    </button>
                </div>
            </div>

            <div class="flex flex-col">
                <!-- Order Items -->
                <div id="order-items" class="pr-1 space-y-2 overflow-y-auto max-h-80"></div>
            </div>
        </div>

        <div>
            <!-- Total, Buttons and Dropdowns -->
            <div class="flex items-center justify-between gap-2 mt-2">
                <!-- Pick Customer (secondary button) -->
                <button 
                    class="px-3 py-2 text-sm font-medium text-blue-600 transition border border-blue-600 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    x-on:click="$dispatch('open-modal', 'add-customer-sale')"
                >
                    Pick Customer
                </button>

                <!-- Payment Method -->
                <select required
                    class="py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    id="payment-method" name="payment_method_visible"
                >
                    <option value="" disabled selected>Pick payment</option>
                    <option value="Cash">Cash</option>
                    <option value="Credit">Credit</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Shipping Fee -->
            <div class="flex items-center justify-between mt-2">
                <span class="text-sm">Shipping Fee</span>
                <select class="py-1 text-sm border border-gray-300 rounded" id="shipping" onchange="renderCart()">
                    <option value="0">No Delivery</option>
                    <option value="50">₱50</option>
                    <option value="70">₱70</option>
                    <option value="100">₱100</option>
                </select>
            </div>

            <!-- Total -->
            <div class="flex items-center justify-between mt-2 font-bold">
                <span>Total</span>
                <span id="order-total">₱0.00</span>
            </div>
            
            <!-- Button -->
            <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $currentBranch->branch_id ?? '' }}">
                <input type="hidden" name="customer_id" id="selectedCustomerId">
                <input type="hidden" name="cart" id="cartData" value=''>
                <input type="hidden" name="payment_method" id="paymentMethodField">
                <input type="hidden" id="shippingField" name="shipping_fee" value="0">
                <input type="hidden" id="totalAmountField" name="total_amount" value="0">


                <button type="submit" class="w-full py-2 mt-4 text-white bg-blue-600 rounded-md shadow hover:bg-blue-800">
                    Proceed
                </button>
            </form>
        </div>
    </div>
</div>










<!-- 1st Customer Modal -->
<x-modal name="add-customer-sale" :show="false" maxWidth="sm">
    <div class="p-6 overflow-y-auto" style="max-height: 80vh;">
        <h2 class="text-lg font-bold text-blue-600">Add Customer</h2>
        <p class="mb-4 text-gray-700">Add a customer to confirm credited order:</p>

        <!-- Search bar -->
        <form method="GET" action="">
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search for customers..."
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300"
                >
                <span class="absolute right-3 top-2.5 text-gray-400">
                    <i class="mr-2 text-blue-400 fa-solid fa-magnifying-glass"></i>
                </span>
            </div>
        </form>

        <!-- Button -->
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'add-new-customer')" 
            class="w-full py-2 mt-2 mb-4 text-white bg-blue-600 rounded-md shadow hover:bg-blue-800">
            Add new customer
        </button>

        <div x-data="{ selectedCustomerId: null }">
            <!-- Hidden input + display -->
            <input type="hidden" id="selected_customer_id" name="customer_id">
            <input type="hidden" id="selected_customer_address" name="customer_address">

            <p>Selected Customer: 
                <span id="selected_customer_name" class="font-medium text-gray-800">None</span>
            </p>

            @forelse ($customers as $customer)
                <button
                    type="button"
                    class="flex items-center w-full p-2 mb-4 space-x-3 rounded shadow cursor-pointer hover:bg-gray-300"
                    data-id="{{ $customer->customer_id }}"
                    data-name="{{ $customer->cust_name }}"
                    data-address="{{ $customer->cust_address ?? '' }}"
                    x-on:click="
                            selectedCustomerId = $el.dataset.id;
                            document.querySelector('#selected_customer_id').value = $el.dataset.id;
                            document.querySelector('#selected_customer_name').textContent = $el.dataset.name;
                            document.querySelector('#selected_customer_address').value = $el.dataset.address;

                            // ✅ Sync with POS main form
                            document.querySelector('#selectedCustomerId').value = $el.dataset.id;

                            setTimeout(() => $dispatch('close-modal', 'add-customer-sale'), 200);
                        "
                >
                    @if($customer->cust_image_path)
                        <img src="{{ asset('storage/' . $customer->cust_image_path) }}" 
                            alt="{{ $customer->cust_name }}" class="object-cover w-8 h-8 rounded-full">
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    @endif

                    <div class="flex-1 text-left">
                        <div class="font-medium text-gray-800">{{ $customer->cust_name }}</div>
                        <div class="text-xs text-gray-500">{{ $customer->cust_address ?? 'No address provided' }}</div>
                    </div>

                    <!-- Checkmark: shows only if this button is the selected customer -->
                    <i x-show="selectedCustomerId == '{{ $customer->customer_id }}'" class="text-green-600 fa-solid fa-check"></i>
                </button>
            @empty
                <div class="p-2 text-sm text-center text-gray-500">No customers found.</div>
            @endforelse
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

        <div class="flex justify-end mt-4 space-x-2">
            <!-- Close button -->
            <button 
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300"
                x-on:click="$dispatch('close-modal', 'add-customer-sale')">
                Close
            </button>

            <button class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700"
                x-on:click="$dispatch('close-modal', 'add-customer-sale')">
                Done
            </button>
        </div>
    </div>
</x-modal>

<!-- 2nd Add Customer Modal -->
<x-modal name="add-new-customer" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Title -->
        <div class="flex items-center mb-4 space-x-1 text-blue-900">
            <i class="fa-solid fa-user-plus"></i>
            <h2 class="text-xl font-semibold">Add New Customer</h2>
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
                        <input type="text" name="cust_name" placeholder="Juan Dela Cruz"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" required />
                    </div>

                    <!-- Contact Number -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input type="text" name="cust_contact" placeholder="+63 912 345 6789"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input type="text" name="cust_address" placeholder="123 Main St, City"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                    </div>

                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-new-customer')"
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

<!-- Extra Modals -->
<!-- No Stock Modal -->
<x-modal name="no-stock" :show="false" maxWidth="sm">
    <div class="p-6 text-center">
        <h2 class="mb-4 text-lg font-bold text-red-600">No more stock available!</h2>
        <button 
            class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
            x-on:click="$dispatch('close-modal', 'no-stock')">
            OK
        </button>
    </div>
</x-modal>

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

<script>
    let cart = {};

    // Save cart to localStorage and sync hidden input
    function saveCart() {
        localStorage.setItem("cart", JSON.stringify(cart));

        const cartInput = document.getElementById("cartData");
        if (cartInput) cartInput.value = JSON.stringify(cart);
    }

    // Render cart items in the page
    function renderCart() {
        const container = document.querySelector("#order-items");
        if (!container) return;
        container.innerHTML = "";

        // Remove zero-qty items
        for (const id in cart) {
            if (cart[id].qty <= 0) delete cart[id];
        }

        Object.values(cart).forEach(item => {
            let div = document.createElement("div");
            div.className = "flex items-center justify-between p-2 bg-white rounded shadow";
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-200 rounded">
                        ${
                            item.image 
                            ? `<img src="${item.image}" alt="${item.name}" class="object-cover w-full h-full rounded">`
                            : `<div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-box fa-2x"></i>
                            </div>`
                        }
                    </div>
                    <div>
                        <div class="text-sm font-semibold">${item.name}</div>
                        <div class="text-xs text-gray-500">Unit Price: ₱${item.price.toFixed(2)}</div>
                        <div class="text-xs text-gray-500">Stock left: ${item.stock - item.qty}x</div>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <button class="px-2 py-1 text-xs bg-red-300 rounded clear-item hover:bg-red-600" data-id="${item.branch_product_id}">
                        Clear
                    </button>
                    <div class="font-semibold">₱${(item.price * item.qty).toFixed(2)}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <button class="px-2 py-1 text-sm bg-gray-200 rounded qty-decrease" data-id="${item.branch_product_id}">-</button>
                        <span class="text-sm font-medium">${item.qty}</span>
                        <button class="px-2 py-1 text-sm bg-gray-200 rounded qty-increase" data-id="${item.branch_product_id}">+</button>
                    </div>
                </div>
            `;
            container.appendChild(div);
        });

        // Attach events after rendering
        document.querySelectorAll(".qty-decrease").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.dataset.id;
                if (cart[id].qty > 1) {
                    cart[id].qty--;
                } else {
                    delete cart[id];
                }
                saveCart();
                renderCart();
            });
        });

        document.querySelectorAll(".qty-increase").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.dataset.id;
                if (cart[id].qty < cart[id].stock) {
                    cart[id].qty++;
                    saveCart();
                } else {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'no-stock' }));
                }
                renderCart();
            });
        });

        document.querySelectorAll(".clear-item").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.dataset.id;
                delete cart[id];
                saveCart();
                renderCart();
            });
        });

        // Calculate total
        const subtotal = Object.values(cart).reduce((sum, item) => sum + (item.qty * item.price), 0);
        const shippingFee = parseFloat(document.querySelector("#shipping")?.value || 0);
        const total = subtotal + shippingFee;
        document.querySelector("#order-total").textContent = "₱" + total.toFixed(2);
    }

    // Add item to cart
    function addToCart(branchProductId, name, stock, price, image, supplier) {
        if (!cart[branchProductId]) {
            cart[branchProductId] = { branch_product_id: branchProductId, name, qty: 1, stock, price, image, supplier };
        } else if (cart[branchProductId].qty < stock) {
            cart[branchProductId].qty++;
        } else {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'no-stock' }));
            return;
        }
        saveCart();
        renderCart();
    }

    // Clear entire cart
    function clearAllCart() {
        cart = {};
        saveCart();
        renderCart();
    }

    // Select customer
    function selectCustomer(id, name) {
        document.getElementById("selectedCustomerId").value = id;
        document.getElementById("selectedCustomerDisplay").innerHTML = "👤 " + name;
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-customer-sale' }));
    }

    // ✅ DOMContentLoaded
    document.addEventListener("DOMContentLoaded", function() {
        // Load cart from localStorage
        cart = JSON.parse(localStorage.getItem("cart")) || {};

        // Clear cart after redirect if flagged
        if (localStorage.getItem("clearCartAfterSubmit") === "true") {
            clearAllCart();
            localStorage.removeItem("clearCartAfterSubmit");
        }

        // Render cart
        renderCart();

        // Handle sale form submit
        const saleForm = document.getElementById("saleForm");
        if (saleForm) {
            saleForm.addEventListener("submit", function(e) {
                // Fill hidden fields first
                document.getElementById("cartData").value = JSON.stringify(cart);
                document.getElementById("paymentMethodField").value = document.getElementById("payment-method").value;
                document.getElementById("shippingField").value = document.getElementById("shipping").value;

                // ✅ Defer clearing cart until after form posts
                setTimeout(() => clearAllCart(), 50);
            });
        }
    });
</script>