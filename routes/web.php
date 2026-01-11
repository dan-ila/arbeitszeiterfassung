<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\WorkTimeRequestController;
use App\Http\Controllers\AdminWorkTimeRequestController;
use App\Http\Controllers\AdminVacationRequestController;
use App\Http\Controllers\WebWorkLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 2FA challenge (must be accessible AFTER login but BEFORE dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/2fa/challenge', [TwoFactorController::class, 'challenge'])
        ->name('2fa.challenge');

    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
        ->name('2fa.verify');
});

/*
|--------------------------------------------------------------------------
| Protected routes (auth + 2FA)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', '2fa'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('users.dashboard');

    Route::get('/dashboard/export', [UserManagementController::class, 'export'])
        ->name('users.dashboard.export');

    Route::get('/settings', [SettingsController::class, 'edit'])
        ->name('users.settings');

    Route::put('/settings', [SettingsController::class, 'update'])
        ->name('users.settings.update');

// routes/web.php (inside auth+2fa middleware)
    Route::get('/vacation', [VacationController::class, 'index'])->name('users.vacation');
    Route::get('/vacation/create', [VacationController::class, 'create'])->name('users.vacation.create');
    Route::post('/vacation', [VacationController::class, 'store'])->name('users.vacation.store');


    /*
    |--------------------------------------------------------------------------
    | Work logs (web/manual, user)
    |--------------------------------------------------------------------------
    */
    Route::get('/worklogs/create', [WebWorkLogController::class, 'create'])
        ->name('users.worklogs.create');

    Route::post('/worklogs', [WebWorkLogController::class, 'store'])
        ->name('users.worklogs.store');

    Route::delete('/worklogs/{workLog}', [WebWorkLogController::class, 'destroy'])
        ->name('users.worklogs.destroy');


    /*
    |--------------------------------------------------------------------------
    | Work time requests (user)
    |--------------------------------------------------------------------------
    */
    Route::get('/worktime/requests/create', [WorkTimeRequestController::class, 'create'])
        ->name('worktime.requests.create');

    Route::get('/worktime/requests/{workLog}/edit', [WorkTimeRequestController::class, 'edit'])
        ->name('worktime.requests.edit');

    Route::post('/worktime/requests', [WorkTimeRequestController::class, 'store'])
        ->name('worktime.requests.store');


    /*
    |--------------------------------------------------------------------------
    | 2FA setup (user settings)
    |--------------------------------------------------------------------------
    */
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');

    /*
    |--------------------------------------------------------------------------
    | Admin-only routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {

        Route::get('/user/management', [UserManagementController::class, 'index'])
            ->name('admin.user.management');

        Route::get('/user/management/create', [UserManagementController::class, 'create'])
            ->name('admin.user.management.create');

        Route::post('/user/management/store', [UserManagementController::class, 'store'])
            ->name('admin.user.management.store');

        Route::get('/user/management/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('admin.user.management.edit');

        Route::put('/user/management/{user}', [UserManagementController::class, 'update'])
            ->name('admin.user.management.update');

        Route::delete('/user/management/{user}', [UserManagementController::class, 'destroy'])
            ->name('admin.user.management.destroy');

        Route::post('/user/management/{user}/send-password-link',
            [UserManagementController::class, 'sendPasswordLink']
        )->name('admin.user.management.sendPasswordLink');

        Route::get('/logs', [LogController::class, 'index'])
            ->name('admin.logs.index');

        Route::get('/terminals', [TerminalController::class, 'index'])
            ->name('terminals.index');

        Route::get('/terminals/create', [TerminalController::class, 'create'])
            ->name('terminals.create');

        Route::post('/terminals', [TerminalController::class, 'store'])
            ->name('terminals.store');

        Route::get('/terminals/{terminal}/edit', [TerminalController::class, 'edit'])
            ->name('terminals.edit');

        Route::put('/terminals/{terminal}', [TerminalController::class, 'update'])
            ->name('terminals.update');

        Route::post('/terminals/{terminal}/enable', [TerminalController::class, 'enable'])
            ->name('terminals.enable');

        Route::post('/terminals/{terminal}/disable', [TerminalController::class, 'disable'])
            ->name('terminals.disable');

        Route::delete('/terminals/{terminal}', [TerminalController::class, 'destroy'])
            ->name('terminals.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Requests (manager + admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('requests')->group(function () {
        Route::get('/admin/worktime/requests', [AdminWorkTimeRequestController::class, 'index'])
            ->name('admin.worktime.requests.index');

        Route::post('/admin/worktime/requests/{workTimeRequest}/approve', [AdminWorkTimeRequestController::class, 'approve'])
            ->name('admin.worktime.requests.approve');

        Route::post('/admin/worktime/requests/{workTimeRequest}/reject', [AdminWorkTimeRequestController::class, 'reject'])
            ->name('admin.worktime.requests.reject');

        Route::post('/admin/vacation/requests/{vacation}/approve', [AdminVacationRequestController::class, 'approve'])
            ->name('admin.vacation.requests.approve');

        Route::post('/admin/vacation/requests/{vacation}/reject', [AdminVacationRequestController::class, 'reject'])
            ->name('admin.vacation.requests.reject');
    });
});

/*
|--------------------------------------------------------------------------
| Password reset / set routes
|--------------------------------------------------------------------------
*/

Route::get('/reset-password/{token}', [AuthController::class, 'showSetPasswordForm'])
    ->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'setPassword'])
    ->name('password.store');
