<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class BarcodePrintDialog extends Component
{
    public $selectedRecords = [];
    public $showDialog = false;

    public function mount($records = [])
    {
        $this->selectedRecords = $records;
    }

    public function printBarcodes($format)
    {
        $records = Product::whereIn('id', $this->selectedRecords)->get();

        return $this->generateBarcodeAndDownload($records, $format);
    }

    protected function generateBarcodeAndDownload($records, $format)
    {
        $barcodes = [];
        $barcodeGenerator = new \Milon\Barcode\DNS1D();

        foreach ($records as $product) {
            $barcodes[] = [
                'name' => $product->name,
                'price' => $product->price,
                'barcode' => 'data:image/png;base64,' . $barcodeGenerator->getBarcodePNG($product->barcode, 'C128'),
                'number' => $product->barcode
            ];
        }

        // Tentukan ukuran kertas berdasarkan format
        $paperSize = match($format) {
            'label_33x15' => [85, 38], // 33mm x 15mm dalam mm
            'label_60x30' => [152, 76], // 60mm x 30mm dalam mm
            default => 'a4',
        };

        $orientation = 'portrait';
        if ($format === 'label_33x15' || $format === 'label_60x30') {
            $orientation = 'landscape';
        }

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.barcodes.barcode', compact('barcodes', 'format'))
            ->setPaper($paperSize, $orientation);

        // Tentukan nama file
        $filename = match($format) {
            'label_33x15' => 'barcodes_33x15.pdf',
            'label_60x30' => 'barcodes_60x30.pdf',
            default => 'barcodes.pdf',
        };

        // Trigger download
        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.barcode-print-dialog');
    }
}
