<?php

use App\Http\Controllers\Platform\AuthController;
use App\Http\Controllers\Platform\StorageAccessController;
use App\Http\Controllers\Platform\SupportController;
use App\Http\Controllers\Platform\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('platform')->name('platform.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    Route::middleware(['auth:platform', 'platform.admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', function () {
            return redirect()->route('platform.tenants.index');
        })->name('home');

        Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::post('tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::post('tenants/{tenant}/discard', [TenantController::class, 'discard'])->name('tenants.discard');
        Route::post('tenants/{tenant}/destroy', [TenantController::class, 'destroy'])->name('tenants.destroy');
        Route::post('tenants/{tenant}/support/enter', [SupportController::class, 'enter'])->name('tenants.support.enter');
        Route::post('storage/test-access', [StorageAccessController::class, 'test'])->name('storage.test-access');
    });
});
