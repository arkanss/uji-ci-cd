<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #fff2cc;
            font-weight: bold;
            text-align: center;
        }

        .no-border td {
            border: none;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
        }

        .po-line {
            display: inline-block;
            min-width: 160px;
            border-bottom: 3px solid #000;
            text-align: right;
            padding-bottom: 2px;
        }

        .order-box-title {
            background: #fff2cc;
            font-weight: bold;
            text-align: center;
        }

        .order-box-body {
            height: 70px;
            text-align: center;
            vertical-align: middle;
        }

        .signature-box {
            height: 150px;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 20px;
        }

        .summary-wrapper {
            border: 1px solid #000;
        }

        .summary-inner td,
        .summary-inner th {
            border: none !important;
        }

        .signature-box {
            height: 150px;
            text-align: center;
            vertical-align: top;
            padding-top: 25px;
        }

        .signature-table td {
            border: none !important;
        }
    </style>
</head>

<body>

    <table class="no-border">
        <tr>
            <td>
                <img src="{{ public_path('images/logo_pt_daya.png') }}" alt="DAYA"
                    style="height:40px; margin-bottom:5px;">
                <br>

                <strong>PT. DISTRIBUSI AKSELERASI KARYA</strong><br>
                Jl. Pondasi No. 21A, Kayu Putih, Pulogadung, Jakarta Timur<br>
                <u>dayaofficialho@gmail.com</u>
            </td>
            <td class="right header-title">PURCHASE ORDER</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <td width="65%" style="padding:0;">
                <table class="table-inner">
                    <tr>
                        <td class="order-box-title">Order To :</td>
                    </tr>
                    <tr>
                        <td class="order-box-body">
                            <strong>{{ $vendor?->name }}</strong>
                        </td>
                    </tr>
                </table>
            </td>

            <td width="35%">
                <table class="no-border">
                    <tr>
                        <td>PO Number</td>
                        <td class="right"><span class="po-line">{{ $po->po_number }}</span></td>
                    </tr>
                    <tr>
                        <td>PO Date</td>
                        <td class="right"><span class="po-line">{{ $po->date?->format('d F Y') }}</span></td>
                    </tr>
                    <tr>
                        <td>Expire Date</td>
                        <td class="right"><span
                                class="po-line">{{ $po->expected_delivery_date?->format('d F Y') }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="22%">VARIAN PRODUCT</th>
                <th width="12%">BRAND</th>
                <th width="10%">UNIT QUANTITY</th>
                <th width="10%">CARTON UNIT</th>
                <th width="10%">PRICE</th>
                <th width="12%">AMOUNT</th>
                <th width="20%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="center">{{ $item['brand'] }}</td>
                    <td class="right">{{ number_format($item['unit_qty'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item['carton_unit'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                    <td>{{ $item['description'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table style="border:1px solid #000;">
        <tr>
            <td width="65%" style="padding:0;">
                <table>
                    <tr>
                        <th colspan="6">SUMMARY ORDER :</th>
                    </tr>
                    <tr>
                        <td colspan="2">Total Product Varian :</td>
                        <td class="right">{{ count($items) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Unit :</td>
                        <td class="right">
                            {{ number_format($po->items->sum('requested_stock'), 0, ',', '.') }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Carton :</td>
                        <td class="right">
                            {{ number_format($po->items->sum('requested_stock') / 24, 0, ',', '.') }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Amount :</td>
                        <td class="right">
                            Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">Description of Amount :</td>
                        <td colspan="4" style="height:50px;">
                            {{ $terbilang }}
                        </td>
                    </tr>
                </table>
            </td>

            <td width="35%" style="padding:0;">
                <table class="signature-table">
                    <tr>
                        <td style="height:170px; text-align:center; vertical-align:top; padding-top:25px;">

                            Best Regards,<br><br>

                            @if ($signature)
                                <img src="{{ $signature }}" alt="Signature" style="height:80px;"><br>
                            @endif

                            <strong>PT. Distribusi Akselerasi Karya</strong><br>
                                FIN & ACC Departemen
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
