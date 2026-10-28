<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Licenses\Http\Controllers\AuthorizedListController;
use Modules\Licenses\Http\Controllers\ImportController;
use Modules\Licenses\Http\Controllers\LicenseController;
use Modules\Licenses\Http\Controllers\LicenseDocumentController;
use Modules\Licenses\Http\Controllers\LicenseSanctionController;
use Modules\Licenses\Http\Controllers\RegisterController;
use Modules\Licenses\Http\Controllers\VesselComplianceController;

Route::middleware('auth')->prefix('licenses')->name('licenses.')->group(function () {
    Route::get('/', [LicenseController::class, 'index'])->can(Permission::LicensesView->value)->name('index');

    Route::middleware('can:'.Permission::LicensesManage->value)->group(function () {
        Route::get('create', [LicenseController::class, 'create'])->name('create');
        Route::post('/', [LicenseController::class, 'store'])->name('store');
        Route::get('{license}/edit', [LicenseController::class, 'edit'])->whereNumber('license')->name('edit');
        Route::put('{license}', [LicenseController::class, 'update'])->whereNumber('license')->name('update');
        Route::post('{license}/documents', [LicenseDocumentController::class, 'store'])->whereNumber('license')->name('documents.store');
        Route::post('vessels/{vessel}/fnp', [VesselComplianceController::class, 'fnp'])->name('vessels.fnp');
        Route::post('vessels/{vessel}/certificates', [VesselComplianceController::class, 'certificate'])->name('vessels.certificates');
        Route::post('vessels/{vessel}/authorizations', [VesselComplianceController::class, 'authorization'])->name('vessels.authorizations');
    });

    Route::middleware('can:'.Permission::LicensesView->value)->group(function () {
        Route::get('vessels/{vessel}', [VesselComplianceController::class, 'show'])->name('vessels.show');
        Route::get('documents/{licenseDocument}', [LicenseDocumentController::class, 'download'])->name('documents.download');
        Route::get('authorized', [AuthorizedListController::class, 'index'])->name('authorized.index');
        Route::get('authorized/{publication}/download', [AuthorizedListController::class, 'download'])->name('authorized.download');
        Route::get('register', [RegisterController::class, 'index'])->name('register.index');
        Route::get('register/{year}/download', [RegisterController::class, 'download'])->whereNumber('year')->name('register.download');
        Route::get('imports', [ImportController::class, 'index'])->name('imports.index');
        Route::get('imports/{import}', [ImportController::class, 'show'])->whereNumber('import')->name('imports.show');
        Route::get('{license}', [LicenseController::class, 'show'])->whereNumber('license')->name('show');
    });

    Route::middleware('can:'.Permission::LicensesImport->value)->group(function () {
        Route::post('imports/file', [ImportController::class, 'storeFile'])->name('imports.file');
        Route::post('imports/batch', [ImportController::class, 'storeBatch'])->name('imports.batch');
        Route::put('imports/lines/{line}', [ImportController::class, 'updateLine'])->name('imports.lines.update');
        Route::post('imports/{import}/cancel', [ImportController::class, 'cancel'])->name('imports.cancel');
    });

    Route::middleware('can:'.Permission::LicensesValidate->value)->group(function () {
        Route::post('imports/lines/{line}/validate', [ImportController::class, 'validateLine'])->name('imports.lines.validate');
        Route::post('imports/{import}/validate', [ImportController::class, 'validateAll'])->name('imports.validate');
    });

    Route::middleware('can:'.Permission::LicensesSuspend->value)->group(function () {
        Route::post('{license}/suspensions', [LicenseSanctionController::class, 'suspend'])->whereNumber('license')->name('suspensions.store');
        Route::post('suspensions/{suspension}/lift', [LicenseSanctionController::class, 'lift'])->name('suspensions.lift');
    });

    Route::post('{license}/revocation', [LicenseSanctionController::class, 'revoke'])->can(Permission::LicensesRevoke->value)->whereNumber('license')->name('revocation.store');

    Route::middleware('can:'.Permission::LicensesPublish->value)->group(function () {
        Route::post('authorized', [AuthorizedListController::class, 'publish'])->name('authorized.publish');
        Route::post('register', [RegisterController::class, 'generate'])->name('register.generate');
    });
});
