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

        <!-- Add Customer -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'add-product')"  class="flex items-center px-5 py-2 text-xs text-white transition-colors bg-blue-600 rounded-md shadow hover:bg-blue-800 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Add Product</span>
            </button>
        </div>
    </div>
</div>

<!-- Export Modal -->
<x-modal name="export-options" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <h2 class="text-lg font-semibold text-gray-800">Export As</h2>

        <form method="GET" action="{{ route('products.export') }}" class="space-y-2">
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

<!-- Add Product -->
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
                            <option value="" disabled selected>Select category</option>
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
                            <option value="" disabled selected>Select a supplier</option>
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











<!-- ALL PRODUCTS -->
<h3 class="mt-8 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">All Products</h3>

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

        <div class="flex space-x-2">
            <!-- Status Filter -->
            <div class="flex items-center px-2 py-1 text-sm border rounded cursor-pointer">
                <i class="mr-2 text-blue-400 fa-solid fa-filter"></i>
                <select onchange="window.location.href='?per_page={{ request('per_page',5) }}&search={{ request('search') }}&status='+this.value" class="text-sm bg-transparent border-none outline-none">
                    <option value="all" @if(request('status','all')=='all') selected @endif>All Status</option>
                    <option value="in_stock" @if(request('status')=='in_stock') selected @endif>In Stock</option>
                    <option value="low_stock" @if(request('status')=='low_stock') selected @endif>Low Stock</option>
                    <option value="out_of_stock" @if(request('status')=='out_of_stock') selected @endif>Out of Stock</option>
                </select>
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
    </div>

    <div class="overflow-x-auto table-pretty-scrollbar">
        <!-- Table -->
        <table class="min-w-full text-sm border">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 text-left border">#</th>
                    <th class="px-3 py-2 text-left border">ID</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Product Name</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Product Supplier</th>
                    <th class="px-3 py-2 text-left border">Category</th>
                    <th class="px-3 py-2 text-left border">Qty.</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Selling Price</th>
                    <th class="px-3 py-2 text-left border">Status</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <!-- Count -->
                    <!-- <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td> -->
                    <td class="px-3 py-2 border bg-blue-50">
                        {{ $products->firstItem() + $loop->index }}
                    </td>

                    <!-- ID -->
                    <td class="px-3 py-2 border">{{ $product->product_id }}</td>

                    <!-- Product Image + Name -->
                    <td class="px-3 py-2 border">
                        <div class="flex items-center gap-2">
                            @if($product->prod_image_path)
                                <img src="{{ asset('storage/'.$product->prod_image_path) }}" 
                                     alt="{{ $product->product_name }}" 
                                     class="object-cover w-8 h-8 rounded-full">
                            @else
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                            @endif
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                {{ $product->prod_name }}
                            </span>
                        </div>
                    </td>

                    <!-- Supplier -->
                    <td class="px-3 py-2 border">
                        {{ $product->product_supplier->first()?->supplier?->supp_name ?? 'N/A' }}
                    </td>

                    <!-- Category -->
                    <td class="px-3 py-2 border whitespace-nowrap">
                        {{ $product->category ?? 'N/A' }}
                    </td>

                    <!-- Quantity -->
                    <td class="px-3 py-2 border">
                        {{ $product->branch_products->first()?->stock_qty ?? 0 }}
                    </td>

                    <!-- Selling Price -->
                    <td class="px-3 py-2 border">
                        P{{ number_format($product->selling_price, 2) }}
                    </td>

                    <!-- Status -->
                    <td class="px-3 py-2 border">
                        @php
                            $stock = $product->branch_products->first()?->stock_qty ?? 0;
                        @endphp

                        @if($stock == 0)
                            <span class="inline-block px-3 py-1 text-xs text-white bg-red-500 rounded-full whitespace-nowrap">
                                Out of Stock
                            </span>
                        @elseif($stock < 20)
                            <span class="inline-block px-3 py-1 text-xs text-white bg-yellow-500 rounded-full whitespace-nowrap">
                                Low Stock
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 text-xs text-white bg-green-500 rounded-full whitespace-nowrap">
                                In Stock
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="flex justify-center gap-2 px-3 py-3 border">
                        <button x-on:click="$dispatch('open-modal', 'view-product-{{ $product->product_id }}')" class="px-2 py-1 text-white bg-blue-500 rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button x-on:click="$dispatch('open-modal', 'edit-product-{{ $product->product_id }}')" class="px-2 py-1 text-white bg-green-500 rounded">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->product_id }}')" class="px-2 py-1 text-white bg-red-500 rounded">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-3 py-2 text-center text-gray-500 border">
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
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} entries
        </p>
        <div class="flex gap-2">
            <a href="{{ $products->previousPageUrl() }}" 
               class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $products->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>
            <a href="{{ $products->nextPageUrl() }}" 
               class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $products->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

@foreach($products as $product)
<!-- View Product Details Modal -->
<x-modal name="view-product-{{ $product->product_id }}" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Product Section -->
        <div class="flex items-center space-x-4">
            <!-- Product Image -->
            <div class="flex items-center justify-center w-20 h-20 overflow-hidden text-3xl text-white bg-blue-400 rounded-full">
                @if($product->prod_image_path)
                    <img src="{{ asset('storage/' . $product->prod_image_path) }}" 
                        alt="{{ $product->prod_name }}" 
                        class="object-cover w-full h-full rounded-full">
                @else
                    <i class="fa-solid fa-box"></i>
                @endif
            </div>

            <!-- Product Name & Category -->
            <div>
                <p class="text-lg font-semibold text-gray-800">{{ $product->prod_name }}</p>
                <p class="text-sm text-gray-500">Category: {{ $product->category ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t"></div>

        <!-- Product Details -->
        <div class="space-y-2 text-sm text-gray-700">
            <p><span class="font-medium">Description:</span> {{ $product->prod_description ?? 'N/A' }}</p>
            <p><span class="font-medium">Unit Cost:</span> ₱{{ number_format($product->unit_cost, 2) }}</p>
            <p><span class="font-medium">Selling Price:</span> ₱{{ number_format($product->selling_price, 2) }}</p>
            <p><span class="font-medium">Stock Quantity:</span> {{ $product->branch_products->first()?->stock_qty ?? 0 }}</p>
            
            @php
                $stock = $product->branch_products->first()?->stock_qty ?? 0;
            @endphp
            <p>
                <span class="font-medium">Status: </span> 
                @if($stock == 0)
                    <span class="text-red-500">Out of Stock</span>
                @elseif($stock < 20)
                    <span class="text-yellow-500">Low Stock</span>
                @else
                    <span class="text-green-500">In Stock</span>
                @endif
            </p>

            <p><span class="font-medium">Active:</span> {{ $product->is_active ? 'Yes' : 'No' }}</p>
            <p><span class="font-medium">Supplier:</span> {{ $product->product_supplier->first()?->supplier?->supp_name ?? 'N/A' }}</p>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end pt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'view-product-{{ $product->product_id }}')"
                class="px-4 py-2 text-white transition bg-gray-500 rounded hover:bg-gray-600"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>
@endforeach

@foreach($products as $product)
<!-- Edit Product Details Modal -->
<x-modal name="edit-product-{{ $product->product_id }}" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        
        <!-- Modal Header -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-box"></i>
            <h2 class="text-xl font-semibold">Edit Product Details</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'edit-product-{{ $product->product_id }}')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>

        <!-- Form -->
        <form action="{{ route('products.update', $product->product_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
            @csrf
            @method('PATCH')

            <!-- Product Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative w-24 h-24">
                    
                    <img id="preview_{{ $product->product_id }}" 
                        src="{{ $product->prod_image_path ? asset('storage/'.$product->prod_image_path) : 'assets/images/logo/logo-removebg-preview.png' }}" 
                        class="object-cover w-full h-full border rounded-full shadow" 
                        alt="{{ $product->prod_name }}">

                    <!-- Edit image button -->
                    <label for="product_image_{{ $product->product_id }}" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                        <input type="file" name="product_image" id="product_image_{{ $product->product_id }}" class="hidden" 
                            onchange="previewImage(event, {{ $product->product_id }})">
                    </label>
                </div>
                <p class="mt-2 text-sm text-gray-500">Change product image</p>
            </div>

            <!-- Product Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Product Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                    <!-- Product Name -->
                    <div>
                        <label class="block mb-1 text-gray-800">Product Name</label>
                        <input type="text" name="prod_name" value="{{ $product->prod_name }}" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block mb-1 text-gray-800">Category</label>
                        <select name="category" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500">
                            <option value="">Select category</option>
                            @php
                                $allCategories = [
                                    'Building Materials','Construction Materials','Decor','Electrical',
                                    'Furniture','Garden & Landscaping','Paints & Finishes',
                                    'Plumbing & Sanitary','Security & Safety','Tools'
                                ];
                                $selectedCategory = old('category', $product->category);
                            @endphp
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Description</label>
                        <textarea name="prod_description" rows="3" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ $product->prod_description }}</textarea>
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label class="block mb-1 text-gray-800">Product Supplier</label>
                        <select name="supplier" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($suppliers as $supp)
                                <option value="{{ $supp->supplier_id }}" 
                                    @if($product->product_supplier->first()?->supplier_id == $supp->supplier_id) selected @endif>
                                    {{ $supp->supp_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stock Quantity -->
                    <div>
                        <label class="block mb-1 text-gray-800">Stock Quantity</label>
                        <input type="number" name="stock_qty" value="{{ $product->branch_products->first()?->stock_qty ?? 0 }}" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
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
                        <input type="number" name="unit_cost" value="{{ $product->unit_cost }}" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label class="block mb-1 text-gray-800">Selling Price</label>
                        <input type="number" name="selling_price" value="{{ $product->selling_price }}" 
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                    x-on:click="$dispatch('close-modal', 'edit-product-{{ $product->product_id }}')"
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
function previewImage(event, id) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('preview_' + id);
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

@foreach($products as $product)
<!-- Delete Product Modal -->
<x-modal name="delete-product-{{ $product->product_id }}" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <!-- Red warning icon -->
        <i class="mx-auto text-4xl text-red-500 fa-solid fa-triangle-exclamation"></i>

        <h2 class="text-lg font-semibold text-gray-800">Delete Product?</h2>
        <p class="text-sm text-gray-500">
            This action will permanently remove <span class="text-red-500"><strong>{{ $product->prod_name }}</strong></span> from the system. This cannot be undone.
        </p>

        <div class="flex justify-center mt-4 space-x-3">
            <!-- Cancel button -->
            <button
                x-on:click="$dispatch('close-modal', 'delete-product-{{ $product->product_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >
                Cancel
            </button>

            <!-- Delete form -->
            <form action="{{ route('products.destroy', $product->product_id) }}" method="POST">
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