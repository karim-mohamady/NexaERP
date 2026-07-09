<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #06b6d4; padding-bottom: 20px; margin-bottom: 28px; }
        .brand { font-size: 28px; font-weight: 800; }
        .muted { color: #64748b; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; }
        .total { text-align: right; font-size: 22px; font-weight: 800; margin-top: 28px; }
        .signature { margin-top: 70px; display: flex; justify-content: space-between; }
        .line { border-top: 1px solid #94a3b8; width: 220px; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">NexaERP</div>
            <div class="muted">Professional enterprise document</div>
        </div>
        <div>
            <h1>{{ $title }}</h1>
            <div class="muted">Number: {{ $document->number ?? 'DRAFT' }}</div>
            <div class="muted">Status: {{ $document->status ?? 'draft' }}</div>
        </div>
    </div>
    <p><strong>Customer/Supplier:</strong> {{ $document->customer->name ?? $document->supplier->name ?? 'N/A' }}</p>
    <p><strong>Date:</strong> {{ $document->invoice_date ?? $document->order_date ?? $document->quote_date ?? now()->toDateString() }}</p>
    <table>
        <thead><tr><th>Description</th><th>Subtotal</th><th>Tax</th><th>Discount</th><th>Total</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $title }} services and products</td>
                <td>{{ number_format((float) ($document->subtotal ?? $document->grand_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->tax_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->discount_total ?? 0), 2) }}</td>
                <td>{{ number_format((float) ($document->grand_total ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="total">Grand Total: {{ number_format((float) ($document->grand_total ?? 0), 2) }}</div>
    <div class="signature">
        <div class="line">Prepared by</div>
        <div class="line">Approved by</div>
    </div>
</body>
</html>