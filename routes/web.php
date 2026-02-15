<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\Admin\UserManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin');
    Route::post('/profile/update-required', [ProfileController::class, 'updateRequired'])->name('profile.updateRequired');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // General User Routes
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
    Route::get('/support', [App\Http\Controllers\SupportController::class, 'index'])->name('support');

    // Wallet Routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('wallet');
        Route::post('/create', [WalletController::class, 'createWallet'])->name('wallet.create');
        Route::post('/claim-bonus', [WalletController::class, 'claimBonus'])->name('wallet.claimBonus');
        
        // P2P Transfer Routes
        Route::get('/transfer', [TransferController::class, 'index'])->name('wallet.transfer');
        Route::post('/transfer/verify', [TransferController::class, 'verifyUser'])->name('transfer.verify');
        Route::post('/transfer/process', [TransferController::class, 'processTransfer'])->name('transfer.process');
        Route::post('/transfer/verify-pin', [TransferController::class, 'verifyPin'])->name('verify.pin');
    });

    // Notification Routes
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::post('/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('send');
        Route::get('/search-user', [\App\Http\Controllers\Admin\NotificationController::class, 'searchUser'])->name('search-user');
    });

    // User Management Routes
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::post('/block-ip', [UserManagementController::class, 'blockIp'])->name('block-ip');
        Route::delete('/unblock-ip/{blockedIp}', [UserManagementController::class, 'unblockIp'])->name('unblock-ip');
        Route::get('/download-sample', [UserManagementController::class, 'downloadSample'])->name('download-sample');
        Route::post('/import', [UserManagementController::class, 'import'])->name('import');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/status', [UserManagementController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{user}/role', [UserManagementController::class, 'updateRole'])->name('update-role');
        Route::patch('/{user}/limit', [UserManagementController::class, 'updateLimit'])->name('update-limit');
        Route::patch('/{user}/verify-email', [UserManagementController::class, 'verifyEmail'])->name('verify-email');
    });

    // Admin Wallet Management
    Route::prefix('admin/wallet')->name('admin.wallet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminWalletController::class, 'index'])->name('index');
        Route::get('/fund', [\App\Http\Controllers\Admin\AdminWalletController::class, 'fundView'])->name('fund.view');
        Route::post('/fund', [\App\Http\Controllers\Admin\AdminWalletController::class, 'fund'])->name('fund');
        Route::get('/bulk-fund', [\App\Http\Controllers\Admin\AdminWalletController::class, 'bulkFundView'])->name('bulk-fund.view');
        Route::post('/bulk-fund', [\App\Http\Controllers\Admin\AdminWalletController::class, 'bulkFund'])->name('bulk-fund');
    });

    // Service Management
    Route::prefix('admin/services')->name('admin.services.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\ServiceController::class, 'store'])->name('store');
        Route::get('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'show'])->name('show');
        Route::put('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroy'])->name('destroy');

        // Field Routes
        Route::post('/{service}/fields', [\App\Http\Controllers\Admin\ServiceController::class, 'storeField'])->name('fields.store');
        Route::put('/fields/{field}', [\App\Http\Controllers\Admin\ServiceController::class, 'updateField'])->name('fields.update');
        Route::delete('/fields/{field}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroyField'])->name('fields.destroy');

        // Price Routes
        Route::post('/{service}/prices', [\App\Http\Controllers\Admin\ServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/prices/{price}', [\App\Http\Controllers\Admin\ServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/prices/{price}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    // --- Agency Services Routes (Super Admin) ---
    Route::middleware(['role:super_admin'])->group(function () {
        
        // BVN Modification
        Route::prefix('bvn-modification')->name('bvnmod.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\BVNmodController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\BVNmodController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\BVNmodController::class, 'update'])->name('update');
        });

        // BVN User

        // CRM
        Route::prefix('crm')->name('crm.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\CRMController::class, 'index'])->name('index');
            Route::get('/export/csv', [\App\Http\Controllers\Agency\CRMController::class, 'exportCsv'])->name('export.csv');
            Route::get('/export/excel', [\App\Http\Controllers\Agency\CRMController::class, 'exportExcel'])->name('export.excel');
            Route::get('/{id}', [\App\Http\Controllers\Agency\CRMController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\CRMController::class, 'update'])->name('update');
        });

        // BVN Search
        Route::prefix('bvn-search')->name('bvn-search.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\BvnSearchController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\BvnSearchController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\BvnSearchController::class, 'update'])->name('update');
        });

        // NIN Modification
        Route::prefix('nin-modification')->name('ninmod.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\NINmodController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\NINmodController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\NINmodController::class, 'update'])->name('update');
        });

        // NIN IPE
        Route::prefix('nin-ipe')->name('ninipe.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\NinIpeController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\NinIpeController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\NinIpeController::class, 'update'])->name('update');
        });

        // NIN Personalisation
        Route::prefix('nin-personalisation')->name('nin-personalisation.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\NinPersonalisationController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\NinPersonalisationController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\NinPersonalisationController::class, 'update'])->name('update');
        });

        // Validation
        Route::prefix('validation')->name('validation.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\ValidationController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\ValidationController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\ValidationController::class, 'update'])->name('update');
        });

        // BVN Service
        Route::prefix('bvn-service')->name('bvnservice.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\BVNserviceController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\BVNserviceController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\BVNserviceController::class, 'update'])->name('update');
        });


        // VNIN to NIBSS
        Route::prefix('vnin-nibss')->name('vnin-nibss.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agency\VninToNibssController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Agency\VninToNibssController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Agency\VninToNibssController::class, 'update'])->name('update');
            Route::post('/', [\App\Http\Controllers\Agency\VninToNibssController::class, 'store'])->name('store');
        });

    });

    // Data Variation Management
    Route::prefix('admin/data-variations')->name('admin.data-variations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DataVariationController::class, 'index'])->name('index');
        Route::get('/{service}', [\App\Http\Controllers\Admin\DataVariationController::class, 'show'])->name('show');
        Route::post('/', [\App\Http\Controllers\Admin\DataVariationController::class, 'store'])->name('store');
        Route::put('/{dataVariation}', [\App\Http\Controllers\Admin\DataVariationController::class, 'update'])->name('update');
        Route::delete('/{dataVariation}', [\App\Http\Controllers\Admin\DataVariationController::class, 'destroy'])->name('destroy');
    });

    // SME Data Management
    Route::prefix('admin/sme-data')->name('admin.sme-data.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SmeDataController::class, 'index'])->name('index');
        Route::post('/sync', [\App\Http\Controllers\Admin\SmeDataController::class, 'sync'])->name('sync');
        Route::put('/{smeData}/update', [\App\Http\Controllers\Admin\SmeDataController::class, 'update'])->name('update');
    });
});

    

require __DIR__.'/auth.php';
