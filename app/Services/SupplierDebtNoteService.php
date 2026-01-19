<?php

namespace App\Services;

use App\Models\SupplierDebt;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierDebtNoteService
{
    /**
     * Generate PDF nota for supplier debt
     */
    public static function generatePdf(SupplierDebt $supplierDebt)
    {
        $data = [
            'supplierDebt' => $supplierDebt,
            'generatedAt' => now()->format('d-m-Y H:i:s'),
            'store' => \App\Models\Setting::first(),
        ];

        $pdf = Pdf::loadView('reports.supplier-debt-note', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 5)
            ->setOption('margin-right', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5);

        return $pdf;
    }

    /**
     * Download PDF nota
     */
    public static function download(SupplierDebt $supplierDebt)
    {
        return self::generatePdf($supplierDebt)
            ->download('Nota-Piutang-' . $supplierDebt->id . '.pdf');
    }

    /**
     * Stream PDF nota
     */
    public static function stream(SupplierDebt $supplierDebt)
    {
        return self::generatePdf($supplierDebt)->stream();
    }
}
