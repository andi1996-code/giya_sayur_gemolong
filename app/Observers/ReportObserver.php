<?php

namespace App\Observers;

use App\Exports\SalesReportExport;
use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportObserver
{
    /**
     * Build datetime from date and time
     */
    private function buildDateTime($date, $time, $isEnd = false): Carbon
    {
        $dateTime = Carbon::parse($date);

        if ($time) {
            $timeParts = explode(':', $time);
            $dateTime->setTime((int)$timeParts[0], (int)$timeParts[1], 0);
        } else {
            // Default: start = 00:00:00, end = 23:59:59
            if ($isEnd) {
                $dateTime->setTime(23, 59, 59);
            } else {
                $dateTime->setTime(0, 0, 0);
            }
        }

        return $dateTime;
    }

    /**
     * Handle the Report "creating" event.
     */
    public function creating(Report $report): void
    {
            $logo = Setting::first()->logo;
            $today = now()->format('Ymd');
            $countToday = Report::whereDate('created_at', today())
            ->count() + 1;

            // Buat nama file dan path
            $fileName = 'LAPORAN-' . $today . '-' . str_pad($countToday, 2, '0', STR_PAD_LEFT);
            $path = 'reports/' . $fileName . '.pdf';

            // Build datetime dengan filter jam
            $startDateTime = $this->buildDateTime($report->start_date, $report->start_time, false);
            $endDateTime = $this->buildDateTime($report->end_date, $report->end_time, true);

            if ($report->report_type == 'inflow') {
                // Ambil data Inflow sesuai start_date/time dan end_date/time
                $data = CashFlow::query()->where('type', 'income')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                // Ambil data transaksi untuk payment method summary
                $transactions = Transaction::query()
                    ->with('paymentMethod')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'transactions' => $transactions,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                ])->setPaper('a4', 'portrait');

            } elseif($report->report_type == 'outflow') {
                 // Ambil data Outflow sesuai start_date/time dan end_date/time
                 $data = CashFlow::query()->where('type', 'expense')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                ])->setPaper('a4', 'portrait');
            } else {
                // Ambil data Sales sesuai start_date/time dan end_date/time
                 $data = Transaction::query()
                    ->with(['transactionItems.product.category', 'paymentMethod'])
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.penjualan', [
                    'fileName' => $fileName,
                    'data' => $data,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    'simpleView' => $report->simple_view ?? false,
                ])->setPaper('a4', 'portrait');

                // Generate Excel untuk laporan penjualan
                $excelFileName = $fileName . '.xlsx';
                $excelPath = 'reports/' . $excelFileName;
                Excel::store(new SalesReportExport($data, $report->simple_view ?? false, $fileName, $startDateTime, $endDateTime), $excelPath, 'public');
                $report->excel_file = $excelPath;
            }

            // Pastikan folder 'storage/app/public/reports' ada
            $pathDirectory = storage_path('app/public/reports');
            if (!file_exists($pathDirectory)) {
                mkdir($pathDirectory, 0755, true);
            }

            // Simpan PDF ke storage
            $fullPath = storage_path('app/public/' . $path);
            $pdf->save($fullPath);

            // Set nama dan path_file ke model
            $report->name = $fileName;
            $report->path_file = $path;
    }

    /**
     * Handle the Report "update" event.
     */
    public function updated(Report $report): void
    {
            $logo = Setting::first()->logo;
            // Buat nama file dan path
            $path = 'reports/' . $report->name . '.pdf';

            // Build datetime dengan filter jam
            $startDateTime = $this->buildDateTime($report->start_date, $report->start_time, false);
            $endDateTime = $this->buildDateTime($report->end_date, $report->end_time, true);

            if ($report->report_type == 'inflow') {
                // Ambil data Inflow sesuai start_date/time dan end_date/time
                $data = CashFlow::query()->where('type', 'income')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                // Ambil data transaksi untuk payment method summary
                $transactions = Transaction::query()
                    ->with('paymentMethod')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'transactions' => $transactions,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                ])->setPaper('a4', 'portrait');

            } elseif($report->report_type == 'outflow') {
                 // Ambil data Outflow sesuai start_date/time dan end_date/time
                 $data = CashFlow::query()->where('type', 'expense')
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                ])->setPaper('a4', 'portrait');
            } else {
                // Ambil data Sales sesuai start_date/time dan end_date/time
                 $data = Transaction::query()
                    ->with(['transactionItems.product.category', 'paymentMethod'])
                    ->where('updated_at', '>=', $startDateTime)
                    ->where('updated_at', '<=', $endDateTime)
                    ->get();

                 // Generate PDF
                $pdf = Pdf::loadView('pdf.reports.penjualan', [
                    'fileName' => $report->name,
                    'data' => $data,
                    'logo' => $logo,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    'simpleView' => $report->simple_view ?? false,
                ])->setPaper('a4', 'portrait');

                // Generate Excel untuk laporan penjualan (update)
                $excelFileName = $report->name . '.xlsx';
                $excelPath = 'reports/' . $excelFileName;
                Excel::store(new SalesReportExport($data, $report->simple_view ?? false, $report->name, $startDateTime, $endDateTime), $excelPath, 'public');
                $report->excel_file = $excelPath;
            }

            // Pastikan folder 'storage/app/public/reports' ada
            $pathDirectory = storage_path('app/public/reports');
            if (!file_exists($pathDirectory)) {
                mkdir($pathDirectory, 0755, true);
            }

            // Simpan PDF ke storage
            $fullPath = storage_path('app/public/' . $path);
            $pdf->save($fullPath);

    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        // Hapus file PDF
        $pdfPath = 'public/reports/' . $report->name;
        if (Storage::exists($pdfPath)) {
            Storage::delete($pdfPath);
        }

        // Hapus file Excel jika ada
        if ($report->excel_file) {
            $excelPath = 'public/' . $report->excel_file;
            if (Storage::exists($excelPath)) {
                Storage::delete($excelPath);
            }
        }
    }

}
