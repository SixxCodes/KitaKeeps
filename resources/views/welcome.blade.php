<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KitaKeeps - Hardware Management System</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/logo/logo-removebg-preview.png" type="image/x-icon">

    <!-- Open Graph / Social Preview -->
    <meta property="og:title" content="KitaKeeps">
    <meta property="og:description" content="Manage your hardware effortlessly.">
    <meta property="og:image" content="https://raw.githubusercontent.com/SixxCodes/KitaKeeps/main/assets/images/docu/social-preview-1.png">
    <meta property="og:url" content="https://sixxcodes.github.io/KitaKeeps/">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Tailwind -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->

    <!-- Vue.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script> -->
    
    <!-- Font Awesome’s icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vite built CSS/JS (production) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-b from-blue-50 to-white">
    <x-guest-layout>
        <div id="landing-page-app">

            <!-- Header -->
            <header :class="{'nav-scrolled': scrolled}" class="fixed top-0 z-50 w-full py-4 transition-all duration-300">
                <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">

                        <!-- Logo -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <a href="{{ route('welcome') }}"><img src="assets/images/logo/logo-removebg-preview.png" class="w-16 h-16 mr-2" alt="KitaKeeps Logo"></a>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('welcome') }}" class="text-3xl font-bold text-blue-600">KitaKeeps</a>
                            </div>
                        </div>

                        <!-- Desktop Navigation -->
                        <nav class="hidden md:block">
                            <div class="flex items-baseline ml-10 space-x-8">
                                <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                    Home
                                </a>
                                <a href="#features" class="text-gray-700 transition-colors hover:text-blue-600">
                                    Features
                                </a>
                                <!-- <a href="#testimonials" class="text-gray-700 transition-colors hover:text-blue-600">
                                    Testimonials
                                </a> -->
                                <a href="#project-team" class="text-gray-700 transition-colors hover:text-blue-600">
                                    Team
                                </a>
                                <a href="#contact" class="text-gray-700 transition-colors hover:text-blue-600">
                                    Contact
                                </a>
                            </div>
                        </nav>

                        <!-- Login and Signup Nav Button -->
                        <div class="hidden space-x-4 md:block">
                            <a href="{{ url('/login-frontend') }}" class="px-8 py-3 text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-800">
                                Log in
                            </a>
                            <!-- <a href="signup.html" class="px-4 py-3 text-blue-600 transition-colors border border-blue-600 rounded-md hover:bg-blue-50">
                                Sign up
                            </a> -->
                        </div>

                        <!-- Mobile Hamburger button (Hidden in desktop, ofc)-->
                        <div class="md:hidden">
                            <button @click="toggleMenu" class="text-gray-700 hover:text-blue-600">
                                <i :class="menuOpen ? 'fa-times' : 'fa-bars'" class="text-3xl fas"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Navigation -->
                    <div :class="{'open': menuOpen}" class="mobile-menu md:hidden">
                        <div class="px-2 pt-2 pb-3 space-y-1 border-t border-gray-200 sm:px-3">
                            <a href="#" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                Home
                            </a>
                            <a href="#features" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                Features
                            </a>
                            <!-- <a href="#testimonials" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                Testimonials
                            </a> -->
                            <a href="#project-team" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                Team
                            </a>
                            <a href="#contact" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                                Contact
                            </a>
                            <div class="px-3 py-2 space-x-2">
                                <a href="{{ route('login') }}" class="px-10 py-2 text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-700">
                                    Log in
                                </a>
                                <a href="{{ url('/register-frontend') }}" class="px-4 py-2 text-blue-600 transition-colors border border-blue-600 rounded-md hover:bg-blue-50">
                                    Register
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Hero Section -->
            <section class="relative pt-32 pb-20 overflow-hidden lg:pt-25 lg:pb-10">

                <!-- Round background elements -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute bg-blue-200 rounded-full -top-40 -right-32 h-80 w-80 pulse-animation opacity-30 mix-blend-multiply filter"></div>

                    <div class="absolute bg-blue-500 rounded-full -bottom-40 -left-32 h-80 w-80 pulse-animation opacity-30 mix-blend-multiply filter"></div>
                </div>

                <div class="container relative px-4 mx-auto sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-2">
                        <div class="flex flex-col justify-center fade-in">

                            <!-- Landing page title -->
                            <h1 class="z-10 mb-6 text-4xl font-black leading-tight text-gray-900 sm:text-5xl lg:text-6xl">
                                Smarter <span class="text-blue-600">Business Management</span>, Made Simple
                            </h1>

                            <!-- Description -->
                            <p class="max-w-2xl mx-auto mb-8 text-xl leading-relaxed text-gray-600">
                                Monitor, update, and organize hardware inventory with our powerful, easy-to-use system designed for <span class="text-blue-600">Building and Home Improvement Hardware Stores</span>.
                            </p>

                            <!-- Buttons -->
                            <div class="flex flex-col items-start gap-4 sm:flex-row">
                                <a href="{{ url('/register-frontend') }}"  class="flex items-center px-8 py-3 text-white transition-colors bg-blue-600 rounded-md hover:bg-blue-800">
                                    Get Started
                                    <i class="ml-2 fas fa-arrow-right"></i>
                                </a>

                                <a href="#why-kitakeeps" class="flex items-center px-8 py-3 text-blue-600 transition-colors bg-transparent border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white">
                                    <i class="mr-2 fa-solid fa-circle-info"></i>
                                    Learn More
                                </a>
                            </div>

                            <!-- Disclaimer -->
                            <div class="z-10 mt-12">
                                <p class="z-10 mb-4 text-sm text-gray-500">Built for small and medium enterprises (SMEs) specifically for building and home improvement hardware stores.</p>
                                <!-- <div class="flex flex-wrap items-center gap-6 opacity-60">
                                    <div class="text-lg font-semibold text-gray-700">Zyrile Hardware</div>
                                    <div class="text-lg font-semibold text-gray-700">True Value</div>
                                    <div class="text-lg font-semibold text-gray-700">Local Pro</div>
                                </div> -->
                            </div>

                            <!-- Floating elements -->
                            <div class="absolute z-0 w-24 h-24 bg-blue-200 rounded-full shadow-lg -left-2 top-9 float-animation"></div>
                        </div>

                        <div class="relative">
                            <!-- Image -->
                            <div class="relative z-10 rounded-2xl slide-in">
                                <img 
                                    src="assets/images/storyset/Construction-pana.png" 
                                    alt="Dashboard view of hardware inventory management system showing tools, supplies and stock levels"
                                    class="w-full rounded-lg"
                                />
                            </div>
                            
                            <!-- Floating elements -->
                            <div class="absolute z-0 w-24 h-24 bg-blue-200 rounded-full shadow-lg -right-4 -top-4 float-animation-2"></div>

                            <div class="absolute z-0 w-24 w-32 h-24 h-32 bg-blue-200 rounded-full shadow-lg -bottom-6 right-6 float-animation-3"></div>

                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="py-20 scroll-mt-16">
                <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                    <div class="mb-16 text-center fade-in">
                        <h2 class="mb-4 text-3xl font-black text-gray-900 sm:text-4xl">Built for Hardware Stores</h2>
                        <p class="max-w-2xl mx-auto text-xl text-gray-600">
                            Specialized features that understand the unique challenges of hardware inventory management.
                        </p>
                    </div>

                    <!-- Feature grids -->
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="(feature, index) in features" :key="index" class="p-6 transition-all duration-300 border border-blue-200 rounded-lg group hover:shadow-lg hover:shadow-blue-900/20 slide-in" :style="`animation-delay: ${index * 0.1}s`">

                            <!-- Feature Icons (gikan sa landing-page.js) -->
                            <div class="flex items-center justify-center w-12 h-12 mb-4 transition-colors bg-blue-100 rounded-lg group-hover:bg-blue-600">
                                <i :class="feature.icon" class="text-xl text-blue-600 transition-colors group-hover:text-white"></i>
                            </div>

                            <!-- Feature Texts (gikan sa landing-page.js) -->
                            <h3 class="mb-2 text-xl font-bold text-gray-900">@{{ feature.title }}</h3>
                            <p class="leading-relaxed text-gray-600">@{{ feature.description }}</p>
                        </div>
                    </div>

                    <!-- Why KitaKeeps? -->
                    <div id="why-kitakeeps" class="grid gap-12 mt-20 lg:grid-cols-2 scroll-mt-20">

                        <!-- Left Image -->
                        <div class="relative">
                            <div class="relative z-10 overflow-hidden rounded-2xl slide-in">
                                <img 
                                    src="assets/images/storyset/Spreadsheets-pana.png" 
                                    alt="Image of a guy with inventories behind him."
                                    class="w-full transition-transform duration-500 hover:scale-105"
                                />
                            </div>

                            <!-- Background floating elements -->
                            <div class="absolute z-0 w-20 h-20 bg-blue-100 opacity-50 top-6 -right-3 pulse-animation rounded-xl"></div>
                            <div class="absolute z-0 w-40 h-40 bg-blue-100 opacity-50 -bottom-6 -left-6 pulse-animation rounded-xl"></div>
                        </div>
                        
                        <!-- Text Content -->
                        <div class="flex flex-col justify-center fade-in">
                            <h3 class="mb-4 text-5xl font-bold text-gray-900"><i class="text-blue-600 fa-solid fa-book-open"></i> Why KitaKeeps?</h3>
                            <p class="mb-6 text-lg text-gray-600">
                                Turn the ordinary into extraordinary, and watch your store flourish with ease. KitaKeeps is your partner in growth, guiding every step along the way.
                            </p>
                            <ul class="space-y-4">
                                <li class="flex items-center">
                                    <i class="mr-3 text-green-600 fas fa-check"></i>
                                    <span class="text-gray-700">Supports additional stores or branches</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-green-600 fas fa-check"></i>
                                    <span class="text-gray-700">Real-time sync</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-green-600 fas fa-check"></i>
                                    <span class="text-gray-700">Available offline</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-green-600 fas fa-check"></i>
                                    <span class="text-gray-700">Data is encrypted</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-green-600 fas fa-check"></i>
                                    <span class="text-gray-700">Intuitive Interface for users with minimal training</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials Section -->
            <!-- <section id="testimonials" class="py-20 bg-blue-50 scroll-mt-16">
                <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                    <div class="mb-16 text-center fade-in">
                        <h2 class="mb-4 text-3xl font-black text-gray-900 sm:text-4xl">Trusted by Hardware Professionals</h2>
                        <p class="max-w-2xl mx-auto text-xl text-gray-600">
                            See what hardware store owners are saying about our inventory system
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div v-for="(testimonial, index) in testimonials" :key="index" class="p-6 bg-white border border-blue-100 rounded-lg slide-in" :style="`animation-delay: ${index * 0.2}s`">
                            <div class="flex mb-4">
                                <i v-for="star in testimonial.rating" class="text-yellow-600 fas fa-star"></i>
                            </div>
                            <blockquote class="mb-4 leading-relaxed text-gray-700">
                                "@{{ testimonial.content }}"
                            </blockquote>
                            <div class="pt-4 border-t border-blue-100">
                                <div class="font-semibold text-gray-900">@{{ testimonial.name }}</div>
                                <div class="text-sm text-gray-600">@{{ testimonial.role }} at @{{ testimonial.company }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section> -->

            <!-- Project Team Section -->
            <section id="project-team" class="py-20 scroll-mt-16">
                <div class="container px-4 mx-auto sm:px-6 lg:px-8">

                    <div class="mb-16 text-center fade-in">
                        <h2 class="mb-4 text-3xl font-black text-blue-600 sm:text-5xl">Meet Our Team</h2>

                        <p class="max-w-2xl mx-auto text-xl text-gray-600">
                            The developers behind <span class="text-gray-900">KitaKeeps</span> — combining technical expertise and creative design to build an efficient hardware inventory management system.
                        </p>
                    </div>

                    <!-- Project Team grids -->
                    <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="(team, index) in team" :key="index" class="flex flex-col items-center p-4 text-center transition-all duration-300 border border-blue-400 rounded-lg group hover:shadow-lg hover:shadow-blue-900/20 slide-in" :style="`animation-delay: ${index * 0.1}s`">

                            <!-- Project Team Images (gikan sa landing-page.js) -->
                            <div class="flex items-center justify-center mb-4 transition-colors bg-blue-100 rounded-lg h-28 w-28 group-hover:bg-blue-600">
                                <img :src="team.image" alt="Feature image" class="object-contain w-20 h-20 transition-colors group-hover:filter group-hover:brightness-125">
                            </div>

                            <!-- Project Team Texts (gikan sa landing-page.js) -->
                            <h3 class="text-xl font-bold text-gray-900">@{{ team.name }}</h3>
                            <p class="mb-4 leading-relaxed text-gray-600">@{{ team.role }}</p>
                            <p class="leading-relaxed text-black-600">@{{ team.description }}</p>
                        </div>
                    </div>

                </div>
            </section>

            <!-- CTA Section -->
            <!-- <section class="py-20 bg-blue-50">
                <div class="container px-4 mx-auto sm:px-6 lg:px-8">

                    <div class="px-8 py-16 overflow-hidden text-center bg-blue-600 shadow-2xl rounded-2xl shadow-blue-500/20 fade-in">

                        <h2 class="mb-6 text-3xl font-black text-white sm:text-4xl">Ready to Transform Your Inventory Management?</h2>
                        <p class="max-w-2xl mx-auto mb-8 text-xl text-blue-100">
                            Join hundreds of hardware stores that have streamlined their operations with our system.
                        </p>

                        <div class="flex flex-col items-center justify-center max-w-md gap-4 mx-auto sm:flex-row">
                            <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-2 bg-white border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button class="flex items-center px-6 py-2 text-blue-600 transition-colors bg-white rounded-md hover:bg-blue-50">
                                Join now
                                <i class="ml-2 fas fa-arrow-right"></i>
                            </button>
                        </div>

                        <p class="mt-4 text-sm text-blue-200">
                            Start for free today and manage with ease anytime!
                        </p>
                    </div>
                </div>
            </section> -->

            <!-- Footer -->
            <footer id="contact" class="text-white bg-gray-900">
                <div class="container px-4 py-12 mx-auto sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                        <!-- Company Info -->
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="mb-4 text-2xl font-bold text-white">KitaKeeps</h3>
                            <p class="max-w-md mb-4 text-gray-300">
                                Specialized inventory management solutions for small and medium-sized hardware stores.
                            </p>

                            <!-- Social Links -->
                            <div class="flex space-x-4">
                                <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Product Links -->
                        <div>
                            <h4 class="mb-4 font-semibold text-white"></h4>
                            <ul class="space-y-2">
                                <li>
                                    <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                        
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                        
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-gray-300 transition-colors hover:text-white">
                                        
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Contact Info -->
                        <div>
                            <h4 class="mb-4 font-semibold text-white">Contact</h4>
                            <ul class="space-y-3">
                                <li class="flex items-center">
                                    <i class="mr-3 text-blue-400 fas fa-phone"></i>
                                    <span class="text-gray-300">0929 211 9698</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-blue-400 fas fa-envelope"></i>
                                    <span class="text-gray-300">kitakeeps@gmail.com</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="mr-3 text-blue-400 fas fa-map-marker-alt"></i>
                                    <span class="text-gray-300">Mabini, Davao de Oro</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- <div class="flex flex-col items-center justify-between pt-8 mt-8 border-t border-gray-800 sm:flex-row"> -->
                    <div class="flex flex-col items-center pt-8 mt-8 border-t border-gray-800">
                        <p class="text-sm text-gray-400">
                            © 2023 KitaKeeps. All rights reserved.
                        </p>
                        <!-- <div class="flex mt-4 space-x-6 sm:mt-0">
                            <a href="#" class="text-sm text-gray-400 transition-colors hover:text-white">
                                Privacy Policy
                            </a>
                            <a href="#" class="text-sm text-gray-400 transition-colors hover:text-white">
                                Terms of Service
                            </a>
                        </div> -->
                    </div>
                </div>
            </footer>
        </div>
    
    </x-guest-layout>
    @vite('resources/js/app.js')
</body>
</html>