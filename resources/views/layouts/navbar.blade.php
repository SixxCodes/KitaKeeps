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

        <!-- Profile Update Form -->
        <form method="POST" action="{{ route('user.updateProfile') }}" enctype="multipart/form-data">
            @csrf

            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <!-- Profile Image Preview -->
                    <img 
                        id="userImagePreview"
                        src="{{ Auth::user()->user_image_path ? asset('storage/' . Auth::user()->user_image_path) : asset('assets/images/logo/logo-removebg-preview.png') }}" 
                        class="object-cover border-2 border-blue-200 rounded-full shadow-md w-28 h-28"
                        alt="{{ Auth::user()->username }}">

                    <!-- Edit Image Button -->
                    <label for="user_image" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-blue-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>

                    <input type="file" id="user_image" name="user_image" class="hidden" accept="image/*" onchange="previewUserImage(event)">
                </div>
                <p class="mt-2 text-sm text-gray-500">Change profile photo</p>
            </div>

            <!-- Username -->
            <div class="mb-4 text-center">
                <label class="block mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    value="{{ Auth::user()->username }}" 
                    class="w-full px-3 py-2 text-center border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                >
            </div>

            <!-- Role and Branch -->
            <div class="mb-6 space-y-1 text-center">
                <p class="font-medium text-gray-600 dark:text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ Auth::user()->branches->pluck('branch_name')->join(', ') ?: 'No Branch' }}
                </p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-center mb-4">
                <button type="submit" 
                    class="flex items-center px-4 py-2 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                    <i class="mr-2 fa-solid fa-save"></i>
                    Save Changes
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="flex justify-center">
                <button type="submit" 
                    class="flex items-center justify-center w-40 px-4 py-2 text-white transition bg-gray-600 rounded-lg shadow hover:bg-gray-700">
                    <i class="mr-2 fa-solid fa-right-from-bracket"></i>
                    Logout
                </button>
            </div>
        </form>
    </div>

    <!-- JS: Preview Uploaded Image -->
    <script>
        function previewUserImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('userImagePreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-modal>

