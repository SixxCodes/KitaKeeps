@php
    use App\Models\AuditLog;
    $user = auth()->user();

    // Get branch IDs user belongs to
    $branchIds = $user->branches->pluck('branch_id');

    // Fetch audit logs for users in the same branches
    $auditLogs = AuditLog::whereHas('audit_logbelongsToUser', function($query) use ($branchIds) {
        $query->whereHas('branches', function($q) use ($branchIds) {
            $q->whereIn('user_branch.branch_id', $branchIds); // <-- fully qualify the column
        });
        })->orderByDesc('created_at')->get();

@endphp

<!-- Dark Mode -->
<!-- <div x-data="{ dark: localStorage.getItem('dark') === 'true' }"
    x-init="$watch('dark', value => {
        document.documentElement.classList.toggle('dark', value);
        localStorage.setItem('dark', value);
    })"
    >
    <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
        <div class="flex items-center space-x-3">
            <i class="text-xl text-blue-600 fa-solid fa-moon"></i>
            <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">Dark Mode</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Switch between light and dark theme.</p>
            </div>
        </div>

        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" x-model="dark" class="sr-only peer">
            <div class="h-6 bg-gray-200 rounded-full w-11 peer peer-checked:bg-blue-600"></div>
            <div class="absolute w-5 h-5 bg-white rounded-full left-1 top-0.5 peer-checked:translate-x-full transition"></div>
        </label>
    </div>
</div> -->

<div class="p-6 space-y-8">

    <!-- Title -->
    <!-- <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Settings</h2>
        <p class="text-sm text-gray-500">Manage your preferences and system options below.</p>
    </div> -->

    <!-- ================= Category 1: Cloud & Logs ================= -->
    <div class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-800">Cloud & Activity</h2>
        <p class="text-sm text-gray-500">View and manage your cloud files and system activity.</p>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

            <!-- Your Files -->
            <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-indigo-600 fa-solid fa-cloud"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Cloud Files</p>
                        <p class="text-sm text-gray-500">View files you’ve stored in the cloud.</p>
                    </div>
                </div>
                <button 
                    x-data 
                    x-on:click="$dispatch('open-modal', 'cloud-files-modal')" 
                    class="px-3 py-1 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    View
                </button>
            </div>

            <!-- Audit Log -->
            <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-red-600 fa-solid fa-clipboard-list"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Audit Log</p>
                        <p class="text-sm text-gray-500">View system activities and records.</p>
                    </div>
                </div>
                <button 
                    x-data 
                    x-on:click="$dispatch('open-modal', 'audit-log')" 
                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                    Open
                </button>
            </div>

        </div>
    </div>

    <!-- ================= Category 2: Preferences & Help ================= -->
    <div class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-800">Preferences & Support</h2>
        <p class="text-sm text-gray-500">Adjust interface settings and get help or information.</p>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2">

            <!-- Font Size -->
            <div x-data="{ fontSize: localStorage.getItem('fontSize') || 'medium' }"
                x-init="
                    // Apply the saved font size immediately
                    document.documentElement.style.fontSize = {
                        small: '14px',
                        medium: '16px',
                        large: '18px'
                    }[fontSize];

                    // Watch for changes and update both DOM + localStorage
                    $watch('fontSize', value => {
                        document.documentElement.style.fontSize = {
                            small: '14px',
                            medium: '16px',
                            large: '18px'
                        }[value];
                        localStorage.setItem('fontSize', value);
                    });
                "
                class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md"
            >
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-green-600 fa-solid fa-text-height"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Font Size</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Adjust the interface text size.</p>
                    </div>
                </div>

                <select x-model="fontSize" class="px-2 py-1 text-sm border rounded dark:bg-gray-700 dark:text-gray-200">
                    <option value="small">Small</option>
                    <option value="medium">Medium</option>
                    <option value="large">Large</option>
                </select>
            </div>

            <!-- Check for Updates -->
            <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-yellow-600 fa-solid fa-rotate"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Check for Updates</p>
                        <p class="text-sm text-gray-500">Ensure you’re running the latest version.</p>
                    </div>
                </div>
                <button class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700" x-on:click="$dispatch('open-modal', 'update-modal');">
                    Check
                </button>
            </div>

            <!-- Terms & Services -->
            <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-purple-600 fa-solid fa-file-contract"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Terms & Services</p>
                        <p class="text-sm text-gray-500">Read our terms and conditions.</p>
                    </div>
                </div>
                <button 
                    x-data 
                    x-on:click="$dispatch('open-modal', 'terms-and-services-modal')" 
                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                    View
                </button>
            </div>

            <!-- Help -->
            <div class="flex items-center justify-between p-4 transition bg-white border rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="flex items-center space-x-3">
                    <i class="text-xl text-indigo-600 fa-solid fa-circle-question"></i>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Help</p>
                        <p class="text-sm text-gray-500">Get assistance or support.</p>
                    </div>
                </div>
                <button 
                    x-data 
                    x-on:click="$dispatch('open-modal', 'help-modal')" 
                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                    Open
                </button>
            </div>

        </div>
    </div>
</div>


<!-- Footer Branding -->
<footer class="py-4 text-sm text-center text-gray-400 border-t mt-15">
    © 2025 KitaKeeps. All rights reserved.
</footer>

<!-- Modal -->
<x-modal name="update-modal" :show="false" maxWidth="sm">
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 3000)" class="p-6 text-center">
        <!-- Loading -->
        <div x-show="loading" class="flex flex-col items-center space-y-3">
            <svg class="w-10 h-10 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="font-medium text-gray-700">Checking for updates...</p>
        </div>

        <!-- Success Message -->
        <div x-show="!loading" x-transition class="flex flex-col items-center space-y-3">
            <i class="text-green-600 fa-solid fa-circle-check fa-2x"></i>
            <h2 class="mt-2 text-lg font-semibold text-gray-800">No Updates</h2>
            <p class="text-gray-600">You’re good to go! Everything is up to date.</p>
            <button type="button"
                class="px-4 py-2 mt-4 text-white bg-green-600 rounded hover:bg-green-700"
                x-on:click="$dispatch('close-modal', 'update-modal')">
                Awesome!
            </button>
        </div>
    </div>
</x-modal>

<!-- Terms & Services Modal -->
<x-modal name="terms-and-services-modal" :show="false" maxWidth="2xl">
    <div class="p-6 max-h-[80vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Terms of Service & Privacy Policy</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600"
                x-on:click="$dispatch('close-modal', 'terms-and-services-modal')"
            >
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Terms of Service -->
        <section class="p-5 mb-6 rounded-lg shadow-sm bg-gray-50">
            <h3 class="mb-2 text-lg font-semibold text-gray-800">Terms of Service</h3>
            <p class="mb-3 text-gray-600">Effective Date: October 3, 2025</p>
            <p class="mb-3 text-gray-700">Welcome to KitaKeeps, a web-based management and forecasting system designed to help SMEs, particularly hardware stores, modernize and streamline operations. By accessing and using our services, you agree to the following Terms:</p>

            <div class="space-y-2 text-gray-700">
                <h4 class="font-semibold">Purpose of the Service</h4>
                <p>KitaKeeps supports SMEs in the Philippines and beyond by providing cloud-based inventory management, employee tracking, customer recordkeeping, and AI-powered forecasting.</p>

                <h4 class="font-semibold">User Responsibilities</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Provide accurate business info (inventory, sales, employee records).</li>
                    <li>Maintain secure access to your account.</li>
                    <li>Use the system only for legitimate business purposes.</li>
                </ul>

                <h4 class="font-semibold">Forecasting and Limitations</h4>
                <p>KitaKeeps uses AI to forecast demand and support decision-making. Forecasts are estimates and do not guarantee outcomes. KitaKeeps is not liable for losses from reliance on predictions.</p>

                <h4 class="font-semibold">Data Ownership & Intellectual Property</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>All business data entered into KitaKeeps belongs to the SME.</li>
                    <li>The software platform and features remain KitaKeeps' intellectual property.</li>
                </ul>

                <h4 class="font-semibold">Subscriptions & Access</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Some features may require a subscription.</li>
                    <li>Payments are recurring unless canceled.</li>
                    <li>Access is via cloud services for real-time multi-location use.</li>
                </ul>

                <h4 class="font-semibold">Termination of Service</h4>
                <p>We may suspend accounts that misuse the system or violate Terms. Users can close their accounts anytime.</p>

                <h4 class="font-semibold">Governing Law</h4>
                <p>These Terms are governed by the laws of the Republic of the Philippines.</p>
            </div>
        </section>

        <!-- Privacy Policy -->
        <section class="p-5 mb-6 rounded-lg shadow-sm bg-gray-50">
            <h3 class="mb-2 text-lg font-semibold text-gray-800">Privacy Policy</h3>
            <p class="mb-3 text-gray-600">Effective Date: October 3, 2025</p>
            <p class="mb-3 text-gray-700">KitaKeeps is committed to protecting SME privacy. This explains how we handle your data responsibly.</p>

            <div class="space-y-2 text-gray-700">
                <h4 class="font-semibold">Data We Collect</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Business Data: Inventory, sales, supplier & customer details, employee records.</li>
                    <li>Personal Info: Account holder’s name, email, billing info.</li>
                    <li>Technical Data: Device info, IP, cookies, usage logs.</li>
                </ul>

                <h4 class="font-semibold">Why We Collect It</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Support SMEs in efficiency, forecasting, and record-keeping.</li>
                    <li>Allow real-time collaboration via cloud.</li>
                    <li>Process subscriptions, reports, and improve accuracy.</li>
                </ul>

                <h4 class="font-semibold">Data Sharing</h4>
                <p>We do not sell business or personal data. Shared only with trusted providers or when legally required.</p>

                <h4 class="font-semibold">Security</h4>
                <p>Cloud encryption and access controls protect sensitive business data. No system is fully secure.</p>

                <h4 class="font-semibold">Data Retention</h4>
                <p>Business data is stored as long as your account is active or required by law. Users may request deletion upon account closure.</p>

                <h4 class="font-semibold">User Rights</h4>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Access stored records anytime.</li>
                    <li>Request corrections to inaccurate data.</li>
                    <li>Request deletion, subject to legal requirements.</li>
                </ul>

                <h4 class="font-semibold">Children’s Privacy</h4>
                <p>Intended for business use only; not for individuals under 18.</p>

                <h4 class="font-semibold">Updates</h4>
                <p>Policy may update as technology, regulations, or SME needs change. Users will be notified.</p>
            </div>
        </section>

        <!-- Close Button -->
        <div class="flex justify-end">
            <button 
                type="button" 
                class="px-5 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700"
                x-on:click="$dispatch('close-modal', 'terms-and-services-modal')"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Audit Log Modal -->
<x-modal name="audit-log" :show="false" maxWidth="2xl">
    <div class="p-6 overflow-y-auto max-h-[80vh]">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-blue-900 dark:text-gray-100">Audit Log</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'audit-log')"
            >
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">User</th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Branch</th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Action</th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Details</th>
                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($auditLogs as $log)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $log->audit_logbelongsToUser->username ?? 'Unknown' }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $log->audit_logbelongsToUser->branches->pluck('branch_name')->join(', ') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $log->details }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Close Button -->
        <div class="flex justify-end mt-4">
            <button 
                type="button" 
                class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                x-on:click="$dispatch('close-modal', 'audit-log')"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Help Modal -->
<x-modal name="help-modal" :show="false" maxWidth="sm">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-blue-900">Need Help?</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600"
                x-on:click="$dispatch('close-modal', 'help-modal')"
            >
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Socials Grid -->
        <section>
            <h3 class="mb-3 text-lg font-semibold text-blue-800">Follow us on Socials</h3>
            <div class="w-full">
                <!-- Facebook -->
                <a href="https://www.facebook.com/profile.php?id=61581974522036" target="_blank" 
                   class="flex flex-col items-center p-4 transition rounded-lg shadow bg-blue-50 hover:shadow-md">
                    <i class="text-4xl text-blue-600 fa-brands fa-facebook"></i>
                    <span class="mt-2 font-medium text-gray-700">Facebook</span>
                </a>
            </div>
        </section>

        <!-- Contact Emails -->
        <!-- <section>
            <h3 class="mb-3 text-lg font-semibold text-blue-800">Contact Us</h3>
            <div class="grid grid-cols-1 gap-2 text-gray-700 sm:grid-cols-2">
                <a href="mailto:vienugay@gmail.com" class="underline hover:text-blue-600">vienugay@gmail.com</a>
                <a href="mailto:merryfeguisihan@gmail.com" class="underline hover:text-blue-600">merryfeguisihan@gmail.com</a>
                <a href="mailto:crisostomokennymadayag@gmail.com" class="underline hover:text-blue-600">crisostomokennymadayag@gmail.com</a>
                <a href="mailto:latoza@gmail.com" class="underline hover:text-blue-600">latoza@gmail.com</a>
                <a href="mailto:mapuro@gmail.com" class="underline hover:text-blue-600">mapuro@gmail.com</a>
                <a href="mailto:nabre2@gmail.com" class="underline hover:text-blue-600">nabre2@gmail.com</a>
            </div>
        </section> -->

        <!-- Close Button -->
        <div class="flex justify-end mt-4">
            <button 
                type="button" 
                class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700"
                x-on:click="$dispatch('close-modal', 'help-modal')"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>

<!-- Cloud Files Modal -->
<x-modal name="cloud-files-modal" :show="false" maxWidth="2xl">
    <div class="p-6 max-h-[80vh] overflow-y-auto">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-blue-900 dark:text-gray-100">Cloud Files</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                x-on:click="$dispatch('close-modal', 'cloud-files-modal')"
            >
                <i class="text-lg fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left border-collapse table-auto">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="px-4 py-2 border whitespace-nowrap">Filename</th>
                        <th class="px-4 py-2 border whitespace-nowrap">URL</th>
                        <th class="px-4 py-2 border whitespace-nowrap">File Size</th>
                        <th class="px-4 py-2 border whitespace-nowrap">Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(auth()->user()->files as $file)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-2 border whitespace-nowrap">{{ $file->filename }}</td>
                            <td class="px-4 py-2 border whitespace-nowrap">
                                <a href="{{ $file->file_url }}" target="_blank" class="text-indigo-600 hover:underline">
                                    View File
                                </a>
                            </td>
                            <td class="px-4 py-2 border whitespace-nowrap">{{ number_format($file->file_size / 1024, 2) }} KB</td>
                            <td class="px-4 py-2 border whitespace-nowrap">{{ $file->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                    @if(auth()->user()->files->isEmpty())
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">No files found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-6">
            <button 
                x-on:click="$dispatch('close-modal', 'cloud-files-modal')" 
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                Close
            </button>
        </div>

    </div>
</x-modal>