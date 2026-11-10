<?php

use App\Http\Controllers\RoutingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
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
use App\Http\Controllers\Tailoring\TailoringReportController;
use App\Http\Controllers\Hiring\HireItemController;
use App\Http\Controllers\Hiring\HireAgreementController;
use App\Http\Controllers\Hiring\AvailabilityReportController;
use App\Http\Controllers\Hiring\HiringSalesReportController;

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Access control
|--------------------------------------------------------------------------
| Every screen is protected by a permission ("module.action"). The full list
| lives in App\Support\Permissions and is assigned to roles on
| Admin > Roles & Permissions. Super Admin passes every check.
*/

/**
 * Register a resource controller split by permission:
 * index => {module}.view, create/store => {module}.create,
 * edit/update => {module}.edit, destroy => {module}.delete
 */
$permResource = function (string $name, string $controller, string $module, array $parameters = []) {
    $groups = [
        'view'   => ['index'],
        'create' => ['create', 'store'],
        'edit'   => ['edit', 'update'],
        'delete' => ['destroy'],
    ];

    foreach ($groups as $action => $methods) {
        $resource = Route::resource($name, $controller)
            ->only($methods)
            ->middleware("permission:{$module}.{$action}");

        if ($parameters) {
            $resource->parameters($parameters);
        }
    }
};

Route::get('/role-test', function () {
    return auth()->user()?->getRoleNames();
})->middleware(['auth', 'role:super_admin']);

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () use ($permResource) {

    // users (role is assigned here)
    $permResource('users', UserController::class, 'users');

    // roles & permissions
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('permission:roles.manage');

    //profile (everyone)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::middleware('auth')->group(function () use ($permResource) {
    $permResource('customers', CustomerController::class, 'customers');
    $permResource('staff', StaffController::class, 'staff', ['staff' => 'staff']);
    $permResource('dress-types', DressTypeController::class, 'dress_types');
    $permResource('measurement-templates', MeasurementTemplateController::class, 'measurement_templates');
    $permResource('workflow-stages', WorkflowStageController::class, 'workflow_stages');

    // Fields inside a template (part of editing a template)
    Route::middleware('permission:measurement_templates.edit')->group(function () {
        Route::post('measurement-templates/{template}/fields', [MeasurementFieldController::class, 'store'])
            ->name('measurement-templates.fields.store');

        Route::put('measurement-templates/{template}/fields/{field}', [MeasurementFieldController::class, 'update'])
            ->name('measurement-templates.fields.update');

        Route::delete('measurement-templates/{template}/fields/{field}', [MeasurementFieldController::class, 'destroy'])
            ->name('measurement-templates.fields.destroy');
    });
});


Route::middleware(['auth'])->prefix('tailoring')->name('tailoring.')->group(function () {

    // ---- Jobs: create ----
    Route::middleware('permission:jobs.create')->group(function () {
        Route::get('jobs/create-wizard', [JobController::class, 'createWizard'])->name('jobs.createWizard');
        Route::post('jobs/store-wizard', [JobController::class, 'storeWizard'])->name('jobs.storeWizard');
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    });

    // ---- Jobs: edit ----
    Route::middleware('permission:jobs.edit')->group(function () {
        Route::get('jobs/{job}/edit-wizard', [JobController::class, 'editWizard'])->name('jobs.editWizard');
        Route::put('jobs/{job}/update-wizard', [JobController::class, 'updateWizard'])->name('jobs.updateWizard');

        // batch create/delete inside job
        Route::post('jobs/{job}/batches', [JobBatchController::class, 'store'])->name('jobs.batches.store');
        Route::delete('jobs/{job}/batches/{batch}', [JobBatchController::class, 'destroy'])->name('jobs.batches.destroy');

        // items inside a batch
        Route::post('jobs/{job}/batches/{batch}/items', [JobBatchItemController::class, 'store'])->name('jobs.batches.items.store');
        Route::delete('jobs/{job}/batches/{batch}/items/{item}', [JobBatchItemController::class, 'destroy'])->name('jobs.batches.items.destroy');
    });

    // ---- Jobs: view ----
    Route::middleware('permission:jobs.view')->group(function () {
        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
        Route::get('jobs/{job}/bill-data', [JobController::class, 'billData'])->name('jobs.billData');
        Route::get('jobs/{job}/invoice', [JobController::class, 'invoicePdf'])->name('jobs.invoicePdf');
    });

    Route::delete('jobs/{job}', [JobController::class, 'destroy'])
        ->middleware('permission:jobs.delete')
        ->name('jobs.destroy');

    // ---- Measurements ----
    Route::get('jobs/{job}/batches/{batch}/items/{item}/measurements', [MeasurementEntryController::class, 'edit'])
        ->middleware('permission:measurements.view')
        ->name('measurements.edit');

    Route::post('jobs/{job}/batches/{batch}/items/{item}/measurements', [MeasurementEntryController::class, 'store'])
        ->middleware('permission:measurements.edit')
        ->name('measurements.store');

    // ---- Dashboard / reports ----
    Route::get('/production-dashboard', [ProductionDashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('production.dashboard');

    Route::middleware('permission:tailoring_reports.view')->group(function () {
        Route::get('reports/stages', [TailoringReportController::class, 'stages'])->name('reports.stages');
        Route::get('reports/staff', [TailoringReportController::class, 'staff'])->name('reports.staff');
    });

    // ---- Work queue ----
    Route::get('/work-queue', [WorkQueueController::class, 'index'])
        ->middleware('permission:workqueue.view|workqueue.all_stages')
        ->name('workqueue.index');

    // ---- Handover ----
    Route::middleware('permission:handover.view')->group(function () {
        Route::get('/handover', [HandoverController::class, 'index'])->name('handover.index');
        Route::get('/handover/{item}/history', [HandoverController::class, 'history'])->name('handover.history');
    });

    Route::middleware('permission:handover.create')->group(function () {
        Route::get('/handover/{item}/create', [HandoverController::class, 'create'])->name('handover.create');
        Route::post('/handover/{item}', [HandoverController::class, 'store'])->name('handover.store');

        // Group-based handover (user-friendly)
        Route::get('handover/group/{groupId}', [HandoverController::class, 'createGroup'])->name('handover.group.create');
        Route::post('handover/group/{groupId}', [HandoverController::class, 'storeGroup'])->name('handover.group.store');
    });

    Route::post('/handover/{item}/complete', [HandoverController::class, 'complete'])
        ->middleware('permission:handover.complete')
        ->name('handover.complete');

    // ---- Delivery ----
    Route::middleware('permission:delivery.view')->group(function () {
        Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
        Route::get('/delivery/{job}/invoice', [DeliveryController::class, 'invoice'])->name('delivery.invoice');
        Route::get('/delivery/{job}/print', [DeliveryController::class, 'print'])->name('delivery.print');
    });

    Route::middleware('permission:delivery.manage')->group(function () {
        Route::post('/delivery/{job}/prices', [DeliveryController::class, 'updatePrices'])->name('delivery.prices');
        Route::post('/delivery/{job}/deliver', [DeliveryController::class, 'deliver'])->name('delivery.deliver');
    });
});

// small helper api: get template fields (used by job wizards + templates)
Route::get('measurement-templates/{template}/fields', [MeasurementTemplateController::class, 'fieldsJson'])
    ->middleware('auth')
    ->name('measurement-templates.fields.json');

Route::middleware('auth')->prefix('hiring')->group(function () {

    // ---- Hire items ----
    Route::middleware('permission:hire_items.create')->group(function () {
        Route::get('/items/create', [HireItemController::class, 'create'])->name('hiring.items.create');
        Route::post('/items', [HireItemController::class, 'store'])->name('hiring.items.store');
    });

    Route::middleware('permission:hire_items.edit')->group(function () {
        Route::get('/items/{hire_item}/edit', [HireItemController::class, 'edit'])->name('hiring.items.edit');
        Route::put('/items/{hire_item}', [HireItemController::class, 'update'])->name('hiring.items.update');
        Route::delete('/items/image/{image}', [HireItemController::class, 'deleteImage'])->name('hiring.items.images.destroy');
    });

    Route::delete('/items/{hire_item}', [HireItemController::class, 'destroy'])
        ->middleware('permission:hire_items.delete')
        ->name('hiring.items.destroy');

    Route::middleware('permission:hire_items.view')->group(function () {
        Route::get('/items', [HireItemController::class, 'index'])->name('hiring.items.index');
        Route::get('/items/{hire_item}', [HireItemController::class, 'show'])->name('hiring.items.show');
    });

    // ---- Agreements ----
    Route::middleware('permission:hire_agreements.create')->group(function () {
        Route::get('/agreements/create', [HireAgreementController::class, 'create'])->name('hiring.agreements.create');
        Route::post('/agreements', [HireAgreementController::class, 'store'])->name('hiring.agreements.store');
    });

    // AJAX item scan by code (used when creating or editing an agreement)
    Route::post('/agreements/find-item', [HireAgreementController::class, 'findItemByCode'])
        ->middleware('permission:hire_agreements.create|hire_agreements.edit')
        ->name('hiring.agreements.find_item');

    Route::middleware('permission:hire_agreements.edit')->group(function () {
        Route::get('/hiring/agreements/{hire_agreement}/edit', [HireAgreementController::class, 'edit'])->name('hiring.agreements.edit');
        Route::put('/hiring/agreements/{hire_agreement}', [HireAgreementController::class, 'update'])->name('hiring.agreements.update');
    });

    Route::middleware('permission:hire_agreements.return')->group(function () {
        Route::get('/agreements/{hire_agreement}/return', [HireAgreementController::class, 'returnForm'])->name('hiring.agreements.return');
        Route::post('/agreements/{hire_agreement}/return', [HireAgreementController::class, 'returnStore'])->name('hiring.agreements.return.store');
    });

    Route::delete('/agreements/delete/{hire_agreement}', [HireAgreementController::class, 'destroy'])
        ->middleware('permission:hire_agreements.delete')
        ->name('hiring.agreements.destroy');

    Route::middleware('permission:hire_agreements.view')->group(function () {
        Route::get('/agreements', [HireAgreementController::class, 'index'])->name('hiring.agreements.index');
        Route::get('/agreements/{hire_agreement}', [HireAgreementController::class, 'show'])->name('hiring.agreements.show');
        Route::get('hiring/agreements/{hire_agreement}/invoice', [HireAgreementController::class, 'invoice'])->name('hiring.agreements.invoice');
    });

    // ---- Availability Dashboard + Reports ----
    Route::middleware('permission:hiring_availability.view')->group(function () {
        Route::get('/availability', [AvailabilityReportController::class, 'index'])->name('hiring.availability.index');
        Route::get('/availability/overdue', [AvailabilityReportController::class, 'overdue'])->name('hiring.availability.overdue');
        Route::get('/availability/upcoming', [AvailabilityReportController::class, 'upcomingReturns'])->name('hiring.availability.upcoming');
    });

    Route::get('/reports/sales', [HiringSalesReportController::class, 'index'])
        ->middleware('permission:hiring_reports.view')
        ->name('hiring.reports.sales');
});

// Theme demo pages (served straight from the view folders) - super admin only
Route::group(['prefix' => '/', 'middleware' => ['auth', 'role:super_admin']], function () {
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
    // Home: dashboard if allowed, otherwise the first screen this user may open
    Route::get('/', [HomeController::class, 'index'])
        ->name('production.dashboard');
});
