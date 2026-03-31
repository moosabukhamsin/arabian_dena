<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Request - {{ $Order->order_number ?? ('Order #' . $Order->id) }}</title>
    <style>
        @page { margin: 18px; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
        }
        .sheet {
            border: 2px solid #111;
        }
        .header {
            padding: 10px 12px 6px 12px;
            text-align: center;
            border-bottom: 2px solid #111;
        }
        .header .company {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header .title {
            margin-top: 6px;
            font-size: 12px;
            font-weight: bold;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            border-bottom: 1px solid #111;
            padding: 6px 8px;
        }
        .meta .label {
            width: 14%;
            background: #f4d45f;
            font-weight: bold;
            border-right: 1px solid #111;
        }
        .meta .value {
            width: 36%;
            border-right: 1px solid #111;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #111;
            padding: 6px 6px;
            vertical-align: top;
        }
        .table thead th {
            background: #f4d45f;
            font-weight: bold;
            text-align: center;
        }
        .table td.num,
        .table td.qty {
            text-align: center;
            width: 8%;
        }
        .table td.desc {
            width: 52%;
        }
        .notes {
            width: 100%;
            border-collapse: collapse;
        }
        .notes td {
            border: 1px solid #111;
            padding: 10px 8px;
        }
        .notes .label {
            width: 10%;
            background: #f4d45f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="company">ARABIAN DENA CONTRACTING EST.</div>
            <div class="title">RENTAL MATERIALS REQUEST LIST</div>
        </div>

        <table class="meta">
            <tr>
                <td class="label">DOC.NO:</td>
                <td class="value">{{ $Order->order_number }}</td>
                <td class="label">DATE</td>
                <td class="value">{{ $Order->created_at ? $Order->created_at->format('d F Y') : '' }}</td>
            </tr>
            <tr>
                <td class="label">CLIENT:</td>
                <td class="value">{{ $Order->Company->name }}</td>
                <td class="label">REQUESTOR</td>
                <td class="value">{{ $requesterName ?? '' }}</td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 8%;">S.NO</th>
                    <th style="width: 52%;">PRODUCT NAME</th>
                    <th style="width: 12%;">REQ QTY</th>
                    <th style="width: 12%;">AVAIL QTY</th>
                    <th style="width: 16%;">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="num">{{ $row['no'] }}</td>
                        <td class="desc">{{ $row['product_name'] }}</td>
                        <td class="qty">{{ $row['requested_qty'] }}</td>
                        <td class="qty">{{ $row['available_qty'] }}</td>
                        <td>{{ $row['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="notes">
            <tr>
                <td class="label">Note:</td>
                <td></td>
            </tr>
        </table>
    </div>
</body>
</html>

