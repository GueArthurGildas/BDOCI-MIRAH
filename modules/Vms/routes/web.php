<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Vms\Http\Controllers\BeaconController;
use Modules\Vms\Http\Controllers\ErsController;
use Modules\Vms\Http\Controllers\FailureController;
use Modules\Vms\Http\Controllers\RequestController;

$view = Permission::VmsView->value;
$create = Permission::VmsCreate->value;
$manage = Permission::VmsManage->value;

Route::middleware('auth')->prefix('vms')->name('vms.')->group(function () use ($view, $create, $manage) {
    Route::get('beacons', [BeaconController::class, 'index'])->can($view)->name('beacons.index');
    Route::post('beacons', [BeaconController::class, 'store'])->can($manage)->name('beacons.store');
    Route::post('beacons/assign', [BeaconController::class, 'assign'])->can($create)->name('beacons.assign');
    Route::post('obligations', [BeaconController::class, 'obligations'])->can($manage)->name('obligations.store');

    Route::get('requests', [RequestController::class, 'index'])->can($view)->name('requests.index');
    Route::post('requests/assessments', [RequestController::class, 'requestAssessment'])->can($create)->name('requests.assessment');
    Route::post('requests/assessments/{assessmentRequest}', [RequestController::class, 'assess'])->can($create)->name('requests.assess');
    Route::post('requests/new-beacons', [RequestController::class, 'requestNewBeacon'])->can($create)->name('requests.new-beacon');
    Route::post('requests/new-beacons/{newBeaconRequest}/install', [RequestController::class, 'install'])->can($create)->name('requests.install');
    Route::post('requests/installers', [RequestController::class, 'storeInstaller'])->can($manage)->name('requests.installers.store');

    Route::get('failures', [FailureController::class, 'index'])->can($view)->name('failures.index');
    Route::post('failures', [FailureController::class, 'store'])->can($create)->name('failures.store');
    Route::get('failures/{failure}', [FailureController::class, 'show'])->can($view)->name('failures.show');
    Route::post('failures/{failure}/reports', [FailureController::class, 'report'])->can($create)->name('failures.report');
    Route::post('failures/{failure}/attestation', [FailureController::class, 'attest'])->can($create)->name('failures.attest');
    Route::post('failures/{failure}/cessation-order', [FailureController::class, 'order'])->can($manage)->name('failures.order');
    Route::post('cutoffs', [FailureController::class, 'authorizeCutoff'])->can($manage)->name('cutoffs.store');

    Route::get('ers', [ErsController::class, 'index'])->can($view)->name('ers.index');
    Route::get('ers/create', [ErsController::class, 'create'])->can($create)->name('ers.create');
    Route::post('ers', [ErsController::class, 'store'])->can($create)->name('ers.store');
    Route::get('ers/{declaration}', [ErsController::class, 'show'])->can($view)->name('ers.show');
});
