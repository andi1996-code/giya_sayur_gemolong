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

// Download Report PDF with proper headers
Route::get('/reports/{id}/download', function ($id) {
    $report = \App\Models\Report::findOrFail($id);
    $filePath = storage_path('app/public/' . $report->path_file);

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return response()->download($filePath, basename($filePath), [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
    ]);
})->name('reports.download')->middleware(['auth']);

// Download Report Excel with proper headers
Route::get('/reports/{id}/download-excel', function ($id) {
    $report = \App\Models\Report::findOrFail($id);

    if (!$report->excel_file) {
        abort(404, 'Excel file not found');
    }

    $filePath = storage_path('app/public/' . $report->excel_file);

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return response()->download($filePath, basename($filePath));
})->name('reports.download-excel')->middleware(['auth']);

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



