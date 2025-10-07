@php
    $role = Auth::user()->role ?? null;
@endphp

<div class="flex flex-col w-64 min-h-screen text-white bg-blue-600 dark:bg-gray-800">
    <!-- Logo and Name -->
    <div class="flex items-center px-6 py-3 space-x-3 border-b border-blue-700">
        <img src="assets/images/logo/logo-removebg-preview.png" alt="Logo" class="w-10 h-10" />
        <span class="pr-10 text-xl font-bold">
            KitaKeeps
        </span>

        <!-- Close button (mobile only) -->
        <button 
            @click="toggleSidebar" 
            class="text-gray-300 hover:text-white focus:outline-none md:hidden"
        >
            <!-- X Icon -->
            <i class="text-lg fa-solid fa-x"></i>
        </button>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
        <!-- Top: Clock + Date -->
        <!-- <div class="flex items-start justify-start">
            <div class="flex flex-col items-start">
                <span id="clock" class="text-xl font-semibold text-white-600">12:45:32</span>
                <span id="date" class="text-sm text-white-500">September 22, 2025</span>
            </div>
        </div> -->

        <!-- Home Section -->
        <div>
            <p class="mb-2 text-xs font-semibold text-blue-300 uppercase select-none">
                Home
            </p>
            @if($role === 'Owner' || $role === 'Admin')
            <a href="#" @click.prevent="changePage('Dashboard')" 
                :class="{'font-bold bg-blue-800': currentPage === 'Dashboard'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 text-2xl fa-solid fa-grip"></i>
                Dashboard
            </a>
            @endif
            <a href="#" @click.prevent="changePage('POS')" 
                :class="{'font-bold bg-blue-800': currentPage === 'POS'}"
                class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 fa-solid fa-computer"></i>
                POS
            </a>
        </div>

        <!-- Business Intelligence Section -->
        @if($role === 'Owner' || $role === 'Admin')
        <div>
            <p class="mb-2 text-xs font-semibold text-blue-300 uppercase select-none">
                Business Intelligence
            </p>
            <a href="#" @click.prevent="changePage('Reports &amp; Analytics')" 
                :class="{'font-bold bg-blue-800': currentPage === 'Reports &amp; Analytics'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 fa-solid fa-robot"></i>
                Reports &amp; Analytics
            </a>
        </div>
        @endif

        <!-- Management Section -->
        <div>
            <p class="block mb-2 text-xs font-semibold text-blue-300 uppercase select-none">
                Management
            </p>
            @if($role === 'Owner')
            <a href="#" @click.prevent="changePage('My Hardware')" 
                :class="{'font-bold bg-blue-800': currentPage === 'My Hardware'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 fa-solid fa-warehouse"></i>
                My Hardware
            </a>
            @endif
            
            @if($role === 'Owner' || $role === 'Admin')
            <a href="#" @click.prevent="changePage('My Inventory')" 
                :class="{'font-bold bg-blue-800': currentPage === 'My Inventory'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-2 text-xl fa-solid fa-clipboard-list"></i>
                My Inventory
            </a>
            @endif

            @if($role === 'Owner' || $role === 'Admin')
            <a href="#" @click.prevent="changePage('My Suppliers')" 
                :class="{'font-bold bg-blue-800': currentPage === 'My Suppliers'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 text-sm fa-solid fa-truck"></i>
                My Suppliers
            </a>
            @endif
            
            @if($role === 'Owner' || $role === 'Admin')
                <a href="#" @click.prevent="changePage('My Employees')" 
                    :class="{'font-bold bg-blue-800': currentPage === 'My Employees'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                    <i class="mr-1 text-sm fa-solid fa-users"></i>
                    My Employees
                </a>
            @endif

            <a href="#" @click.prevent="changePage('My Customers')" 
                :class="{'font-bold bg-blue-800': currentPage === 'My Customers'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 text-sm fa-solid fa-users-line"></i>
                My Customers
            </a>
        </div>
    </nav>

    <!-- Bottom Buttons -->
    <div class="px-4 py-4 space-y-2 border-t border-blue-700">
        <a href="#" @click.prevent="changePage('Settings')" 
                :class="{'font-bold bg-blue-800': currentPage === 'Settings'}" class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
            <i class="mr-1 fa-solid fa-gear"></i>
            Settings
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                class="block w-full px-3 py-2 text-left transition rounded hover:bg-blue-700">
                <i class="mr-1 fa-solid fa-right-from-bracket"></i>
                Logout
            </button>
        </form>
    </div>
</div>