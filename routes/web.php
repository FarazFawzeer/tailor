<?php

use App\Http\Controllers\RoutingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DressTypeController;
use App\Http\Controllers\MeasurementTemplateController;
use App\Http\Controllers\MeasurementFieldController;
use App\Http\Controllers\WorkflowStageController;
use App\Http\Controllers\Tailoring\JobController;
use App\Http\Controllers\Tailoring\JobBatchController;
use App\Http\Controllers\Tailoring\JobBatchItemController;
use App\Http\Controllers\Tailoring\MeasurementEntryController;
use App\Http\Controllers\Tailoring\ProductionDashboardController;
use App\Http\Controllers\Tailoring\HandoverController;
use App\Http\Controllers\Tailoring\DeliveryController;
use App\Http\Controllers\Tailoring\WorkQueueController;
use App\Http\Controllers\Hiring\HireItemController;
use App\Http\Controllers\Hiring\HireAgreementController;
use App\Http\Controllers\Hiring\AvailabilityReportController;

require __DIR__ . '/auth.php';

Route::get('/role-test', function () {
    return auth()->user()?->getRoleNames();
})->middleware(['auth', 'role:super_admin']);

Route::prefix('admin')->name('admin.')->group(function () {

    //admin
    Route::resource('users', UserController::class);


    //profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::middleware(['auth', 'role:super_admin|admin|front_desk'])->group(function () {
    Route::resource('customers', CustomerController::class)->except(['show']);
});


Route::middleware(['auth', 'role:super_admin|admin'])->group(function () {
    Route::resource('staff', StaffController::class)->parameters(['staff' => 'staff'])->except(['show']);

    Route::resource('dress-types', DressTypeController::class)->except(['show']);

    Route::resource('measurement-templates', MeasurementTemplateController::class)->except(['show']);

    // Fields inside a template
    Route::post('measurement-templates/{template}/fields', [MeasurementFieldController::class, 'store'])
        ->name('measurement-templates.fields.store');

    Route::put('measurement-templates/{template}/fields/{field}', [MeasurementFieldController::class, 'update'])
        ->name('measurement-templates.fields.update');

    Route::delete('measurement-templates/{template}/fields/{field}', [MeasurementFieldController::class, 'destroy'])
        ->name('measurement-templates.fields.destroy');

    Route::resource('workflow-stages', WorkflowStageController::class)->except(['show']);
});


Route::middleware(['auth'])->prefix('tailoring')->name('tailoring.')->group(function () {

    Route::get('jobs/create-wizard', [JobController::class, 'createWizard'])->name('jobs.createWizard');
    Route::post('jobs/store-wizard', [JobController::class, 'storeWizard'])->name('jobs.storeWizard');

    // ✅ Wizard edit/update (NEW)
    Route::get('jobs/{job}/edit-wizard', [JobController::class, 'editWizard'])->name('jobs.editWizard');
    Route::put('jobs/{job}/update-wizard', [JobController::class, 'updateWizard'])->name('jobs.updateWizard');

    Route::get('reports/stages', [\App\Http\Controllers\Tailoring\TailoringReportController::class, 'stages'])
        ->name('reports.stages');

    Route::get('reports/staff', [\App\Http\Controllers\Tailoring\TailoringReportController::class, 'staff'])
        ->name('reports.staff');



    // Job screens: super_admin/admin/front_desk can create jobs
    Route::middleware(['role:super_admin|admin|front_desk'])->group(function () {
        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

        // batch create/delete inside job
        Route::post('jobs/{job}/batches', [JobBatchController::class, 'store'])->name('jobs.batches.store');
        Route::delete('jobs/{job}/batches/{batch}', [JobBatchController::class, 'destroy'])->name('jobs.batches.destroy');

        // items inside a batch
        Route::post('jobs/{job}/batches/{batch}/items', [JobBatchItemController::class, 'store'])->name('jobs.batches.items.store');
        Route::delete('jobs/{job}/batches/{batch}/items/{item}', [JobBatchItemController::class, 'destroy'])->name('jobs.batches.items.destroy');

        Route::get('jobs/{job}/batches/{batch}/items/{item}/measurements', [MeasurementEntryController::class, 'edit'])
            ->name('measurements.edit');

        Route::post('jobs/{job}/batches/{batch}/items/{item}/measurements', [MeasurementEntryController::class, 'store'])
            ->name('measurements.store');

        Route::get('/production-dashboard', [ProductionDashboardController::class, 'index'])
            ->name('production.dashboard');
    });

    Route::middleware(['role:super_admin|admin|front_desk|cutter|sewing|button|ironing|packaging'])->group(function () {
        Route::get('/work-queue', [WorkQueueController::class, 'index'])->name('workqueue.index');
    });

    Route::get('/handover', [HandoverController::class, 'index'])
        ->name('handover.index');

    Route::get('/handover/{item}/create', [HandoverController::class, 'create'])
        ->name('handover.create');

    Route::post('/handover/{item}', [HandoverController::class, 'store'])
        ->name('handover.store');

    Route::post('/handover/{item}/complete', [HandoverController::class, 'complete'])
        ->name('handover.complete');

    Route::get('/handover/{item}/history', [HandoverController::class, 'history'])
        ->name('handover.history');

    // Group-based handover (user-friendly)
    Route::get('handover/group/{groupId}', [HandoverController::class, 'createGroup'])
        ->name('handover.group.create');

    Route::post('handover/group/{groupId}', [HandoverController::class, 'storeGroup'])
        ->name('handover.group.store');


    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');

    Route::get('/delivery/{job}/invoice', [DeliveryController::class, 'invoice'])->name('delivery.invoice');
    Route::post('/delivery/{job}/prices', [DeliveryController::class, 'updatePrices'])->name('delivery.prices');

    Route::post('/delivery/{job}/deliver', [DeliveryController::class, 'deliver'])->name('delivery.deliver');

    Route::get('/delivery/{job}/print', [DeliveryController::class, 'print'])->name('delivery.print');
});

Route::delete('/tailoring/jobs/{job}', [JobController::class, 'destroy'])
    ->name('tailoring.jobs.destroy');

Route::get('tailoring/jobs/{job}/invoice', [JobController::class, 'invoicePdf'])
    ->name('tailoring.jobs.invoicePdf');
// small helper api: get template fields
Route::get('measurement-templates/{template}/fields', [MeasurementTemplateController::class, 'fieldsJson'])
    ->name('measurement-templates.fields.json');

Route::middleware(['auth', 'role:super_admin|admin|front_desk'])->prefix('hiring')->group(function () {

    Route::get('/items', [HireItemController::class, 'index'])->name('hiring.items.index');
    Route::get('/items/create', [HireItemController::class, 'create'])->name('hiring.items.create');
    Route::post('/items', [HireItemController::class, 'store'])->name('hiring.items.store');
    Route::get('/items/{hire_item}/edit', [HireItemController::class, 'edit'])->name('hiring.items.edit');
    Route::put('/items/{hire_item}', [HireItemController::class, 'update'])->name('hiring.items.update');
    Route::delete('/items/{hire_item}', [HireItemController::class, 'destroy'])->name('hiring.items.destroy');

    Route::delete('/items/image/{image}', [HireItemController::class, 'deleteImage'])->name('hiring.items.images.destroy');

    Route::get('/items/{hire_item}', [HireItemController::class, 'show'])->name('hiring.items.show');
    // Agreements
    Route::get('/agreements', [HireAgreementController::class, 'index'])->name('hiring.agreements.index');
    Route::get('/agreements/create', [HireAgreementController::class, 'create'])->name('hiring.agreements.create');
    Route::post('/agreements', [HireAgreementController::class, 'store'])->name('hiring.agreements.store');

    Route::get('/agreements/{hire_agreement}', [HireAgreementController::class, 'show'])->name('hiring.agreements.show');

    Route::get('/agreements/{hire_agreement}/return', [HireAgreementController::class, 'returnForm'])->name('hiring.agreements.return');
    Route::post('/agreements/{hire_agreement}/return', [HireAgreementController::class, 'returnStore'])->name('hiring.agreements.return.store');

    // AJAX item scan by code
    Route::post('/agreements/find-item', [HireAgreementController::class, 'findItemByCode'])->name('hiring.agreements.find_item');


    Route::get('/hiring/agreements/{hire_agreement}/edit', [HireAgreementController::class, 'edit'])->name('hiring.agreements.edit');
    Route::put('/hiring/agreements/{hire_agreement}', [HireAgreementController::class, 'update'])->name('hiring.agreements.update');

    Route::delete('/agreements/delete/{hire_agreement}', [HireAgreementController::class, 'destroy'])
        ->name('hiring.agreements.destroy');

        Route::get('hiring/agreements/{hire_agreement}/invoice', [\App\Http\Controllers\Hiring\HireAgreementController::class, 'invoice'])
    ->name('hiring.agreements.invoice');

    // Availability Dashboard + Reports
    Route::get('/availability', [AvailabilityReportController::class, 'index'])->name('hiring.availability.index');

    // Optional: separate pages (if you want)
    Route::get('/availability/overdue', [AvailabilityReportController::class, 'overdue'])->name('hiring.availability.overdue');

    Route::get('/availability/upcoming', [AvailabilityReportController::class, 'upcomingReturns'])->name('hiring.availability.upcoming');

    Route::get('/reports/sales', [\App\Http\Controllers\Hiring\HiringSalesReportController::class, 'index'])
    ->name('hiring.reports.sales');
});

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});


Route::get('/login', function () {
    return view('auth.signin');
})->name('login');

// Login action
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    // Route::get('/', function () {
    //     return view('index'); // create resources/views/dashboard.blade.php
    // });
    Route::get('/', [ProductionDashboardController::class, 'index'])
        ->name('production.dashboard');
});
