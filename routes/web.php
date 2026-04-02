<?php

use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\Auth\SupplierAuthController;
use App\Http\Controllers\ceo\CeoDashboardController;
use App\Http\Controllers\client\DashboardController as ClientDashboardController;
use App\Http\Controllers\client\InvoicesController;
use App\Http\Controllers\client\OrdersController;
use App\Http\Controllers\client\ProductController as ClientProductController;
use App\Http\Controllers\client\ProfileController as ClientProfileController;
use App\Http\Controllers\client\QuotationController;
use App\Http\Controllers\client\SupportController;
use App\Http\Controllers\crm\AccessController as CrmAccessController;
use App\Http\Controllers\crm\ApprovalController;
use App\Http\Controllers\crm\CrmDashboardController;
use App\Http\Controllers\crm\CustomerProfileController;
use App\Http\Controllers\crm\InterviewController as CrmInterviewController;
use App\Http\Controllers\crm\InvestigationController;
use App\Http\Controllers\crm\LeadController;
use App\Http\Controllers\crm\TraineeController as CrmTraineeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\eco\EcoDashboardController;
use App\Http\Controllers\eco\manager\BookController;
use App\Http\Controllers\eco\manager\ClientVerificationController;
use App\Http\Controllers\eco\manager\CreditController;
use App\Http\Controllers\eco\manager\OrderManagementController;
use App\Http\Controllers\eco\manager\QuotationController as EcoQuotationController;
use App\Http\Controllers\eco\manager\StoreController;
use App\Http\Controllers\eco\staff\CustomerController;
use App\Http\Controllers\eco\staff\OrdermngController;
use App\Http\Controllers\eco\staff\ProductsController;
use App\Http\Controllers\fin\FinDashboardController;
use App\Http\Controllers\hrm\AccessController;
use App\Http\Controllers\hrm\AnalyticsController;
use App\Http\Controllers\hrm\ApplicantController as HrmApplicantController;
use App\Http\Controllers\hrm\EmployeeController;
use App\Http\Controllers\hrm\HrmDashboardController;
use App\Http\Controllers\hrm\InterviewController;
use App\Http\Controllers\hrm\OnboardingController;
use App\Http\Controllers\hrm\PayrollController;
use App\Http\Controllers\hrm\TraineeController;
use App\Http\Controllers\inv\InvDashboardController;
use App\Http\Controllers\inv\InventoryController as InvInventoryController;
use App\Http\Controllers\inv\manager\ProductionPlanningController;
use App\Http\Controllers\inv\MaterialController;
use App\Http\Controllers\inv\ProductController as InvProductController;
use App\Http\Controllers\it\ItDashboardController;
use App\Http\Controllers\man\Manager\ManufacturingManagerController;
use App\Http\Controllers\man\ManDashboardController;
use App\Http\Controllers\man\Staff\CheckerQualityController;
use App\Http\Controllers\man\Staff\DyeingColorController;
use App\Http\Controllers\man\Staff\DyeingFabricSoftenerController;
use App\Http\Controllers\man\Staff\DyeingFormingController;
use App\Http\Controllers\man\Staff\DyeingIroningController;
use App\Http\Controllers\man\Staff\DyeingPackagingController;
use App\Http\Controllers\man\Staff\DyeingSqueezerController;
use App\Http\Controllers\man\Staff\KnittingYarnController;
use App\Http\Controllers\man\Staff\MaintenanceCheckerController;
use App\Http\Controllers\ord\OrdDashboardController;
use App\Http\Controllers\pro\manager\ProcurementController;
use App\Http\Controllers\pro\ProDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\proj\ProjDashboardController;
use App\Http\Controllers\scm\employee\InboundController;
use App\Http\Controllers\scm\employee\InventoryController as ScmInventoryController;
use App\Http\Controllers\scm\employee\RecievingController;
use App\Http\Controllers\scm\employee\VerificationController;
use App\Http\Controllers\scm\manager\CloseController;
use App\Http\Controllers\scm\manager\PaymentController;
use App\Http\Controllers\scm\manager\SalesOrderController;
use App\Http\Controllers\scm\manager\ScmManagerController;
use App\Http\Controllers\scm\manager\VendorController;
use App\Http\Controllers\scm\ScmDashboardController;
use App\Http\Controllers\SUPPLIERS\SupplierDashboardController;
use App\Http\Controllers\trainee\TraineeAttendanceController;
use App\Http\Controllers\trainee\TraineePayslipController;
use App\Http\Controllers\trainee\TraineeTimeKeepingController;
use App\Http\Controllers\users\AppController;
use App\Http\Controllers\users\ClockController;
use App\Http\Controllers\users\leaveController as UserLeaveController;
use App\Http\Controllers\war\WarDashboardController;
// Workforce Management Controllers
use App\Http\Controllers\workforce\AbsentController;
use App\Http\Controllers\workforce\AccessController as WorkforceAccessController;
use App\Http\Controllers\workforce\LeaveController as WorkforceLeaveController;
use App\Http\Controllers\workforce\SchedulerController;
use App\Http\Controllers\workforce\WorkforceDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core Application Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return inertia('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => app()->version(),
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Career Application Routes
|--------------------------------------------------------------------------
*/
Route::get('/apply', function () {
    return inertia('Auth/apply');
})->name('apply');

// FIXED: Corrected Alias to HrmApplicantController
Route::post('/apply/store', [HrmApplicantController::class, 'store'])->name('applicants.public.store');

/*
|--------------------------------------------------------------------------
| Authenticated User Core Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Unified Employee UI Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'position:staff,manager'])->prefix('dashboard/employee-ui')->group(function () {
    Route::get('/', [AppController::class, 'index'])->name('employee.ui.dashboard');
    Route::get('/clock', [ClockController::class, 'clock'])->name('employee.ui.clock');
    Route::post('/clock/toggle', [ClockController::class, 'toggle'])->name('employee.attendance.toggle');
    Route::get('/leave', [UserLeaveController::class, 'leave'])->name('employee.ui.leave');
    Route::post('/leave', [UserLeaveController::class, 'store'])->name('employee.leave.store');
    Route::get('/payslip', function () {
        return inertia('Dashboard/USERS/payslip');
    })->name('employee.ui.payslip');
});

/*
|--------------------------------------------------------------------------
| Unified Trainee Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/trainee')->middleware(['auth', 'verified', 'position:trainee'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('trainee.dashboard');
    Route::get('/timekeeping', [TraineeTimeKeepingController::class, 'index'])->name('trainee.timekeeping');
    Route::post('/timekeeping/clock', [TraineeTimeKeepingController::class, 'clockInOut'])->name('trainee.timekeeping.clock');
    Route::get('/attendance', [TraineeAttendanceController::class, 'index'])->name('trainee.attendance');
    Route::get('/payslip', [TraineePayslipController::class, 'index'])->name('trainee.payslip');
    Route::get('/payslip/{payroll}', [TraineePayslipController::class, 'show'])->name('trainee.payslip.show');
});

/*
|--------------------------------------------------------------------------
| Human Resources Management (HRM) Routes – Restructured
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/hrm')->name('hrm.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HrmDashboardController::class, 'index'])->name('dashboard');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::patch('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    Route::get('/applications', [HrmApplicantController::class, 'index'])->name('applications.index');
    Route::post('/applications', [HrmApplicantController::class, 'store'])->name('applications.store');
    Route::post('/applications/{id}/accept', [HrmApplicantController::class, 'accept'])->name('applications.accept');
    Route::post('/applications/{id}/reject', [HrmApplicantController::class, 'reject'])->name('applications.reject');
    Route::get('/rejected', [HrmApplicantController::class, 'rejected'])->name('applications.rejected');

    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::post('/interview/{id}/schedule', [InterviewController::class, 'schedule'])->name('interview.schedule');
    Route::post('/interview/{id}/pass', [InterviewController::class, 'pass'])->name('interview.pass');
    Route::post('/interview/{id}/fail', [InterviewController::class, 'fail'])->name('interview.fail');
    Route::post('/interview/{id}/pass-to-other', [InterviewController::class, 'passToOtherModule'])->name('interview.pass-to-other');

    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::post('/trainee/{id}/grade', [TraineeController::class, 'grade'])->name('trainee.grade');
    Route::post('/trainee/{id}/pass', [TraineeController::class, 'pass'])->name('trainee.pass');
    Route::post('/trainee/{id}/fail', [TraineeController::class, 'fail'])->name('trainee.fail');

    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/{id}/convert', [OnboardingController::class, 'convert'])->name('onboarding.convert');

    Route::get('/access', [AccessController::class, 'index'])->name('access.index');
    Route::post('/access/update', [AccessController::class, 'update'])->name('access.update');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
});

/*
|--------------------------------------------------------------------------
| Workforce Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/workforce')->name('workforce.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [WorkforceDashboardController::class, 'index'])->name('dashboard');
    Route::get('/scheduler', [SchedulerController::class, 'index'])->name('scheduler');
    Route::post('/scheduler/shift', [SchedulerController::class, 'storeShift'])->name('scheduler.shift.store');
    Route::delete('/scheduler/shift/{id}', [SchedulerController::class, 'deleteShift'])->name('scheduler.shift.destroy');
    Route::post('/scheduler/holiday', [SchedulerController::class, 'storeHoliday'])->name('scheduler.holiday.store');
    Route::delete('/scheduler/holiday/{id}', [SchedulerController::class, 'deleteHoliday'])->name('scheduler.holiday.destroy');
    Route::post('/scheduler/shift/bulk', [SchedulerController::class, 'storeBulkShift'])->name('scheduler.shift.bulk');
    Route::patch('/scheduler/holiday/{id}', [SchedulerController::class, 'updateHoliday'])->name('scheduler.holiday.update');
    // CEO Planner routes
    Route::post('/scheduler/planner', [SchedulerController::class, 'storePlannerEvent'])->name('scheduler.planner.store');
    Route::patch('/scheduler/planner/{id}', [SchedulerController::class, 'updatePlannerEvent'])->name('scheduler.planner.update');
    Route::delete('/scheduler/planner/{id}', [SchedulerController::class, 'deletePlannerEvent'])->name('scheduler.planner.destroy');

    Route::get('/leave', [WorkforceLeaveController::class, 'index'])->name('leave');
    Route::post('/leave/{id}/approve', [WorkforceLeaveController::class, 'approve'])->name('leave.approve');
    Route::post('/leave/{id}/reject', [WorkforceLeaveController::class, 'reject'])->name('leave.reject');

    Route::get('/absent', [AbsentController::class, 'index'])->name('absent');
    Route::post('/absent/{id}/suspend', [AbsentController::class, 'suspend'])->name('absent.suspend');

    Route::get('/access', [WorkforceAccessController::class, 'index'])->name('access');
    Route::post('/access/update', [WorkforceAccessController::class, 'update'])->name('access.update');
});

/*
|--------------------------------------------------------------------------
| Supply Chain Management (SCM) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/scm')->name('scm.')->middleware(['auth', 'verified'])->group(function () {
    // Shared RBAC Routes for CEO Oversight
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');
    Route::post('/access/update', [AccessController::class, 'update'])->name('access.update');

    Route::middleware(['role:SCM', 'position:manager'])->group(function () {
        Route::get('/manager', [ScmDashboardController::class, 'managerDashboard'])->name('manager.dashboard');
        Route::get('/operations', [ScmDashboardController::class, 'operations'])->name('manager.operations');
        Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('manager.sales-orders');
        Route::post('/sales-orders/{order}/forward-to-inv', [SalesOrderController::class, 'forwardToINV'])->name('manager.sales-orders.forward');
        Route::post('/material-requests/{id}/forward', [ScmManagerController::class, 'forwardMaterialRequest'])->name('manager.material-request.forward');
        Route::get('/assignment', [ScmManagerController::class, 'assignment'])->name('manager.assignment');
        Route::post('/staff/{id}/update-role', [ScmManagerController::class, 'updateRole'])->name('manager.update-staff-role');
        Route::post('/orders/{order}/approve-manufacturing', [ScmManagerController::class, 'approveManufacturing'])->name('manager.approve-manufacturing');
        Route::post('/orders/{order}/recheck', [ScmManagerController::class, 'recheckOrder'])->name('manager.order.recheck');
        Route::get('/manager/payments', [PaymentController::class, 'index'])->name('manager.payments');
        Route::post('/payments', [PaymentController::class, 'processPayment'])->name('manager.payments.process');

        Route::prefix('vendor')->group(function () {
            Route::get('/', [VendorController::class, 'vendor'])->name('manager.vendor');
            Route::post('/register', [VendorController::class, 'register'])->name('manager.vendor.register');
            Route::get('/registrations', [VendorController::class, 'getRegistrations'])->name('manager.vendor.registrations');
            Route::get('/registrations/{id}', [VendorController::class, 'getRegistration'])->name('manager.vendor.registration.show');
            Route::post('/registrations/{id}/approve', [VendorController::class, 'approve'])->name('manager.vendor.approve');
            Route::post('/registrations/{id}/reject', [VendorController::class, 'reject'])->name('manager.vendor.reject');
            Route::post('/registrations/{id}/requirements', [VendorController::class, 'setRequirements'])->name('manager.vendor.requirements.store');
            Route::get('/registrations/{id}/requirements', [VendorController::class, 'getRequirements'])->name('manager.vendor.requirements.show');
            Route::get('/my-registration', [VendorController::class, 'getMyRegistration'])->name('manager.vendor.my-registration');
        });

        Route::get('/close', [CloseController::class, 'close'])->name('manager.close');
    });

    Route::middleware(['role:SCM', 'position:staff'])->group(function () {
        Route::get('/staff', [ScmDashboardController::class, 'staffDashboard'])->name('employee.dashboard');
        Route::get('/inbound', [InboundController::class, 'inbound'])->name('employee.inbound');
        Route::get('/inventory', [ScmInventoryController::class, 'inventory'])->name('employee.inventory');
        Route::get('/recieving', [RecievingController::class, 'recieving'])->name('employee.recieving');
        Route::get('/verification', [VerificationController::class, 'verification'])->name('employee.verification');
    });
});

/*
|--------------------------------------------------------------------------
| Financial Operations (FIN) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/fin')->name('fin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');
    Route::post('/access/update', [AccessController::class, 'update'])->name('access.update');

    Route::get('/manager', [FinDashboardController::class, 'managerDashboard'])
        ->middleware(['role:FIN', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [FinDashboardController::class, 'staffDashboard'])
        ->middleware(['role:FIN', 'position:staff'])
        ->name('employee.dashboard');
});

/*
|--------------------------------------------------------------------------
| Manufacturing Plant (MAN) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/man')->name('man.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/staff', [ManDashboardController::class, 'staffDashboard'])
        ->middleware(['role:MAN', 'position:staff'])
        ->name('employee.dashboard');

    Route::middleware(['role:MAN', 'position:manager'])->group(function () {
        Route::get('/', [ManufacturingManagerController::class, 'index'])->name('manager.dashboard');
        Route::get('/production', [ManufacturingManagerController::class, 'production'])->name('manager.production');
        Route::get('/rejected', [ManufacturingManagerController::class, 'rejected'])->name('manager.rejected');
        Route::post('/orders/{id}/forward-to-checker', [ManufacturingManagerController::class, 'forwardToChecker'])->name('manager.forward-to-checker');
        Route::post('/staff/{id}/update-role', [ManufacturingManagerController::class, 'updateStaffRole'])->name('manager.update-staff-role');
        Route::post('/packages/{id}/send-to-logistics', [ManufacturingManagerController::class, 'sendToLogistics'])->name('manager.send-to-logistics');
    });

    Route::middleware(['role:MAN', 'position:staff'])->group(function () {
        Route::prefix('knitting-yarn')->name('staff.knitting-yarn.')->controller(KnittingYarnController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/knitting-yarn', 'knittingYarn')->name('page');
            Route::post('/fabric', 'storeFabric')->name('store-fabric');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-color')->name('staff.dyeing-color.')->controller(DyeingColorController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/dyeing-color', 'dyeingColor')->name('page');
            Route::post('/dye', 'storeDye')->name('store-dye');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-fabric-softener')->name('staff.dyeing-fabric-softener.')->controller(DyeingFabricSoftenerController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/dyeing-fabric-softener', 'dyeingFabricSoftener')->name('page');
            Route::post('/soften', 'storeSoftener')->name('store-soften');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-squeezer')->name('staff.dyeing-squeezer.')->controller(DyeingSqueezerController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/dyeing-squeezer', 'dyeingSqueezer')->name('page');
            Route::post('/squeeze', 'storeSqueezer')->name('store-squeeze');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-ironing')->name('staff.dyeing-ironing.')->controller(DyeingIroningController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/dyeing-ironing', 'dyeingIroning')->name('page');
            Route::post('/iron', 'storeIron')->name('store-iron');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-forming')->name('staff.dyeing-forming.')->controller(DyeingFormingController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/dyeing-forming', 'dyeingForming')->name('page');
            Route::post('/form', 'storeForm')->name('store-form');
            Route::get('/reports', 'reports')->name('reports');
            Route::post('/machine-report', 'reportMachine')->name('report-machine');
        });

        Route::prefix('dyeing-packaging')->name('staff.dyeing-packaging.')->controller(DyeingPackagingController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/packaging', 'packaging')->name('page');
            Route::post('/package', 'storePackage')->name('store-package');
        });

        Route::prefix('maintenance-checker')->name('staff.maintenance-checker.')->controller(MaintenanceCheckerController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/maintenance', 'maintenance')->name('page');
            Route::post('/machine', 'storeMachine')->name('store-machine');
            Route::patch('/machine/{id}', 'updateMachineStatus')->name('update-machine');
            Route::get('/reports', 'reports')->name('reports');
            Route::patch('/report/{id}', 'resolveReport')->name('resolve-report');
        });

        Route::prefix('checker-quality')->name('staff.checker-quality.')->controller(CheckerQualityController::class)->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::get('/production', 'production')->name('production');
            Route::post('/order/{id}/check-inventory', 'checkInventory')->name('check-inventory');
            Route::post('/order/{id}/start-production', 'startProduction')->name('start-production');
            Route::post('/fabric/{id}/pass', 'passFabric')->name('pass-fabric');
            Route::post('/dye/{id}/pass', 'passDye')->name('pass-dye');
            Route::post('/softener/{id}/pass', 'passSoftener')->name('pass-softener');
            Route::post('/squeezer/{id}/pass', 'passSqueezer')->name('pass-squeezer');
            Route::post('/iron/{id}/pass', 'passIron')->name('pass-iron');
            Route::post('/form/{id}/pack', 'packForm')->name('pack-form');
            Route::post('/form/{id}/reject', 'rejectForm')->name('reject-form');
            Route::post('/package/{id}/assign-to-order', 'assignPackageToOrder')->name('assign-package');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Inventory & Warehousing (INV) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/inv')->name('inv.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/manager', [InvDashboardController::class, 'managerDashboard'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [InvDashboardController::class, 'staffDashboard'])
        ->middleware(['role:INV', 'position:staff'])
        ->name('employee.dashboard');

    Route::get('/production-planning', [ProductionPlanningController::class, 'index'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.production-planning');
    Route::post('/production-planning/{order}/check', [ProductionPlanningController::class, 'checkAvailability'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.production-planning.check');

    Route::get('/inventory', [InvInventoryController::class, 'inventory'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.inventory');
    Route::post('/inventory/receive', [InvInventoryController::class, 'receiveDelivery'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.inventory.receive');
    Route::post('/warehouse', [InvInventoryController::class, 'storeWarehouse'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.warehouse.store');
    Route::post('/inventory/item', [InvInventoryController::class, 'storeItem'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.inventory.item.store');
    Route::delete('/inventory/item/{wmId}', [InvInventoryController::class, 'destroyItem'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.inventory.item.destroy');

    Route::post('/material/procurement', [MaterialController::class, 'requestProcurement'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.material.procurement');
    Route::get('/material', [MaterialController::class, 'material'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.material');
    Route::post('/material', [MaterialController::class, 'store'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.material.store');
    Route::delete('/material/{id}', [MaterialController::class, 'destroy'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.material.destroy');
    Route::post('/material/delegate', [MaterialController::class, 'delegate'])
        ->middleware(['role:INV', 'position:manager'])
        ->name('manager.material.delegate');

    Route::get('/product', [InvProductController::class, 'product'])->name('manager.product');
    Route::post('/product', [InvProductController::class, 'store'])->name('manager.product.store');
    Route::post('/product/{id}/update', [InvProductController::class, 'update'])->name('manager.product.update');
    Route::delete('/product/image/{imageId}', [InvProductController::class, 'destroyImage'])->name('manager.product.image.destroy');
    Route::delete('/product/{id}', [InvProductController::class, 'destroy'])->name('manager.product.destroy');
});

/*
|--------------------------------------------------------------------------
| Order Processing (ORD) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/ord')->name('ord.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/manager', [OrdDashboardController::class, 'managerDashboard'])
        ->middleware(['role:ORD', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [OrdDashboardController::class, 'staffDashboard'])
        ->middleware(['role:ORD', 'position:staff'])
        ->name('employee.dashboard');
});

/*
|--------------------------------------------------------------------------
| Warehouse Dispatch (WAR) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/war')->name('war.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/manager', [WarDashboardController::class, 'managerDashboard'])
        ->middleware(['role:WAR', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [WarDashboardController::class, 'staffDashboard'])
        ->middleware(['role:WAR', 'position:staff'])
        ->name('employee.dashboard');
});

/*
|--------------------------------------------------------------------------
| Customer Relationship Management (CRM) Routes – Restructured
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/crm')->name('crm.')->middleware(['auth', 'verified'])->group(function () {
    // Dashboard (unified, no manager/staff split)
    Route::get('/', [CrmDashboardController::class, 'index'])->name('dashboard');

    // Lead Pipeline
    Route::get('/lead', [LeadController::class, 'index'])->name('lead');
    Route::post('/lead/store', [LeadController::class, 'store'])->name('lead.store');
    Route::patch('/lead/{id}/status', [LeadController::class, 'updateStatus'])->name('lead.status');
    Route::post('/lead/convert', [LeadController::class, 'convertToClient'])->name('lead.convert');
    Route::post('/lead/{id}/note', [LeadController::class, 'addNote'])->name('lead.add-note');
    Route::post('/lead/{id}/interview', [LeadController::class, 'scheduleInterview'])->name('lead.schedule-interview');
    Route::post('/lead/{id}/file', [LeadController::class, 'uploadApprovalFile'])->name('lead.upload-file');
    Route::post('/lead/{id}/accept', [LeadController::class, 'acceptLead'])->name('lead.accept');
    Route::post('/lead/{id}/reject', [LeadController::class, 'rejectLead'])->name('lead.reject');

    // Interview (applicants assigned to CRM)
    Route::get('/interview', [CrmInterviewController::class, 'index'])->name('interview.index');
    Route::post('/interview/{id}/schedule', [CrmInterviewController::class, 'schedule'])->name('interview.schedule');
    Route::post('/interview/{id}/pass', [CrmInterviewController::class, 'pass'])->name('interview.pass');
    Route::post('/interview/{id}/fail', [CrmInterviewController::class, 'fail'])->name('interview.fail');
    Route::post('/interview/{id}/pass-to-other', [CrmInterviewController::class, 'passToOtherModule'])->name('interview.pass-to-other');

    // Trainee (CRM trainees)
    Route::get('/trainee', [CrmTraineeController::class, 'index'])->name('trainee.index');
    Route::post('/trainee/{id}/grade', [CrmTraineeController::class, 'grade'])->name('trainee.grade');
    Route::post('/trainee/{id}/pass', [CrmTraineeController::class, 'pass'])->name('trainee.pass');
    Route::post('/trainee/{id}/fail', [CrmTraineeController::class, 'fail'])->name('trainee.fail');

    // Approval (pending client registrations)
    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approval/{id}/note', [ApprovalController::class, 'addNote'])->name('approval.note');
    Route::post('/approval/{id}/meeting', [ApprovalController::class, 'scheduleMeeting'])->name('approval.meeting');
    Route::patch('/approval/meeting/{meetingId}/status', [ApprovalController::class, 'updateMeetingStatus'])->name('approval.meeting.update');
    Route::post('/approval/{id}/accept', [ApprovalController::class, 'accept'])->name('approval.accept');
    Route::post('/approval/{id}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');

    // Customer Profiles — index lists all clients, show displays one
    Route::get('/customerprofile', [CustomerProfileController::class, 'index'])->name('customerprofile.index');
    Route::get('/customerprofile/{id}', [CustomerProfileController::class, 'show'])->name('customerprofile.show');

    // Investigation (feedback/complaints)
    Route::get('/investigation', [InvestigationController::class, 'index'])->name('investigation.index');
    Route::post('/investigation/assign', [InvestigationController::class, 'assignStaff'])->name('investigation.assign');
    Route::post('/investigation/feedback', [InvestigationController::class, 'storeFeedback'])->name('investigation.feedback.store');
    Route::patch('/investigation/feedback/{id}/status', [InvestigationController::class, 'updateFeedbackStatus'])->name('investigation.feedback.status');

    // Access Control (CRM permissions)
    Route::get('/access', [CrmAccessController::class, 'index'])->name('access.index');
    Route::post('/access/update', [CrmAccessController::class, 'update'])->name('access.update');
});

/*
|--------------------------------------------------------------------------
| E-Commerce & Account Management (ECO) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/eco')->name('eco.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::middleware(['role:ECO', 'position:manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/', [EcoDashboardController::class, 'index'])->name('dashboard');
        Route::get('/store', [StoreController::class, 'index'])->name('store');
        Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders');
        Route::post('/orders/{order}/approve', [OrderManagementController::class, 'approveOrder'])->name('order.approve');
        Route::post('/orders/{order}/reject', [OrderManagementController::class, 'rejectOrder'])->name('order.reject');
        Route::post('/orders/{order}/send-to-scm', [OrderManagementController::class, 'sendToSCM'])->name('order.send-to-scm');
        Route::get('/book', [BookController::class, 'book'])->name('book');
        Route::post('/book/store', [BookController::class, 'store'])->name('book.store');
        Route::patch('/book/{tier}/update', [BookController::class, 'update'])->name('book.update');
        Route::post('/book/apply-tier/{po}', [BookController::class, 'applyTier'])->name('book.apply-tier');
        Route::get('/credit', [CreditController::class, 'credit'])->name('credit');
        Route::post('/credit', [CreditController::class, 'store'])->name('credit.store');
        Route::post('/credit/verify/{po}', [CreditController::class, 'verifyOrder'])->name('credit.verify');
        Route::patch('/credit/{account}/toggle', [CreditController::class, 'toggleStatus'])->name('credit.toggle-status');
        Route::delete('/credit/{account}', [CreditController::class, 'destroy'])->name('credit.destroy');
        Route::get('/credit/client/{client}/history', [CreditController::class, 'clientHistory'])->name('credit.client-history');
        Route::get('/quotations', [EcoQuotationController::class, 'index'])->name('quotations');
        Route::get('/quotations/{id}', [EcoQuotationController::class, 'show'])->name('quotations.show');
        Route::post('/quotations/{id}/respond', [EcoQuotationController::class, 'respond'])->name('quotations.respond');
        Route::get('/verification', [ClientVerificationController::class, 'index'])->name('verification.index');
        Route::patch('/clients/{client}/status', [ClientVerificationController::class, 'updateStatus'])->name('clients.status.update');
    });

    Route::middleware(['role:ECO', 'position:staff'])->group(function () {
        Route::get('/staff', [DashboardController::class, 'index'])->name('employee.dashboard');
        Route::get('/products', [ProductsController::class, 'products'])->name('employee.products');
        Route::post('/products', [ProductsController::class, 'store'])->name('employee.products.store');
        Route::post('/products/{product}/update', [ProductsController::class, 'update'])->name('employee.products.update');
        Route::patch('/products/{product}/toggle', [ProductsController::class, 'toggleStatus'])->name('employee.products.toggle');
        Route::get('/ordermng', [OrdermngController::class, 'ordermng'])->name('employee.ordermng');
        Route::get('/customer', [CustomerController::class, 'customer'])->name('employee.customer');
    });
});

/*
|--------------------------------------------------------------------------
| Procurement Sub-System (PRO) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/pro')->name('pro.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::middleware(['role:PRO', 'position:manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/', [ProDashboardController::class, 'managerDashboard'])->name('dashboard');
        Route::get('/material-requests', [ProcurementController::class, 'materialRequests'])->name('material-requests');
        Route::post('/material-requests/rfq', [ProcurementController::class, 'createRFQ'])->name('rfq.store');
        Route::get('/supplier-quotations', [ProcurementController::class, 'supplierQuotations'])->name('supplier-quotations');
        Route::post('/quotations/{responseId}/accept', [ProcurementController::class, 'acceptQuotation'])->name('quotations.accept');
        Route::post('/quotations/{responseId}/decline', [ProcurementController::class, 'declineQuotation'])->name('quotations.decline');
        Route::get('/receipt', [ProcurementController::class, 'receipt'])->name('receipt');
        Route::post('/purchase-orders/{poId}/send', [ProcurementController::class, 'sendPurchaseOrder'])->name('purchase-orders.send');
    });

    Route::middleware(['role:PRO', 'position:staff'])->group(function () {
        Route::get('/staff', [ProDashboardController::class, 'staffDashboard'])->name('employee.dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Project Automation (PROJ) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/proj')->name('proj.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/manager', [ProjDashboardController::class, 'managerDashboard'])
        ->middleware(['role:PROJ', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [ProjDashboardController::class, 'staffDashboard'])
        ->middleware(['role:PROJ', 'position:staff'])
        ->name('employee.dashboard');
});

/*
|--------------------------------------------------------------------------
| IT & Systems Admin (IT) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/it')->name('it.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/interview', [InterviewController::class, 'index'])->name('interview.index');
    Route::get('/trainee', [TraineeController::class, 'index'])->name('trainee.index');
    Route::get('/access', [AccessController::class, 'index'])->name('access.index');

    Route::get('/manager', [ItDashboardController::class, 'managerDashboard'])
        ->middleware(['role:IT', 'position:manager'])
        ->name('manager.dashboard');

    Route::get('/staff', [ItDashboardController::class, 'staffDashboard'])
        ->middleware(['role:IT', 'position:staff'])
        ->name('employee.dashboard');
});

/*
|--------------------------------------------------------------------------
| CEO Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/ceo')->name('ceo.')->middleware(['auth', 'verified', 'role:CEO'])->group(function () {
    Route::get('/', [CeoDashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| B2B Client Gateway Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest:client')->group(function () {
    Route::get('client/register', [ClientAuthController::class, 'create'])->name('client.register');
    Route::post('client/register', [ClientAuthController::class, 'store'])->name('client.register.store');
    Route::get('client/login', [ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('client/login', [ClientAuthController::class, 'login'])->name('client.login.store');
});

Route::post('client/logout', [ClientAuthController::class, 'logout'])
    ->middleware('auth:client')
    ->name('client.logout');

/*
|--------------------------------------------------------------------------
| Protected Client B2B Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:client')->prefix('partner')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [ClientProductController::class, 'index'])->name('products');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::get('/profile', [ClientProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ClientProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [OrdersController::class, 'orders'])->name('orders');
    Route::post('/orders/{order}/accept', [OrdersController::class, 'acceptPurchaseOrder'])->name('orders.accept');
    Route::post('/purchase-order', [ClientDashboardController::class, 'placeOrder'])->name('purchase-order.store');
    Route::post('/quotation/{po}/accept', [ClientDashboardController::class, 'acceptQuotation'])->name('quotation.accept');
    Route::get('/invoices', [InvoicesController::class, 'invoices'])->name('invoices');
    Route::get('/support', [SupportController::class, 'support'])->name('support');
});

/*
|--------------------------------------------------------------------------
| Vendor/Supplier Gateway Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest:supplier')->group(function () {
    Route::get('supplier/login', [SupplierAuthController::class, 'showLogin'])->name('supplier.login');
    Route::post('supplier/login', [SupplierAuthController::class, 'login'])->name('supplier.login.store');
    Route::get('supplier/register', [SupplierAuthController::class, 'create'])->name('supplier.register');
    Route::post('supplier/register', [SupplierAuthController::class, 'store'])->name('supplier.register.store');
});

Route::post('supplier/logout', [SupplierAuthController::class, 'logout'])
    ->middleware('auth:supplier')
    ->name('supplier.logout');

/*
|--------------------------------------------------------------------------
| Protected Supplier Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:supplier')->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierDashboardController::class, 'index'])->name('dashboard');
    Route::post('/rfq/{id}/respond', [SupplierDashboardController::class, 'submitQuotation'])->name('rfq.respond');
    Route::get('/orders', [SupplierDashboardController::class, 'purchaseOrders'])->name('orders');
    Route::post('/orders/{id}/status', [SupplierDashboardController::class, 'updateOrderStatus'])->name('orders.update_status');
    Route::post('/orders/{id}/invoice', [SupplierDashboardController::class, 'createInvoice'])->name('orders.invoice');
});

/*
|--------------------------------------------------------------------------
| Framework Generated Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';