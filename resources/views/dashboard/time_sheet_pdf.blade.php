<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Time Sheet</title>
    <style>
        @page { margin: 18px 18px 14px 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .header { width: 100%; margin-bottom: 10px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .brand {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        .sub {
            margin-top: 4px;
            font-size: 10px;
            color: #333;
        }
        .meta {
            width: 100%;
            border: 1px solid #C00000;
            border-collapse: collapse;
        }
        .meta th, .meta td {
            border: 1px solid #C00000;
            padding: 4px 6px;
        }
        .meta th {
            background: #F4D45F;
            text-align: left;
            font-weight: 700;
        }
        .meta td { background: #fff; }

        .titlebar {
            margin: 10px 0 8px 0;
            font-weight: 700;
            font-size: 11px;
            text-align: center;
        }

        table.sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.sheet th, table.sheet td {
            border: 1px solid #C00000;
            padding: 3px 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.sheet thead th {
            background: #F4D45F;
            font-weight: 700;
            text-align: center;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .status-under { background: #00B050; color: #fff; font-weight: 700; text-align: center; }
        .status-returned { background: #FFC000; color: #111; font-weight: 700; text-align: center; }
        .total-label { font-weight: 700; text-align: right; }
        .total-amount { background: #C00000; color: #fff; font-weight: 700; }

        .signatures {
            width: 100%;
            margin-top: 18px;
        }
        .signatures table { width: 100%; border-collapse: collapse; }
        .signatures td { width: 33.33%; text-align: center; padding-top: 14px; }
        .line { border-top: 1px solid #333; width: 70%; margin: 0 auto 4px auto; }
        .sig-label { font-weight: 700; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="brand">ARABIAN DENA CONTRACTING EST.</div>
                    <div class="sub">Rented Equipment List - Time Sheet</div>
                </td>
                <td style="width: 40%;">
                    <table class="meta">
                        <tr>
                            <th style="width: 45%;">Invoice Number</th>
                            <td>{{ $invoiceNumber ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $todayDate ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <td>{{ $clientName ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Vendor</th>
                            <td>{{ $vendor ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="titlebar">
        Time Sheet - {{ $Order->order_number ?? ('Order #' . $Order->id) }}
    </div>

    @php
        $totalRental = 0.0;
        foreach (($timesheetRows ?? []) as $r) {
            $totalRental += (float) ($r['total_rental_cost'] !== '' ? $r['total_rental_cost'] : 0);
        }
    @endphp

    <table class="sheet">
        <thead>
            <tr>
                <th style="width: 5%;">File #</th>
                <th style="width: 6%;">Site</th>
                <th style="width: 4%;">S.No.</th>
                <th style="width: 14%;">Description</th>
                <th style="width: 8%;">Tracking Number</th>
                <th style="width: 7%;">Invoice Number</th>
                <th style="width: 8%;">P.O. Reference</th>
                <th style="width: 7%;">Delivery Note</th>
                <th style="width: 7%;">Delivery Date</th>
                <th style="width: 6%;">Delivery Time</th>
                <th style="width: 7%;">Backload Note</th>
                <th style="width: 7%;">Backload Date</th>
                <th style="width: 5%;">Time BLKD</th>
                <th style="width: 7%;">Rental Status</th>
                <th style="width: 6%;">Rental Period</th>
                <th style="width: 7%;">Unit Rental Cost SAR</th>
                <th style="width: 8%;">Total Rental Cost SAR</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($timesheetRows ?? []) as $row)
                <tr>
                    <td class="center">{{ $row['file_no'] }}</td>
                    <td class="center">{{ $row['site'] }}</td>
                    <td class="center">{{ $row['sno'] }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td class="center">{{ $row['tracking_number'] }}</td>
                    <td class="center">{{ $row['invoice_number'] }}</td>
                    <td class="center">{{ $row['po_reference'] }}</td>
                    <td class="center">{{ $row['delivery_note'] }}</td>
                    <td class="center">{{ $row['delivery_date'] }}</td>
                    <td class="center">{{ $row['delivery_time'] }}</td>
                    <td class="center">{{ $row['backload_note'] }}</td>
                    <td class="center">{{ $row['backload_date'] }}</td>
                    <td class="center">{{ $row['time_blkd'] }}</td>
                    @php $st = strtoupper(trim((string) ($row['rental_status'] ?? ''))); @endphp
                    <td class="{{ $st === 'RETURNED' ? 'status-returned' : 'status-under' }}">
                        {{ $row['rental_status'] }}
                    </td>
                    <td class="center">{{ $row['rental_period'] }}</td>
                    <td class="right">{{ $row['unit_rental_cost'] !== '' ? ('SAR ' . $row['unit_rental_cost']) : '' }}</td>
                    <td class="right">{{ $row['total_rental_cost'] !== '' ? ('SAR ' . $row['total_rental_cost']) : '' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="16" class="total-label">Total Rental</td>
                <td class="total-amount right">{{ 'SAR ' . $totalRental }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="line"></div>
                    <div class="sig-label">ADCE Prepared By:</div>
                </td>
                <td>
                    <div class="line"></div>
                    <div class="sig-label">ADCE Approved By:</div>
                </td>
                <td>
                    <div class="line"></div>
                    <div class="sig-label">Client Approved By:</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

