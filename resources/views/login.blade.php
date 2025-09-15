<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>KitaKeeps - Login</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/logo/logo-removebg-preview.png" type="image/x-icon">

    <!-- Open Graph / Social Preview -->
    <meta property="og:title" content="KitaKeeps - Login">
    <meta property="og:description" content="Manage your hardware effortlessly.">
    <meta property="og:image" content="https://raw.githubusercontent.com/SixxCodes/KitaKeeps/main/assets/images/docu/social-preview-1.png">
    <meta property="og:url" content="https://sixxcodes.github.io/KitaKeeps/">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>

    <!-- Font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <link rel="stylesheet" href="{{ asset('assets/css/login-register.css') }}">
</head>
<body>
    <div id="app" class="relative min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        
        <!-- Floating shapes -->
        <div class="float-shape"></div>
        <div class="float-shape"></div>
        <div class="float-shape"></div>

        <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-10 fade-slide-in relative z-10">

            <h2 class="text-center text-3xl font-extrabold text-blue-600 mb-2">Login to KitaKeeps</h2>
            <p class="text-center mb-4">Welcome back! Log in to continue.</p>

            <form @submit.prevent="submitLogin" novalidate>
                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email address</label>
                    <input
                        id="email"
                        type="email"
                        v-model.trim="email"
                        required
                        placeholder="you@example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 transition focus:ring-2 focus:ring-blue-400"
                        :class="{'border-red-500': emailError}"
                    />
                    <p v-if="emailError" class="text-red-500 text-sm mt-1">@{{ emailError }}</p>
                </div>

                <!-- Password -->
                <div class="mb-6 relative">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        v-model.trim="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 transition focus:ring-2 focus:ring-blue-400"
                        :class="{'border-red-500': passwordError}"
                    />
                    <p v-if="passwordError" class="text-red-500 text-sm mt-1">@{{ passwordError }}</p>

                    <!-- Show password (eye) icon -->
                    <button type="button" @click="togglePassword" class="absolute right-3 top-11 text-gray-500 hover:text-blue-600 focus:outline-none" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between mb-6">

                    <label class="flex items-center text-gray-700">
                        <input type="checkbox" v-model="rememberMe" class="form-checkbox h-4 w-4 text-blue-600" />
                        <span class="ml-2 text-sm">Remember me</span>
                    </label>

                    <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                </div>

                <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg transition-colors focus:ring-4 focus:ring-blue-400 focus:outline-none"
                :disabled="loading"
                >
                    <span v-if="!loading">Log In</span>
                    <span v-else class="flex justify-center items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Logging in...</span>
                    </span>
                </button>
            </form>

            <p class="mt-6 text-center text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Register</a>
            </p>
        </div>
    </div>

    <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
