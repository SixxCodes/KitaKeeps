<!-- Module Header -->
<div class="flex items-center justify-between" x-data>
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
        <!-- Take Attendance -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'take-attendance')" class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-file-pen"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Take Attendance</span> 
            </button>
        </div>

        <!-- Export -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'export-options')" class="flex items-center px-5 py-2 text-xs text-black transition-colors bg-white rounded-md shadow hover:bg-blue-300 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-download"></i>
                <span class="hidden ml-2 lg:inline">Export</span>
            </button>
        </div>

        <!-- Add Employee -->
        <div class="flex items-center space-x-4">
            <button x-on:click="$dispatch('open-modal', 'add-employee')" class="flex items-center px-5 py-2 text-xs text-white transition-colors bg-blue-600 rounded-md shadow hover:bg-blue-800 sm:text-xs md:text-xs lg:text-sm">
                <i class="fa-solid fa-user-plus"></i>
                <span class="hidden ml-2 lg:inline whitespace-nowrap">Hire Employee</span>
            </button>
        </div>
    </div>
</div>

<!-- Hire Employee Modal -->
<x-modal name="add-employee" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="mr-2 fa-solid fa-user-plus"></i>
                <h2 class="text-xl font-semibold">Hire Employee</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'add-employee')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  
        
        <!-- Form -->
        <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm"
            x-data="{ position: '' }">
            @csrf <!-- Laravel CSRF -->
            
            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <img id="preview-employee" src="assets/images/logo/logo-removebg-preview.png" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="Employee photo">

                    <!-- Upload button -->
                    <label for="employee_image" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-blue-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>
                    <input type="file" id="employee_image" name="employee_image_path" class="hidden" accept="image/*"
                        onchange="document.getElementById('preview-employee').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <p class="mt-2 text-sm text-gray-500">Add profile photo</p>
            </div>

            <!-- Personal Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Personal Information</legend>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">

                <!-- First Name -->
                <div>
                    <label class="block mb-1 text-gray-800">First Name</label>
                    <input required type="text" name="firstname" placeholder="Kita" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block mb-1 text-gray-800">Last Name</label>
                    <input required type="text" name="lastname" placeholder="Keeper" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block mb-1 text-gray-800">Gender</label>
                    <select required name="gender" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500">
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block mb-1 text-gray-800">Contact Number</label>
                    <input 
                            maxlength="11"
                            pattern="\d{11}"
                            title="Contact number must be exactly 11 digits" 
                            required 
                            name="contact_number" 
                            type="text" 
                            placeholder="09123456789"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Email -->
                <div class="sm:col-span-2">
                    <label class="block mb-1 text-gray-800">Email</label>
                    <input required type="email" name="email" placeholder="example@email.com" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label class="block mb-1 text-gray-800">Address</label>
                    <input required type="text" name="address" placeholder="123 Main St, City" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                </div>

                </div>
            </fieldset>

            <!-- Job Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Job Information</legend>
                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Position Dropdown -->
                    <div>
                        <label class="block mb-1 text-gray-800">Position</label>
                        <select required name="position" x-model="position"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500">
                            <option value="" disabled selected>Select Position</option>
                            <option value="Admin">Admin</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Daily Salary -->
                    <div>
                        <label class="block mb-1 text-gray-800">Daily Salary</label>
                        <input required type="number" name="daily_rate" placeholder="500"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"/>
                    </div>
                </div>
                
                <!-- Username & Password for Admin/Cashier -->
                <div x-show="position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin'" class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <!-- Username -->
                    <div>
                        <label class="block mb-1 text-gray-800">Username</label>
                        <input 
                            type="text" 
                            name="username"
                            placeholder="e.g., john.doe"
                            x-bind:required="position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin'"
                            x-bind:pattern="(position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin') ? '^[a-zA-Z][a-zA-Z0-9._]{2,19}$' : ''"
                            title="Username must start with a letter, 3-20 characters, letters, numbers, dots or underscores only" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-1 text-gray-800">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Enter password"
                            x-bind:required="position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin'"
                            x-bind:pattern="(position.toLowerCase() === 'cashier' || position.toLowerCase() === 'admin') ? '(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}' : ''"
                            title="Password must be at least 8 characters, include 1 uppercase letter and 1 special character"
                            class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500"
                        />
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                x-on:click="$dispatch('close-modal', 'add-employee')"
                class="px-3 py-1 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">Cancel</button>

                <button type="submit" 
                class="px-3 py-1 text-white transition bg-green-600 rounded hover:bg-green-700">Save</button>
            </div>
        </form>

    </div>
</x-modal>

<!-- Export Modal -->
<x-modal name="export-options" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <h2 class="text-lg font-semibold text-gray-800">Export As</h2>

        <!-- Info about sheets -->
        <p class="text-sm text-gray-500">
            Attendance and Employee details will be exported on <strong>separate sheets</strong> in the Excel file.
        </p>

        <form method="GET" action="{{ route('employees.export') }}" class="mt-4 space-y-2">
            <!-- Export Button -->
            <button 
                type="submit"
                class="flex flex-col items-center px-4 py-3 mx-auto transition bg-blue-100 rounded-lg shadow w-28 hover:bg-blue-200"
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
                    class="mr-2 text-blue-600 border-gray-300 rounded focus:ring-blue-400"
                >
                Sync to Cloud
            </label>

            <!-- Subtext -->
            <p class="text-[11px] text-gray-400 leading-tight">
                If unchecked, the file will only be downloaded locally.
            </p>
        </form>

        <!-- Cancel -->
        <div class="flex justify-center mt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'export-options')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >Cancel</button>
        </div>
    </div>
</x-modal>

<!-- Attendance Modal -->
<x-modal name="take-attendance" :show="false" maxWidth="lg">
    <div class="p-6 max-h-[80vh] overflow-y-auto">

        <h2 class="text-xl font-semibold text-center text-gray-800">Attendance for Today</h2>
        <p class="mb-5 text-sm text-center text-gray-500">
            Take attendance for <span class="font-medium">{{ now()->format('F d, Y (l)') }}</span>.
        </p>
        
        <div class="flex items-center justify-between mb-4 whitespace-nowrap">
            <div>
                <label class="mr-2 text-sm text-ellipsis sm:text-base">Show</label>
                <select onchange="window.location.href='?per_page='+this.value" class="px-5 py-1 text-sm border rounded">
                    <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                    <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                    <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
                </select>
                <span class="ml-2 text-sm">entries</span>
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

        <!-- Table Container -->
        <form method="POST" action="{{ route('attendance.mark')}}"> 
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-3 py-2 text-left border">#</th>
                            <th class="px-3 py-2 border">ID</th>
                            <th class="px-3 py-2 border">Employee Name</th>
                            <th class="px-3 py-2 border">Role</th>
                            <th class="px-3 py-2 border">Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-x divide-y divide-blue-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-100">
                                <!-- Count -->
                                <!-- <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td> -->
                                <td class="px-3 py-2 border bg-blue-50">
                                    {{ $employees->firstItem() + $loop->index }}
                                </td>

                                <!-- ID -->
                                <td class="px-3 py-2 border">{{ $employee->employee_id }}</td>

                                <!-- Name -->
                                <td class="px-3 py-2 border">
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                            {{ $employee->person->firstname }} {{ $employee->person->lastname }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Position -->
                                <td class="px-3 py-2 border">
                                    <span class="inline-block px-3 py-1 text-xs text-white {{ in_array($employee->position,['Cashier','Admin']) ? 'bg-orange-400' : 'bg-blue-400' }} rounded-full">
                                        {{ $employee->position }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-3 py-2 text-center border">
                                    <input 
                                        type="checkbox" 
                                        name="present[]" 
                                        value="{{ $employee->employee_id }}" 
                                        class="w-5 h-5 text-green-600"
                                        @if($employee->todayAttendance && $employee->todayAttendance->status === 'Present') checked @endif
                                    >
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-center text-gray-500 border">
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
                    Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries
                </p>
                <!-- Previous / Next -->
                <div class="flex gap-2">
                    <!-- Previous button -->
                    <a 
                        href="{{ $employees->previousPageUrl() }}" 
                        class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        Previous
                    </a>

                    <!-- Next button -->
                    <a 
                        href="{{ $employees->nextPageUrl() }}" 
                        class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                        Next
                    </a>
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button 
                    x-on:click="$dispatch('close-modal', 'take-attendance')"
                    type="button"
                    class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                    Cancel
                </button>

                <button type="submit" 
                    class="px-4 py-2 text-white transition bg-green-600 rounded hover:bg-green-700">
                    Save
                </button>
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








<!-- ==================== ATTENDANCE ==================== -->
<h3 class="mt-8 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">Attendance</h3>

<div class="p-4 mt-3 bg-white rounded-lg shadow">
    <!-- Search + Entries -->
    <div class="flex items-center justify-between mb-4 whitespace-nowrap">
        <div>
            <label class="mr-2 text-sm text-ellipsis sm:text-base">Show</label>
            <select onchange="window.location.href='?per_page='+this.value" class="py-1 text-sm border rounded">
                <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
            </select>
            <span class="ml-2 text-sm">entries</span>
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

    <div class="overflow-x-auto table-pretty-scrollbar">
        <!-- Table -->
        <table class="min-w-full text-sm border">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 text-left border">#</th>
                    <th class="px-3 py-2 text-left border">ID</th>
                    <th class="px-3 py-2 text-left border">Employee Name</th>
                    <th class="px-3 py-2 text-left border whitespace-nowrap">Daily Rate</th>
                    <th class="px-3 py-2 text-left border">Mon</th>
                    <th class="px-3 py-2 text-left border">Tue</th>
                    <th class="px-3 py-2 text-left border">Wed</th>
                    <th class="px-3 py-2 text-left border">Thu</th>
                    <th class="px-3 py-2 text-left border">Fri</th>
                    <th class="px-3 py-2 text-left border">Sat</th>
                    <th class="px-3 py-2 text-left border">Sun</th>
                    <th class="px-3 py-2 text-left border">Total Salary</th>
                </tr>
            </thead>
            <tbody>
                <!-- Employee Rows -->
                @forelse($employees as $employee)
                <tr class="hover:bg-gray-50">
                    <!-- Count -->
                    <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td>

                    <!-- ID -->
                    <td class="px-3 py-2 border">{{ $employee->employee_id }}</td>

                    <!-- Image and Name -->
                    <td class="px-3 py-2 border">
                        <div class="flex items-center gap-2">
                            <!-- Circle image or placeholder -->
                            @if($employee->employee_image_path)
                                <img 
                                    src="{{ asset('storage/' . $employee->employee_image_path) }}" 
                                    alt="{{ $employee->employee_name }}" 
                                    class="object-cover w-8 h-8 rounded-full"
                                >
                            @else
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            @endif
                            <!-- Name -->
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                {{ $employee->person->firstname }} {{ $employee->person->lastname }}
                            </span>
                        </div>
                    </td>

                    <td class="px-3 py-2 border">{{ number_format($employee->daily_rate, 2) }}</td>

                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                    <td class="px-3 py-2 text-center border">
                        @php
                            $att = $employee->attendance->where('att_date', now()->startOfWeek()->addDays(array_search($day,['Mon','Tue','Wed','Thu','Fri','Sat','Sun'])))->first();
                        @endphp
                        @if($att && $att->status === 'Present')
                            <i class="text-green-500 fa-solid fa-circle-check"></i>
                        @elseif($att && $att->status === 'Absent')
                            <i class="text-red-500 fa-solid fa-circle-xmark"></i>
                        @else
                            <i class="text-gray-400 fa-solid fa-minus"></i>
                        @endif
                    </td>
                    @endforeach

                    @php
                        $totalSalary = $employee->attendance
                            ->whereBetween('att_date', [now()->startOfWeek(), now()->endOfWeek()])
                            ->where('status', 'Present')
                            ->count() * $employee->daily_rate;
                    @endphp

                    <td class="px-3 py-2 text-right border whitespace-nowrap">
                        P{{ number_format($totalSalary, 2) }}

                        <button x-on:click="$dispatch('open-modal', 'pay-salary-confirm-{{ $employee->employee_id }}')" 
                            class="px-2 py-1 text-white bg-green-500 rounded">
                            <i class="fa-solid fa-money-bill"></i>
                        </button>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-3 py-2 text-center text-gray-500 border">
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
            Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries
        </p>
        <!-- Previous / Next -->
        <div class="flex gap-2">
            <!-- Previous button -->
            <a 
                href="{{ $employees->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>

            <!-- Next button -->
            <a 
                href="{{ $employees->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
    
</div>

<!-- Pay Salary Confirmation Modal -->
@foreach($employees as $employee)
<x-modal name="pay-salary-confirm-{{ $employee->employee_id }}" :show="false" maxWidth="sm">
    <div class="p-6 space-y-4 text-center">

        <i class="mx-auto text-4xl text-yellow-400 fa-solid fa-triangle-exclamation"></i>

        <h2 class="text-lg font-semibold text-gray-800">Are you sure?</h2>
        <p class="text-sm text-gray-500">
            This will process the salary for <span class="font-medium">{{ $employee->person->firstname }} {{ $employee->person->lastname }}</span>. 
            This action cannot be undone.
        </p>

        <div class="flex justify-center mt-4 space-x-3">
            <button
                x-on:click="$dispatch('close-modal', 'pay-salary-confirm-{{ $employee->employee_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300"
            >
                Cancel
            </button>

            <form method="POST" action="{{ route('pay-salary', $employee->employee_id) }}">
                @csrf
                <button
                    type="submit"
                    class="px-4 py-2 text-white transition bg-green-600 rounded hover:bg-green-700"
                >
                    Yes, Pay
                </button>
            </form>
        </div>

    </div>
</x-modal>
@endforeach










<!-- ==================== All Employees ==================== -->
<h3 class="mt-5 text-blue-600 sm:text-sm md:text-sm lg:text-lg text-shadow-lg">All Employees</h3>

<div class="p-4 mt-3 bg-white rounded-lg shadow pb-50">
    <!-- Search + Entries -->
    <div class="flex items-center justify-between mb-4 whitespace-nowrap">
        <div>
            <label class="mr-2 text-sm text-ellipsis sm:text-base">Show</label>
            <select onchange="window.location.href='?per_page='+this.value" class="py-1 text-sm border rounded">
                <option value="5" @if(request('per_page',5)==5) selected @endif>5</option>
                <option value="10" @if(request('per_page',5)==10) selected @endif>10</option>
                <option value="25" @if(request('per_page',5)==25) selected @endif>25</option>
            </select>
            <span class="ml-2 text-sm">entries</span>
        </div>

        <div class="flex space-x-2">
            <!-- Role Filter -->
            <div class="flex items-center px-2 py-1 text-sm border rounded cursor-pointer">
                <i class="mr-2 text-blue-400 fa-solid fa-filter"></i>
                <select onchange="window.location.href='?per_page={{ request('per_page',5) }}&search={{ request('search') }}&role='+this.value" class="text-sm bg-transparent border-none outline-none">
                    <option value="all" @if(request('role','all')=='all') selected @endif>All Roles</option>
                    <option value="Admin" @if(request('role')=='Admin') selected @endif>Admin</option>
                    <option value="Cashier" @if(request('role')=='Cashier') selected @endif>Cashier</option>
                    <option value="Other" @if(request('role')=='Other') selected @endif>Other</option>
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
                    <th class="px-3 py-2 text-left border">Employee Name</th>
                    <th class="px-3 py-2 text-left border">Email</th>
                    <th class="px-3 py-2 text-left border">Username</th>
                    <th class="px-3 py-2 text-left border">Role</th>
                    <th class="px-3 py-2 text-left border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <!-- Employee Rows -->
                <tr class="hover:bg-gray-50">
                    <!-- Count -->
                    <!-- <td class="px-3 py-2 border bg-blue-50">{{ $loop->iteration }}</td> -->
                    <td class="px-3 py-2 border bg-blue-50">
                        {{ $employees->firstItem() + $loop->index }}
                    </td>

                    <!-- ID -->
                    <td class="px-3 py-2 border">{{ $employee->employee_id }}</td>

                    <!-- Image and Name -->
                    <td class="px-3 py-2 border">
                        <div class="flex items-center gap-2">
                            <!-- Circle image or placeholder -->
                            @if($employee->employee_image_path)
                                <img 
                                    src="{{ asset('storage/' . $employee->employee_image_path) }}" 
                                    alt="{{ $employee->employee_name }}" 
                                    class="object-cover w-8 h-8 rounded-full"
                                >
                            @else
                                <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-200 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            @endif
                            <!-- Name -->
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis">
                                {{ $employee->person->firstname }} {{ $employee->person->lastname }}
                            </span>
                        </div>
                    </td>

                    <td class="px-3 py-2 border">{{ $employee->person->email }}</td>

                    <td class="px-3 py-2 border">{{ $employee->person->user->username ?? '-' }}</td>

                    <td class="px-3 py-2 border">
                        @if($employee->position == 'Cashier' || $employee->position == 'Admin')
                            <span class="inline-block px-3 py-1 text-xs text-white bg-orange-400 rounded-full">
                                {{ $employee->position }}
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 text-xs text-white bg-blue-400 rounded-full">
                                {{ $employee->position }}
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="flex justify-center gap-2 px-3 py-3 border">
                        <button x-on:click="$dispatch('open-modal', 'view-employee-{{ $employee->employee_id }}')" class="px-2 py-1 text-white bg-blue-500 rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button x-on:click="$dispatch('open-modal', 'edit-employee-{{ $employee->employee_id }}')"  class="px-2 py-1 text-white bg-green-500 rounded">
                            <i class="fa-solid fa-user-pen"></i>
                        </button>
                        <button x-on:click="$dispatch('open-modal', 'delete-employee-{{ $employee->employee_id }}')" class="px-2 py-1 text-white bg-red-500 rounded">
                            <i class="fa-solid fa-user-minus"></i>
                        </button>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-2 text-center text-gray-500 border">
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
            Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries
        </p>
        <!-- Previous / Next -->
        <div class="flex gap-2">
            <!-- Previous button -->
            <a 
                href="{{ $employees->previousPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                Previous
            </a>

            <!-- Next button -->
            <a 
                href="{{ $employees->nextPageUrl() }}" 
                class="px-3 py-1 text-sm border rounded hover:bg-blue-700 {{ $employees->hasMorePages() ? '' : 'opacity-50 pointer-events-none' }}">
                Next
            </a>
        </div>
    </div>
</div>

@foreach($employees as $employee)
<!-- View Employee Details Modal -->
<x-modal name="view-employee-{{ $employee->employee_id }}" :show="false" maxWidth="sm">
    <div class="p-6">
        <!-- Profile Section -->
        <div class="flex items-center space-x-4">
            <!-- Employee Icon / Image -->
            <div class="flex items-center justify-center w-20 h-20 overflow-hidden text-3xl text-white bg-blue-400 rounded-full">
                @if($employee->employee_image_path)
                    <img src="{{ asset('storage/' . $employee->employee_image_path) }}" 
                         alt="{{ $employee->person->firstname }} {{ $employee->person->lastname }}" 
                         class="object-cover w-full h-full rounded-full">
                @else
                    <i class="fa-solid fa-user"></i>
                @endif
            </div>

            <!-- Name + Role -->
            <div>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $employee->person->firstname }} {{ $employee->person->lastname }}
                </p>
                <p class="text-sm text-gray-500">{{ $employee->position }}</p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t"></div>

        <!-- Contact Details -->
        <div class="space-y-4 text-sm text-gray-700">

            <!-- Personal Info -->
            <div>
                <h3 class="mb-2 text-xs font-semibold tracking-wide text-blue-500 uppercase">Personal Information</h3>
                <div class="flex flex-col space-y-2">
                    <p><span class="font-medium">Gender:</span> {{ $employee->person->gender ?? 'N/A' }}</p>
                    <p><span class="font-medium">Contact:</span> {{ $employee->person->contact_number ?? 'N/A' }}</p>
                    <p><span class="font-medium">Email:</span> {{ $employee->person->email ?? 'N/A' }}</p>
                    <p><span class="font-medium">Address:</span> {{ $employee->person->address ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Employment Info -->
            <div>
                <h3 class="mb-2 text-xs font-semibold tracking-wide text-blue-500 uppercase">Employment Information</h3>
                <div class="flex flex-col space-y-2">
                    <p><span class="font-medium">Position:</span> {{ $employee->position ?? 'N/A' }}</p>
                    <p><span class="font-medium">Hire Date:</span> {{ $employee->hire_date?->format('M d, Y') ?? 'N/A' }}</p>
                    <p><span class="font-medium">Daily Salary:</span> ₱{{ number_format($employee->daily_rate, 2) }}</p>
                </div>
            </div>

        </div>



        <!-- Close Button -->
        <div class="flex justify-end pt-4">
            <button 
                x-on:click="$dispatch('close-modal', 'view-employee-{{ $employee->employee_id }}')"
                class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700"
            >
                Close
            </button>
        </div>
    </div>
</x-modal>
@endforeach

<!-- Edit Employee Details Modal -->
@foreach($employees as $employee)
<x-modal name="edit-employee-{{ $employee->employee_id }}" :show="false" maxWidth="lg">
    <div class="p-6 overflow-y-auto max-h-[80vh] table-pretty-scrollbar">

        <!-- Modal Header -->
        <div class="flex justify-between mb-4 space-x-1 text-blue-900">
            <div class="flex items-center">
                <i class="fa-solid fa-user-pen"></i>
                <h2 class="text-xl font-semibold">Edit Employee Details</h2>
            </div>
            <span x-on:click="$dispatch('close-modal', 'edit-employee-{{ $employee->employee_id }}')" class="cursor-pointer">
                <i class="text-lg fa-solid fa-xmark"></i>
            </span>
        </div>  

        <form action="{{ route('employees.update', $employee->employee_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
            @csrf
            @method('PUT')

            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-6">
                <div class="relative">
                    <!-- Employee image preview -->
                    <img id="employeeImagePreview-{{ $employee->employee_id }}" 
                        src="{{ $employee->employee_image_path ? asset('storage/' . $employee->employee_image_path) : 'assets/images/logo/logo-removebg-preview.png' }}" 
                        class="object-cover w-24 h-24 border rounded-full shadow" 
                        alt="{{ $employee->person->firstname }} {{ $employee->person->lastname }}">

                    <!-- Edit image button -->
                    <label for="employee_image_{{ $employee->employee_id }}" 
                        class="absolute bottom-0 right-0 flex items-center justify-center w-8 h-8 text-white bg-blue-600 rounded-full cursor-pointer hover:bg-green-700">
                        <i class="text-xs fa-solid fa-pen"></i>
                    </label>

                    <input type="file" name="employee_image" id="employee_image_{{ $employee->employee_id }}" class="hidden" 
                        onchange="previewEmployeeImage(event, {{ $employee->employee_id }})">
                </div>
                <p class="mt-2 text-sm text-gray-500">Change employee photo</p>
            </div>

            <!-- Personal Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Personal Information</legend>
                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-gray-800">First Name</label>
                        <input type="text" name="firstname" value="{{ $employee->person->firstname }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-800">Last Name</label>
                        <input type="text" name="lastname" value="{{ $employee->person->lastname }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-800">Gender</label>
                        <select name="gender" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ $employee->person->gender=='Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $employee->person->gender=='Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ $employee->person->gender=='Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-800">Contact Number</label>
                        <input type="text" name="contact_number" value="{{ $employee->person->contact_number }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Email</label>
                        <input type="email" name="email" value="{{ $employee->person->email }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-gray-800">Address</label>
                        <input type="text" name="address" value="{{ $employee->person->address }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Job Information -->
            <fieldset class="p-4 border border-gray-200 rounded-lg">
                <legend class="font-semibold text-gray-700">Job Information</legend>
                <div class="mt-2 mb-2 text-sm text-red-600">
                    <i class="mr-1 fa-solid fa-triangle-exclamation"></i>
                    To change the position, delete this employee and add a new one.
                </div>

                <div class="grid grid-cols-1 gap-4 mt-2 sm:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-gray-800">Position</label>
                        <input type="text" value="{{ $employee->position }}" disabled class="w-full px-2 py-1 bg-gray-100 border border-gray-300 rounded cursor-not-allowed"/>
                    </div>

                    <div>
                        <label class="block mb-1 text-gray-800">Daily Salary</label>
                        <input type="number" name="daily_rate" value="{{ $employee->daily_rate }}" class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                    </div>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex justify-end mt-2 space-x-2">
                <button type="button" 
                    x-on:click="$dispatch('close-modal', 'edit-employee-{{ $employee->employee_id }}')"
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
    function previewEmployeeImage(event, employeeId) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById(`employeeImagePreview-${employeeId}`);
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

@foreach($employees as $employee)
<!-- Delete/Fire Employee Modal -->
<x-modal name="delete-employee-{{ $employee->employee_id }}" :show="false" maxWidth="sm">
    <div class="p-6 text-center">

        <!-- Red warning icon -->
        <i class="mb-2 text-4xl text-red-500 mx-a uto fa-solid fa-triangle-exclamation"></i>

        <h2 class="mb-2 text-lg font-semibold text-gray-800">Fire {{ $employee->person->firstname }} {{ $employee->person->lastname }}?</h2>
        <p class="text-sm text-gray-500">
            This action will permanently remove <span class="font-bold text-red-500">{{ $employee->person->firstname }} {{ $employee->person->lastname }}</span> from the system. This cannot be undone.
        </p>

        <form action="{{ route('employees.destroy', $employee->employee_id) }}" method="POST" class="flex justify-center mt-4 space-x-3">
            @csrf
            @method('DELETE')

            <!-- Cancel -->
            <button type="button"
                x-on:click="$dispatch('close-modal', 'delete-employee-{{ $employee->employee_id }}')"
                class="px-4 py-2 text-gray-700 transition bg-gray-200 rounded hover:bg-gray-300">
                Cancel
            </button>

            <!-- Confirm Delete -->
            <button type="submit"
                class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                Yes, Fire
            </button>
        </form>
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