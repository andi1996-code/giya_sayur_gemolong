<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SalesReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    protected $data;
    protected $simpleView;
    protected $fileName;
    protected $startDateTime;
    protected $endDateTime;
    protected $styleInfo = [];
    protected $currencyColumns = [];

    public function __construct($data, $simpleView, $fileName, $startDateTime, $endDateTime)
    {
        $this->data = $data;
        $this->simpleView = $simpleView;
        $this->fileName = $fileName;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }

    public function array(): array
    {
        $rows = [];
        $currentRow = 1;

        // ================= HEADER SECTION =================
        $rows[] = ['LAPORAN PENJUALAN']; // Row 1
        $this->styleInfo['report_title'] = $currentRow++;

        $rows[] = [$this->fileName]; // Row 2
        $this->styleInfo['file_title'] = $currentRow++;

        $rows[] = ['Periode: ' . $this->startDateTime->format('d F Y H:i') . ' s/d ' . $this->endDateTime->format('d F Y H:i')]; // Row 3
        $this->styleInfo['period'] = $currentRow++;

        $rows[] = ['']; // Empty row - Row 4
        $currentRow++;

        // ================= DATA PREPARATION =================
        $categoriesData = [];
        $totalOrderAmount = 0;
        $totalProfitAmount = 0;

        // Track currency columns for formatting
        $this->currencyColumns = $this->simpleView
            ? ['C', 'D']  // Simple view: Total Bayar, Total Profit
            : ['B', 'C', 'E', 'F']; // Detail view: Harga Modal, Harga Jual, Total Bayar, Total Profit

        // Group data by category
        foreach($this->data as $order) {
            foreach($order->transactionItems as $item) {
                // Skip items dengan product yang null (produk sudah dihapus)
                if (!$item->product) {
                    continue;
                }

                $categoryName = $item->product->category->name ?? 'Tanpa Kategori';
                $transactionKey = $order->id;

                if (!isset($categoriesData[$categoryName])) {
                    $categoriesData[$categoryName] = [
                        'transactions' => [],
                        'total_amount' => 0,
                        'total_profit' => 0
                    ];
                }

                if (!isset($categoriesData[$categoryName]['transactions'][$transactionKey])) {
                    $categoriesData[$categoryName]['transactions'][$transactionKey] = [
                        'order' => $order,
                        'items' => [],
                        'transaction_total' => 0,
                        'transaction_profit' => 0,
                        'item_count' => 0
                    ];
                }

                $categoriesData[$categoryName]['transactions'][$transactionKey]['items'][] = $item;
                $itemTotal = $item->price * $item->quantity;
                $categoriesData[$categoryName]['transactions'][$transactionKey]['transaction_total'] += $itemTotal;
                $categoriesData[$categoryName]['transactions'][$transactionKey]['transaction_profit'] += $item->total_profit;
                $categoriesData[$categoryName]['transactions'][$transactionKey]['item_count']++;

                $categoriesData[$categoryName]['total_amount'] += $itemTotal;
                $categoriesData[$categoryName]['total_profit'] += $item->total_profit;

                $totalOrderAmount += $itemTotal;
                $totalProfitAmount += $item->total_profit;
            }
        }

        // ================= DATA RENDERING =================
        if ($this->simpleView) {
            // ============ SIMPLE VIEW ============
            $sectionStartRow = $currentRow;

            foreach($categoriesData as $categoryName => $categoryData) {
                // Category Header
                $rows[] = ['KATEGORI: ' . strtoupper($categoryName)];
                $this->styleInfo['category_headers'][] = [
                    'row' => $currentRow++,
                    'start_col' => 'A',
                    'end_col' => 'D'
                ];

                // Column Headers
                $rows[] = [
                    'NO. TRANSAKSI',
                    'JENIS PEMBAYARAN',
                    'TOTAL BAYAR',
                    'TOTAL PROFIT'
                ];
                $this->styleInfo['table_headers'][] = $currentRow++;

                $dataStartRow = $currentRow;

                // Transaction Rows
                foreach($categoryData['transactions'] as $transaction) {
                    $rows[] = [
                        $transaction['order']->transaction_number,
                        $transaction['order']->paymentMethod->name ?? 'Tidak Ada',
                        $transaction['transaction_total'],
                        $transaction['transaction_profit']
                    ];
                    $currentRow++;
                }

                $dataEndRow = $currentRow - 1;

                // Category Subtotal
                $rows[] = [
                    'SUBTOTAL ' . strtoupper($categoryName),
                    '',
                    $categoryData['total_amount'],
                    $categoryData['total_profit']
                ];
                $this->styleInfo['category_subtotals'][] = [
                    'row' => $currentRow++,
                    'data_range' => "C{$dataStartRow}:D{$dataEndRow}"
                ];

                // Empty row between categories
                $rows[] = [''];
                $currentRow++;
            }

            $this->styleInfo['simple_section_range'] = "A{$sectionStartRow}:D" . ($currentRow - 1);

        } else {
            // ============ DETAIL VIEW ============
            $sectionStartRow = $currentRow;

            foreach($categoriesData as $categoryName => $categoryData) {
                // Category Header
                $rows[] = ['KATEGORI: ' . strtoupper($categoryName)];
                $this->styleInfo['category_headers'][] = [
                    'row' => $currentRow++,
                    'start_col' => 'A',
                    'end_col' => 'F'
                ];

                // Empty row
                $rows[] = [''];
                $currentRow++;

                $categoryDataStartRow = $currentRow;

                foreach($categoryData['transactions'] as $transaction) {
                    // Transaction Header
                    $rows[] = [
                        'No. Transaksi:',
                        $transaction['order']->transaction_number,
                        '',
                        'Pembayaran:',
                        $transaction['order']->paymentMethod->name ?? 'Tidak Ada'
                    ];
                    $this->styleInfo['transaction_headers'][] = $currentRow++;

                    // Item Table Headers
                    $rows[] = [
                        'PRODUK',
                        'HARGA MODAL',
                        'HARGA JUAL',
                        'QTY',
                        'TOTAL BAYAR',
                        'TOTAL PROFIT'
                    ];
                    $this->styleInfo['table_headers'][] = $currentRow++;

                    $itemsStartRow = $currentRow;

                    // Item Rows
                    foreach($transaction['items'] as $item) {
                        // Skip items dengan product yang null
                        if (!$item->product) {
                            continue;
                        }

                        $qty = $item->weight
                            ? number_format($item->weight, 3, ',', '.') . ' kg'
                            : $item->quantity;

                        $rows[] = [
                            $item->product->name,
                            $item->cost_price,
                            $item->price,
                            $qty,
                            $item->price * $item->quantity,
                            $item->total_profit
                        ];
                        $currentRow++;
                    }

                    $itemsEndRow = $currentRow - 1;

                    // Transaction Subtotal
                    $rows[] = [
                        'SUBTRANSAKSI',
                        '',
                        '',
                        $transaction['item_count'] . ' item(s)',
                        $transaction['transaction_total'],
                        $transaction['transaction_profit']
                    ];
                    $this->styleInfo['transaction_subtotals'][] = [
                        'row' => $currentRow++,
                        'data_range' => "E{$itemsStartRow}:F{$itemsEndRow}"
                    ];

                    // Empty row between transactions
                    $rows[] = [''];
                    $currentRow++;
                }

                $categoryDataEndRow = $currentRow - 1;

                // Category Summary
                $rows[] = ['RINGKASAN KATEGORI'];
                $this->styleInfo['category_summary_headers'][] = $currentRow++;

                $rows[] = ['Total Transaksi:', count($categoryData['transactions'])];
                $currentRow++;

                $rows[] = ['Total Uang Masuk:', $categoryData['total_amount']];
                $this->styleInfo['category_amounts'][] = $currentRow++;

                $rows[] = ['Total Keuntungan:', $categoryData['total_profit']];
                $this->styleInfo['category_profits'][] = $currentRow++;

                // Separator
                $rows[] = array_fill(0, 6, '');
                $this->styleInfo['category_separators'][] = $currentRow++;
            }

            $this->styleInfo['detail_section_range'] = "A{$sectionStartRow}:F" . ($currentRow - 1);
        }

        // ================= GRAND TOTAL SECTION =================
        $rows[] = ['RINGKASAN KESELURUHAN'];
        $this->styleInfo['grand_total_header'] = $currentRow++;

        $rows[] = ['Total Transaksi:', count($this->data)];
        $currentRow++;

        $rows[] = ['Total Uang Masuk:', $totalOrderAmount];
        $this->styleInfo['grand_total_amount'] = $currentRow++;

        $rows[] = ['Total Keuntungan:', $totalProfitAmount];
        $this->styleInfo['grand_total_profit'] = $currentRow++;

        // Profit Margin Calculation
        $profitMargin = $totalOrderAmount > 0 ? ($totalProfitAmount / $totalOrderAmount) * 100 : 0;
        $rows[] = ['Margin Keuntungan:', number_format($profitMargin, 2) . '%'];
        $this->styleInfo['profit_margin'] = $currentRow++;

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        // ============ HEADER STYLES ============
        // Main Report Title
        $styles[$this->styleInfo['report_title']] = [
            'font' => [
                'bold' => true,
                'size' => 18,
                'color' => ['rgb' => '1F497D']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // File Title
        $styles[$this->styleInfo['file_title']] = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '2F5496']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];

        // Period
        $styles[$this->styleInfo['period']] = [
            'font' => [
                'italic' => true,
                'size' => 11,
                'color' => ['rgb' => '7F7F7F']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];

        // ============ CATEGORY HEADERS ============
        if (isset($this->styleInfo['category_headers'])) {
            foreach($this->styleInfo['category_headers'] as $headerInfo) {
                $styles[$headerInfo['row']] = [
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];
            }
        }

        // ============ TABLE HEADERS ============
        if (isset($this->styleInfo['table_headers'])) {
            foreach($this->styleInfo['table_headers'] as $row) {
                $styles[$row] = [
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5B9BD5']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF']
                        ]
                    ]
                ];
            }
        }

        // ============ TRANSACTION HEADERS (Detail View Only) ============
        if (isset($this->styleInfo['transaction_headers'])) {
            foreach($this->styleInfo['transaction_headers'] as $row) {
                $styles[$row] = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DDEBF7']
                    ]
                ];
            }
        }

        // ============ SUBTOTAL ROWS ============
        // Category Subtotals (Simple View)
        if (isset($this->styleInfo['category_subtotals'])) {
            foreach($this->styleInfo['category_subtotals'] as $subtotalInfo) {
                $styles[$subtotalInfo['row']] = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2EFDA']
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => 'A9D08E']
                        ]
                    ]
                ];
            }
        }

        // Transaction Subtotals (Detail View)
        if (isset($this->styleInfo['transaction_subtotals'])) {
            foreach($this->styleInfo['transaction_subtotals'] as $subtotalInfo) {
                $styles[$subtotalInfo['row']] = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6']
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'F4B084']
                        ]
                    ]
                ];
            }
        }

        // ============ CATEGORY SUMMARY ============
        if (isset($this->styleInfo['category_summary_headers'])) {
            foreach($this->styleInfo['category_summary_headers'] as $row) {
                $styles[$row] = [
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '1F497D']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ];
            }
        }

        // ============ GRAND TOTAL SECTION ============
        $styles[$this->styleInfo['grand_total_header']] = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '203764']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        $styles[$this->styleInfo['grand_total_amount']] = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '2F75B5']
            ]
        ];

        $styles[$this->styleInfo['grand_total_profit']] = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '00B050']
            ]
        ];

        $styles[$this->styleInfo['profit_margin']] = [
            'font' => [
                'bold' => true,
                'italic' => true,
                'color' => ['rgb' => 'FF6600']
            ]
        ];

        // ============ GENERAL DATA CELL STYLES ============
        // Apply borders to all data cells
        $dataRange = isset($this->styleInfo['simple_section_range'])
            ? $this->styleInfo['simple_section_range']
            : $this->styleInfo['detail_section_range'];

        $styles[$dataRange] = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '4472C4']
                ],
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9']
                ]
            ]
        ];

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // ============ MERGE CELLS ============
                // Header cells
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->mergeCells('A3:' . $highestColumn . '3');

                // Category headers
                if (isset($this->styleInfo['category_headers'])) {
                    foreach($this->styleInfo['category_headers'] as $headerInfo) {
                        $sheet->mergeCells("A{$headerInfo['row']}:{$headerInfo['end_col']}{$headerInfo['row']}");
                    }
                }

                // Grand total header
                $sheet->mergeCells("A{$this->styleInfo['grand_total_header']}:B{$this->styleInfo['grand_total_header']}");

                // ============ COLUMN WIDTHS ============
                if ($this->simpleView) {
                    $sheet->getColumnDimension('A')->setWidth(25); // No. Transaksi
                    $sheet->getColumnDimension('B')->setWidth(20); // Jenis Pembayaran
                    $sheet->getColumnDimension('C')->setWidth(18); // Total Bayar
                    $sheet->getColumnDimension('D')->setWidth(18); // Total Profit
                } else {
                    $sheet->getColumnDimension('A')->setWidth(30); // Produk
                    $sheet->getColumnDimension('B')->setWidth(15); // Harga Modal
                    $sheet->getColumnDimension('C')->setWidth(15); // Harga Jual
                    $sheet->getColumnDimension('D')->setWidth(12); // Qty
                    $sheet->getColumnDimension('E')->setWidth(18); // Total Bayar
                    $sheet->getColumnDimension('F')->setWidth(18); // Total Profit
                }

                // ============ ROW HEIGHTS ============
                $sheet->getRowDimension($this->styleInfo['report_title'])->setRowHeight(30);
                $sheet->getRowDimension($this->styleInfo['grand_total_header'])->setRowHeight(25);

                // Set height for category headers
                if (isset($this->styleInfo['category_headers'])) {
                    foreach($this->styleInfo['category_headers'] as $headerInfo) {
                        $sheet->getRowDimension($headerInfo['row'])->setRowHeight(20);
                    }
                }

                // Set height for table headers
                if (isset($this->styleInfo['table_headers'])) {
                    foreach($this->styleInfo['table_headers'] as $row) {
                        $sheet->getRowDimension($row)->setRowHeight(18);
                    }
                }

                // ============ ALIGNMENTS ============
                // Center align all headers
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Right align currency columns
                foreach ($this->currencyColumns as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Center align quantity column in detail view
                if (!$this->simpleView) {
                    $sheet->getStyle("D5:D{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ============ NUMBER FORMATTING ============
                // Apply currency format to all currency columns
                $currencyFormat = '"Rp"#,##0.00;[Red]"Rp"#,##0.00';
                foreach ($this->currencyColumns as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode($currencyFormat);
                }

                // ============ DATA VALIDATION BORDERS ============
                // Apply thin borders to all data cells
                $dataStartRow = 5;
                $dataRange = "A{$dataStartRow}:{$highestColumn}{$highestRow}";

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9D9']
                        ]
                    ]
                ]);

                // ============ SEPARATOR ROWS ============
                if (isset($this->styleInfo['category_separators'])) {
                    foreach($this->styleInfo['category_separators'] as $row) {
                        $sheet->getRowDimension($row)->setRowHeight(10);
                    }
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];

        // Pre-define currency formats for columns
        $currencyFormat = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;

        if ($this->simpleView) {
            $formats['C'] = '"Rp"#,##0'; // Total Bayar
            $formats['D'] = '"Rp"#,##0'; // Total Profit
        } else {
            $formats['B'] = '"Rp"#,##0'; // Harga Modal
            $formats['C'] = '"Rp"#,##0'; // Harga Jual
            $formats['E'] = '"Rp"#,##0'; // Total Bayar
            $formats['F'] = '"Rp"#,##0'; // Total Profit
        }

        return $formats;
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
