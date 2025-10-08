<nav class="flex items-center justify-between px-6 py-3 bg-white shadow">
    <div class="flex items-center space-x-4">
        <!-- Hamburger (only visible on mobile/tablet) -->
        <button 
            @click="toggleSidebar"
            class="text-gray-600 focus:outline-none md:hidden"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <h1 class="text-xl text-gray-900">
            <!-- Page Icon -->
            <span class="text-blue-800" v-html="pageIcons[currentPage]"></span>

            <!-- Page Title -->
            <span class="text-[14px] sm:text-base md:text-lg lg:text-xl" v-if="currentPage">@{{ currentPage }}</span>
        </h1>
    </div>

    <div class="flex items-center mr-4 space-x-1">
        <!-- Bell Icon (always visible) -->
        <!-- <i class="mr-5 text-blue-800 fa-solid fa-bell"></i> -->

        <!-- User Icon (always visible) -->
        <button 
            x-data 
            x-on:click="$dispatch('open-modal', 'user-profile')" 
            class="flex items-center justify-center w-8 h-8 overflow-hidden border border-gray-200 rounded-full"
        >
            @if(Auth::user()->user_image_path)
                <img 
                    src="{{ asset('storage/' . Auth::user()->user_image_path) }}" 
                    alt="User photo" 
                    class="object-cover w-full h-full"
                >
            @else
                <!-- Fallback icon if no image -->
                <i class="flex items-center justify-center w-full h-full text-white bg-blue-200 fa-solid fa-user"></i>
            @endif
        </button>

        <!-- Username and Role (hidden on mobile) -->
        <div class="flex-col hidden sm:flex">
            <span class="text-sm text-black">{{ Auth::user()->username }}</span>
            <span class="text-xs text-gray-600">
                {{ Auth::user()->role }} at {{ Auth::user()->branches->first()->branch_name ?? 'No Branch' }}
            </span>
        </div>

        <!-- Dropdown arrow (hidden on mobile) -->
        <button x-data x-on:click="$dispatch('open-modal', 'user-profile')">
            <i class="hidden text-gray-400 sm:inline fa-solid fa-angle-down"></i>
        </button>
    </div>
</nav>

<!-- User Profile Modal -->
<x-modal name="user-profile" :show="false" maxWidth="sm">
    <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-blue-900 dark:text-gray-100">Your Profile</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'user-profile')"
            >
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Profile Image -->
        <div class="flex flex-col items-center mb-6">
            @if(Auth::user()->user_image_path)
                <img 
                    src="{{ asset(Auth::user()->user_image_path) }}" 
                    alt="User photo" 
                    class="object-cover border-2 border-blue-200 rounded-full shadow-md w-28 h-28"
                >
            @else
                <!-- Fallback icon if no image -->
                <div class="flex items-center justify-center text-4xl text-white bg-blue-200 rounded-full shadow-md w-28 h-28">
                    <i class="fa-solid fa-user"></i>
                </div>
            @endif
        </div>

        <!-- Username -->
        <div class="mb-4 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">Username</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ Auth::user()->username }}</p>
        </div>

        <!-- Role and Branch -->
        <div class="mb-6 space-y-1 text-center">
            <p class="text-gray-600 dark:text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
            <p class="text-gray-600 dark:text-gray-400">
                {{ Auth::user()->branches->pluck('branch_name')->join(', ') ?: 'No Branch' }}
            </p>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                class="flex items-center px-4 py-2 mx-auto text-white transition bg-gray-600 rounded-lg shadow hover:bg-gray-700">
                <i class="mr-2 fa-solid fa-right-from-bracket"></i>
                Logout
            </button>
        </form>
    </div>
</x-modal>
