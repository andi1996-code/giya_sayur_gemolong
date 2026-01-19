<?php

use Illuminate\Support\Facades\Route;
use App\Services\SupplierDebtNoteService;
use App\Models\SupplierDebt;

// Custom Login Page
Route::get('/login', \App\Livewire\Auth\Login::class)
    ->name('login')
    ->middleware('guest');

// Logout Route (for Filament)
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('filament.admin.auth.logout')->middleware('auth');

// Download Supplier Debt Note
Route::get('/supplier-debt/{id}/note', function ($id) {
    $supplierDebt = SupplierDebt::findOrFail($id);
    return SupplierDebtNoteService::download($supplierDebt);
})->name('supplier-debt.note')->middleware(['auth']);

// Customer Display for POS (Real-time sync without authentication)
Route::get('/pos/customer-display', \App\Livewire\CustomerDisplay::class)->name('pos.customer-display');

// API Routes for Print Service
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/settings/printer-name', function () {
        $user = auth()->user();

        // Prioritas: printer user > printer global (settings)
        $printerName = $user->getEffectivePrinterName();
        $printViaBluetooth = $user->getEffectivePrintViaBluetooth();
        $autoPrint = $user->getEffectiveAutoPrint();

        return response()->json([
            'printer_name' => $printerName,
            'print_via_bluetooth' => $printViaBluetooth,
            'auto_print' => $autoPrint,
            'source' => !empty($user->printer_name) ? 'user' : 'global',
        ]);
    })->name('api.settings.printer-name');
});



