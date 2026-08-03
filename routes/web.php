<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\App\Dashboard;
use App\Livewire\App\Outlets\Index as OutletsIndex;
use App\Livewire\App\Staff\Index as StaffIndex;
use App\Livewire\App\VehicleCategories\Index as VehicleCategoriesIndex;
use App\Livewire\App\Services\Index as ServicesIndex;
use App\Livewire\App\Pricing\Index as PricingIndex;
use App\Livewire\App\Orders\Index as OrdersIndex;
use App\Livewire\App\Orders\Create as OrdersCreate;
use App\Livewire\App\Orders\Workers as OrderWorkers;
use App\Livewire\App\Employees\Index as EmployeesIndex;
use App\Livewire\App\CommissionRules\Index as CommissionRulesIndex;
use App\Livewire\App\Payroll\Index as PayrollIndex;
use App\Livewire\App\AllowanceTypes\Index as AllowanceTypesIndex;
use App\Livewire\App\Expenses\Index as ExpensesIndex;
use App\Livewire\App\ExpenseCategories\Index as ExpenseCategoriesIndex;
use App\Livewire\App\Reports\Index as ReportsIndex;
use App\Livewire\Marketing\Dashboard as MarketingDashboard;

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::middleware(['auth', 'tenant.role'])->prefix('app')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('app.dashboard');
    Route::get('/outlets', OutletsIndex::class)->name('app.outlets');
    Route::get('/orders', OrdersIndex::class)->name('app.orders');
    Route::get('/orders/create', OrdersCreate::class)->name('app.orders.create');
    Route::get('/orders/{order}/workers', OrderWorkers::class)->name('app.orders.workers');
    Route::get('/vehicle-categories', VehicleCategoriesIndex::class)->name('app.vehicle-categories');
    Route::get('/services', ServicesIndex::class)->name('app.services');
    Route::get('/pricing', PricingIndex::class)->name('app.pricing');
    Route::get('/expenses', ExpensesIndex::class)->name('app.expenses');
    Route::get('/expense-categories', ExpenseCategoriesIndex::class)->name('app.expense-categories');

    Route::middleware('owner.role')->group(function () {
        Route::get('/staff', StaffIndex::class)->name('app.staff');
        Route::get('/employees', EmployeesIndex::class)->name('app.employees');
        Route::get('/commission-rules', CommissionRulesIndex::class)->name('app.commission-rules');
        Route::get('/payroll', PayrollIndex::class)->name('app.payroll');
        Route::get('/allowance-types', AllowanceTypesIndex::class)->name('app.allowance-types');
        Route::get('/reports', ReportsIndex::class)->name('app.reports');
    });

});
    
Route::middleware(['auth', 'marketing.role'])->prefix('marketing')->group(function () {
    Route::get('/dashboard', MarketingDashboard::class)->name('marketing.dashboard');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout')->middleware('auth');