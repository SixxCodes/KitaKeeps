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
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>

    <!-- Font Awesome’s icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/landing-page.css') }}">
</head>

<body class="bg-gradient-to-b from-blue-50 to-white">
    <div id="app">

        <!-- Header -->
        <header :class="{'nav-scrolled': scrolled}" class="fixed top-0 w-full z-50 transition-all duration-300 py-4">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="{{ route('home') }}"><img src="assets/images/logo/logo-removebg-preview.png" class="h-16 w-16 mr-2" alt="KitaKeeps Logo"></a>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('home') }}" class="text-3xl font-bold text-blue-600">KitaKeeps</a>
                        </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-8">
                            <a href="#features" class="text-gray-700 transition-colors hover:text-blue-600">
                                Features
                            </a>
                            <a href="#testimonials" class="text-gray-700 transition-colors hover:text-blue-600">
                                Testimonials
                            </a>
                            <a href="#project-team" class="text-gray-700 transition-colors hover:text-blue-600">
                                Team
                            </a>
                            <a href="#contact" class="text-gray-700 transition-colors hover:text-blue-600">
                                Contact
                            </a>
                        </div>
                    </nav>

                    <!-- Login and Signup Nav Button -->
                    <div class="hidden md:block space-x-4">
                        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-8 py-3 rounded-md hover:bg-blue-800 transition-colors">
                            Log in
                        </a>
                        <!-- <a href="signup.html" class="border border-blue-600 text-blue-600 px-4 py-3 rounded-md hover:bg-blue-50 transition-colors">
                            Sign up
                        </a> -->
                    </div>

                    <!-- Mobile Hamburger button (Hidden in desktop, ofc)-->
                    <div class="md:hidden">
                        <button @click="toggleMenu" class="text-gray-700 hover:text-blue-600">
                            <i :class="menuOpen ? 'fa-times' : 'fa-bars'" class="fas text-3xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div :class="{'open': menuOpen}" class="mobile-menu md:hidden">
                    <div class="space-y-1 border-t border-gray-200 px-2 pb-3 pt-2 sm:px-3">
                        <a href="#features" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                            Features
                        </a>
                        <a href="#testimonials" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                            Testimonials
                        </a>
                        <a href="#project-team" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                            Team
                        </a>
                        <a href="#contact" class="block px-3 py-2 text-gray-700 hover:text-blue-600">
                            Contact
                        </a>
                        <div class="px-3 py-2 space-x-2">
                            <a href="{{ route('login') }}" class="bg-blue-600 text-white px-10 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Log in
                            </a>
                            <a href="{{ route('register') }}" class="border border-blue-600 text-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition-colors">
                                Sign up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-32 pb-20 lg:pt-25 lg:pb-10">

            <!-- Round background elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-32 h-80 w-80 pulse-animation rounded-full bg-blue-200 opacity-30 mix-blend-multiply filter"></div>

                <div class="absolute -bottom-40 -left-32 h-80 w-80 pulse-animation rounded-full bg-blue-500 opacity-30 mix-blend-multiply filter"></div>
            </div>

            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2">
                    <div class="flex flex-col justify-center fade-in">

                        <!-- Landing page title -->
                        <h1 class="mb-6 text-4xl font-black leading-tight text-gray-900 sm:text-5xl lg:text-6xl z-10">
                            Smarter <span class="text-blue-600">Business Management</span>, Made Simple
                        </h1>

                        <!-- Description -->
                        <p class="mx-auto mb-8 max-w-2xl text-xl leading-relaxed text-gray-600">
                            Monitor, update, and organize hardware inventory with our powerful, easy-to-use system designed for <span class="text-blue-600">Building and Home Improvement Hardware Stores</span>.
                        </p>

                        <!-- Buttons -->
                        <div class="flex flex-col items-start gap-4 sm:flex-row">
                            <a href="{{ route('register') }}" class="bg-blue-600 px-8 py-3 text-white rounded-md hover:bg-blue-800 transition-colors flex items-center">
                                Get Started
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>

                            <a href="#why-kitakeeps" class="border border-blue-600 bg-transparent px-8 py-3 text-blue-600 rounded-md hover:bg-blue-600 hover:text-white transition-colors flex items-center">
                                <i class="fa-solid fa-circle-info mr-2"></i>
                                Learn More
                            </a>
                        </div>

                        <!-- Disclaimer -->
                        <div class="mt-12 z-10">
                            <p class="z-10 mb-4 text-sm text-gray-500">Built for small and medium enterprises (SMEs) specifically for building and home improvement hardware stores.</p>
                            <!-- <div class="flex flex-wrap items-center gap-6 opacity-60">
                                <div class="text-lg font-semibold text-gray-700">Zyrile Hardware</div>
                                <div class="text-lg font-semibold text-gray-700">True Value</div>
                                <div class="text-lg font-semibold text-gray-700">Local Pro</div>
                            </div> -->
                        </div>

                        <!-- Floating elements -->
                        <div class="absolute -left-2 top-9 z-0 h-24 w-24 float-animation w-24 h-24 bg-blue-200 rounded-full shadow-lg"></div>
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
                        <div class="absolute -right-4 -top-4 z-0 h-24 w-24 float-animation-2 w-24 h-24 bg-blue-200 rounded-full shadow-lg"></div>

                        <div class="absolute -bottom-6 right-6 z-0 h-32 w-32 float-animation-3 w-24 h-24 bg-blue-200 rounded-full shadow-lg"></div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 scroll-mt-16">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center fade-in">
                    <h2 class="mb-4 text-3xl font-black text-gray-900 sm:text-4xl">Built for Hardware Stores</h2>
                    <p class="mx-auto max-w-2xl text-xl text-gray-600">
                        Specialized features that understand the unique challenges of hardware inventory management.
                    </p>
                </div>

                <!-- Feature grids -->
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(feature, index) in features" :key="index" class="group border border-blue-200 rounded-lg p-6 transition-all duration-300 hover:shadow-lg hover:shadow-blue-900/20 slide-in" :style="`animation-delay: ${index * 0.1}s`">

                        <!-- Feature Icons (gikan sa landing-page.js) -->
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 transition-colors group-hover:bg-blue-600">
                            <i :class="feature.icon" class="text-blue-600 transition-colors group-hover:text-white text-xl"></i>
                        </div>

                        <!-- Feature Texts (gikan sa landing-page.js) -->
                        <h3 class="text-xl font-bold text-gray-900 mb-2">@{{ feature.title }}</h3>
                        <p class="leading-relaxed text-gray-600">@{{ feature.description }}</p>
                    </div>
                </div>

                <!-- Why KitaKeeps? -->
                <div id="why-kitakeeps" class="mt-20 grid gap-12 lg:grid-cols-2 scroll-mt-20">

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
                        <div class="absolute top-6 -right-3 z-0 h-20 w-20 pulse-animation rounded-xl bg-blue-100 opacity-50"></div>
                        <div class="absolute -bottom-6 -left-6 z-0 h-40 w-40 pulse-animation rounded-xl bg-blue-100 opacity-50"></div>
                    </div>
                    
                    <!-- Text Content -->
                    <div class="flex flex-col justify-center fade-in">
                        <h3 class="mb-4 text-5xl font-bold text-gray-900"><i class="fa-solid fa-book-open text-blue-600"></i> Why KitaKeeps?</h3>
                        <p class="mb-6 text-lg text-gray-600">
                            Turn the ordinary into extraordinary, and watch your store flourish with ease. KitaKeeps is your partner in growth, guiding every step along the way.
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Handle up to 100 concurrent users without lag</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Supports additional stores or branches</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Real-time sync</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Available offline</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Data is encrypted</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check mr-3 text-green-600"></i>
                                <span class="text-gray-700">Intuitive Interface for users with minimal training</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="bg-blue-50 py-20 scroll-mt-16">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center fade-in">
                    <h2 class="mb-4 text-3xl font-black text-gray-900 sm:text-4xl">Trusted by Hardware Professionals</h2>
                    <p class="mx-auto max-w-2xl text-xl text-gray-600">
                        See what hardware store owners are saying about our inventory system
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div v-for="(testimonial, index) in testimonials" :key="index" class="border border-blue-100 bg-white rounded-lg p-6 slide-in" :style="`animation-delay: ${index * 0.2}s`">
                        <div class="mb-4 flex">
                            <i v-for="star in testimonial.rating" class="fas fa-star text-yellow-600"></i>
                        </div>
                        <blockquote class="mb-4 leading-relaxed text-gray-700">
                            "@{{ testimonial.content }}"
                        </blockquote>
                        <div class="border-t border-blue-100 pt-4">
                            <div class="font-semibold text-gray-900">@{{ testimonial.name }}</div>
                            <div class="text-sm text-gray-600">@{{ testimonial.role }} at @{{ testimonial.company }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Project Team Section -->
        <section id="project-team" class="py-20 scroll-mt-16">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-16 text-center fade-in">
                    <h2 class="mb-4 text-3xl font-black text-blue-600 sm:text-5xl">Meet Our Team</h2>

                    <p class="mx-auto max-w-2xl text-xl text-gray-600">
                        The developers behind <span class="text-gray-900">KitaKeeps</span> — combining technical expertise and creative design to build an efficient hardware inventory management system.
                    </p>
                </div>

                <!-- Project Team grids -->
                <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(team, index) in team" :key="index" class="group border border-blue-400 rounded-lg p-4 transition-all duration-300 hover:shadow-lg hover:shadow-blue-900/20 slide-in flex flex-col items-center text-center" :style="`animation-delay: ${index * 0.1}s`">

                        <!-- Project Team Images (gikan sa landing-page.js) -->
                        <div class="mb-4 flex h-28 w-28 items-center justify-center rounded-lg bg-blue-100 transition-colors group-hover:bg-blue-600">
                            <img :src="team.image" alt="Feature image" class="h-20 w-20 object-contain transition-colors group-hover:filter group-hover:brightness-125">
                        </div>

                        <!-- Project Team Texts (gikan sa landing-page.js) -->
                        <h3 class="text-xl font-bold text-gray-900">@{{ team.name }}</h3>
                        <p class="leading-relaxed text-gray-600 mb-4">@{{ team.role }}</p>
                        <p class="leading-relaxed text-black-600">@{{ team.description }}</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-blue-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">

                <div class="overflow-hidden rounded-2xl bg-blue-600 px-8 py-16 text-center shadow-2xl shadow-blue-500/20 fade-in">

                    <h2 class="mb-6 text-3xl font-black text-white sm:text-4xl">Ready to Transform Your Inventory Management?</h2>
                    <p class="mx-auto mb-8 max-w-2xl text-xl text-blue-100">
                        Join hundreds of hardware stores that have streamlined their operations with our system.
                    </p>

                    <div class="mx-auto flex max-w-md flex-col items-center justify-center gap-4 sm:flex-row">
                        <input type="email" placeholder="Enter your email" class="flex-1 border border-blue-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button class="bg-white px-6 py-2 text-blue-600 rounded-md hover:bg-blue-50 transition-colors flex items-center">
                            Join now
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <p class="mt-4 text-sm text-blue-200">
                        Start for free today and manage with ease anytime!
                    </p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="contact" class="bg-gray-900 text-white">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <!-- Company Info -->
                    <div class="col-span-1 md:col-span-2">
                        <h3 class="mb-4 text-2xl font-bold text-white">KitaKeeps</h3>
                        <p class="mb-4 max-w-md text-gray-300">
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
                                <i class="fas fa-phone mr-3 text-blue-400"></i>
                                <span class="text-gray-300">0929 211 9698</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-3 text-blue-400"></i>
                                <span class="text-gray-300">kitakeeps@gmail.com</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-3 text-blue-400"></i>
                                <span class="text-gray-300">Mabini, Davao de Oro</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- <div class="mt-8 flex flex-col items-center justify-between border-t border-gray-800 pt-8 sm:flex-row"> -->
                <div class="mt-8 flex flex-col items-center border-t border-gray-800 pt-8">
                    <p class="text-sm text-gray-400">
                        © 2023 KitaKeeps. All rights reserved.
                    </p>
                    <!-- <div class="mt-4 flex space-x-6 sm:mt-0">
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

    <script src="{{ asset('assets/js/landing-page.js') }}"></script>
</body>
</html>