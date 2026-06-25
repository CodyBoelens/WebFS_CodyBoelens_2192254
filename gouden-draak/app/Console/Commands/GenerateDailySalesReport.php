<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\SalesReport;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * UC-19: Genereer dagelijkse verkoop rapportage in Excel formaat.
 * Wordt automatisch uitgevoerd via Laravel Scheduler (zie bootstrap/app.php).
 */
class GenerateDailySalesReport extends Command
{
    protected $signature   = 'rapportage:dagelijks {--date= : Datum (Y-m-d), standaard gisteren}';
    protected $description = 'Genereer dagelijkse verkoop rapportage als Excel bestand.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->subDay();

        $dateStr = $date->toDateString();

        $this->info("Rapportage genereren voor: {$dateStr}");

        // Haal orders op van die dag
        $orders = Order::with(['items.product.category'])
            ->where('status', 'betaald')
            ->whereDate('updated_at', $dateStr)
            ->get();

        if ($orders->isEmpty()) {
            $this->warn('Geen betaalde orders gevonden voor deze datum.');
        }

        // Maak spreadsheet aan
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Verkoop ' . $dateStr);

        // Header stijl
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Headers
        $headers = ['Order #', 'Bron', 'Tafel/Klant', 'Tijdstip', 'Gerechten', 'Totaal (€)'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Data rijen
        $row       = 2;
        $grandTotal = 0;

        foreach ($orders as $order) {
            $gerechten = $order->items->map(fn($i) =>
                "{$i->product->menu_number}. {$i->product->name} x{$i->quantity}"
            )->implode(', ');

            $klant = $order->table
                ? 'Tafel ' . $order->table->number
                : ($order->customer_name ?? 'Onbekend');

            $sheet->setCellValue("A{$row}", $order->id);
            $sheet->setCellValue("B{$row}", ucfirst($order->source));
            $sheet->setCellValue("C{$row}", $klant);
            $sheet->setCellValue("D{$row}", $order->updated_at->format('H:i'));
            $sheet->setCellValue("E{$row}", $gerechten);
            $sheet->setCellValue("F{$row}", number_format($order->total, 2, '.', ''));

            $grandTotal += $order->total;
            $row++;
        }

        // Samenvatting onderaan
        $sheet->setCellValue("E{$row}", 'TOTAAL');
        $sheet->setCellValue("F{$row}", number_format($grandTotal, 2, '.', ''));
        $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true);

        // Kolombreedte automatisch aanpassen
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sla op
        $filename  = "rapportages/rapportage-{$dateStr}.xlsx";
        $fullPath  = storage_path('app/' . $filename);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($fullPath);

        // Sla record op in database
        SalesReport::updateOrCreate(
            ['report_date' => $dateStr],
            [
                'total_revenue' => $grandTotal,
                'total_orders'  => $orders->count(),
                'file_path'     => $filename,
            ]
        );

        $this->info("Rapportage opgeslagen: {$filename}");
        $this->info("Totale omzet: €" . number_format($grandTotal, 2));

        return self::SUCCESS;
    }
}
