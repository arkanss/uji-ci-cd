<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Delivery Note - {{ $delivery->code }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .container {
            padding: 24px;
        }

        .header {
            position: relative;
            margin-bottom: 24px;
            min-height: 120px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .text-sm {
            font-size: 12px;
        }

        .text-xs {
            font-size: 10px;
            color: #6b7280;
        }

        .qr {
            position: absolute;
            top: 0;
            right: 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 16px 0 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 4px;
            background: #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <div class="title">Delivery Note: {{ $delivery->code }}</div>
            <div class="text-sm">Date: {{ $delivery->date?->format('d M Y H:i') }}</div>
            <div class="text-sm">Driver: {{ $delivery->driver->name ?? '-' }}</div>
            <div class="text-sm"> Verified by: {{ $delivery->distributions->first()?->verifier?->name ?? 'System' }}
            </div> <img class="qr" src="{{ $qrBase64 }}" width="90" height="90">
        </div>

        <div class="section-title">Distributions</div>

        @foreach ($delivery->distributions as $dist)
            <div class="text-sm" style="margin-bottom:6px;">
                <strong>PO:</strong> {{ $dist->code }}
                &nbsp;|&nbsp;
                <strong>Status:</strong> {{ $dist->status?->label() ?? '-' }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:center">Requested Qty</th>
                        <th style="text-align:center">Approved Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dist->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td style="text-align:center">{{ $item->requested_stock }}</td>
                            <td style="text-align:center">{{ $item->approved_stock ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

    </div>
</body>

</html>
