<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function invoicePdf(Request $request, Invoice $invoice, AuditLogger $audit)
    {
        abort_unless($invoice->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'invoices', 'export_pdf', $invoice->id);

        return Pdf::loadView('exports.invoice', ['document' => $invoice->load('customer'), 'title' => 'Invoice'])->download("invoice-{$invoice->number}.pdf");
    }

    public function quotationPdf(Request $request, Quotation $quotation, AuditLogger $audit)
    {
        abort_unless($quotation->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'quotations', 'export_pdf', $quotation->id);

        return Pdf::loadView('exports.invoice', ['document' => $quotation->load('customer'), 'title' => 'Quotation'])->download("quotation-{$quotation->number}.pdf");
    }

    public function purchaseOrderPdf(Request $request, PurchaseOrder $purchaseOrder, AuditLogger $audit)
    {
        abort_unless($purchaseOrder->company_id === $request->user()->company_id, 403);
        $audit->log($request, 'purchase-orders', 'export_pdf', $purchaseOrder->id);

        return Pdf::loadView('exports.invoice', ['document' => $purchaseOrder->load('supplier'), 'title' => 'Purchase Order'])->download("purchase-order-{$purchaseOrder->number}.pdf");
    }

    public function reportExcel(Request $request, string $type, AuditLogger $audit): StreamedResponse
    {
        $companyId = $request->user()->company_id;
        $rows = match ($type) {
            'inventory' => Product::where('company_id', $companyId)->get(['sku', 'name', 'stock_quantity', 'cost_price', 'sale_price'])->toArray(),
            'profit-loss' => [['income' => Invoice::where('company_id', $companyId)->sum('grand_total'), 'expenses' => 0, 'net_profit' => Invoice::where('company_id', $companyId)->sum('grand_total')]],
            default => Invoice::where('company_id', $companyId)->get(['number', 'invoice_date', 'status', 'grand_total', 'paid_total'])->toArray(),
        };

        $audit->log($request, 'reports', 'export_excel', null, [], ['type' => $type]);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        }, "{$type}-report.csv", ['Content-Type' => 'text/csv']);
    }
}