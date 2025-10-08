<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerCreditController;
use App\Http\Controllers\AttendanceEmployeeExportController;

Route::post('/register-frontend', [RegisterUserController::class, 'register']);

Route::post('/login-frontend', [AuthenticatedSessionController::class, 'store']);

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login-frontend', function () {
    return view('auth.login');
})->name('login');

Route::get('/register-frontend', function () {
    return view('auth.register');
});

Route::get('/dashboard', function () {
    return view('layouts.app');
})->middleware(['auth'])->name('dashboard'); // i-comment ni if di sa maggamit ug auth
// })->name('dashboard'); // i-comment ni if login is required na

// Suppliers
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
// Update supplier
Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
// Delete a supplier
Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
Route::get('/suppliers/export', [SupplierController::class, 'exportSuppliers'])
    ->name('suppliers.export')
    ->middleware('auth');

// Branches
Route::middleware(['auth'])->group(function() {
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
});
Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
Route::post('/branches/switch/{branch}', [BranchController::class, 'switch'])->name('branches.switch');
Route::get('/branches/export', [BranchController::class, 'export'])
    ->name('branches.export')
    ->middleware('auth');

// Employees
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/employees/{employee}/create-user', [EmployeeController::class, 'createUser'])->name('employees.createUser');
Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
Route::get('/attendance/export', [\App\Http\Controllers\AttendanceController::class, 'exportAttendance'])->name('attendance.export');

// Salary
Route::post('/pay-salary/{employee}', [PayrollController::class, 'paySalary'])->name('pay-salary');

// Products
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::get('/products/export', [ProductController::class, 'exportProducts'])
    ->name('products.export')
    ->middleware('auth');

// Customers
Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::get('/customers/{customer}/credits', [CustomerController::class, 'credits']);
Route::get('/customers/export', [CustomerController::class, 'exportCustomers'])
    ->name('customers.export');
Route::get('/credits/export', [CustomerController::class, 'exportCredits'])
    ->name('credits.export');

// Sales
Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
Route::post('/sale/{sale}/pay-credit', [SalesController::class, 'payCredit'])->name('sale.pay-credit');
Route::post('/sales/{sale}/pay', [SalesController::class, 'pay'])->name('sales.pay');
Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
Route::post('/customers/{customer}/credits/pay-all', [SalesController::class, 'payAll'])->name('sales.payAll');
Route::delete('/customers/{customer}/credits/delete-all', [SalesController::class, 'destroyAll'])->name('sales.destroyAll');
Route::get('/sales/export', [SalesController::class, 'exportSales'])->name('sales.export');

Route::get('/employees/export', [AttendanceEmployeeExportController::class, 'export'])
    ->name('employees.export')
    ->middleware('auth');

Route::get('/forecast/{branch}', [App\Http\Controllers\ForecastController::class, 'show'])
    ->name('forecast.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
