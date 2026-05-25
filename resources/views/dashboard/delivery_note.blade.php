<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note - {{ $Order->order_number ?? ('Order #' . $Order->id) }}</title>
    <style>
        @page { margin: 16px; }
        body { font-family: Calibri, Carlito, DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .sheet { width: 100%; }
        .top { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .top td { vertical-align: top; }
        .pdf-logo { max-height: 58px; max-width: 240px; height: auto; display: block; }
        .right-meta { text-align: right; font-size: 9px; line-height: 1.35; }

        .box { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .box td, .box th { border: 1px solid #333; padding: 4px 6px; }
        .box th { background: #f4d45f; text-align: left; font-weight: bold; width: 16%; }

        .title { text-align: center; font-weight: bold; margin: 6px 0 8px; }

        .items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items th, .items td { border: 1px solid #333; padding: 5px 6px; }
        .items th { background: #f4d45f; text-align: center; }
        .items td.num { width: 6%; text-align: center; }
        .items td.qty { width: 8%; text-align: center; }
        .items td.desc { width: 36%; }
        .items td.track { width: 22%; }
        .items td.remarks { width: 28%; }

        .footnote { font-size: 8px; margin-top: 6px; }

        .driver { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .driver td { border: 1px solid #333; padding: 5px 6px; font-size: 10px; }
        .driver .label { background: #f4d45f; font-weight: bold; width: 16%; }

        .sign { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .sign th, .sign td { border: 1px solid #333; padding: 5px 6px; text-align: center; font-size: 10px; }
        .sign th { background: #f4d45f; font-weight: bold; }

        .pdf-iso-logos { text-align: center; margin-top: 12px; width: 100%; }
        .pdf-iso-logos-img { max-width: 260px; width: 55%; height: auto; }
    </style>
</head>
<body>
    <div class="sheet">
        <table class="top">
            <tr>
                <td style="width: 60%;">
                    @include('dashboard.partials.pdf_logo')
                </td>
                <td class="right-meta" style="width: 40%;">
                    <div>P. O Box No. 7969 - 5141, Dammam 32433, Kingdom of Saudi Arabia</div>
                    <div>Off Tel: 013 5804777,</div>
                    <div>Email : info@arabiandena.com</div>
                    <div>Website : www.arabiandena.com</div>
                </td>
            </tr>
        </table>

        <div class="title">DELIVERY NOTE</div>

        <table class="box">
            <tr>
                <th>Delivered To</th>
                <td>{{ $Order->Company->name ?? '' }}</td>
                <th>Order Request Number</th>
                <td>{{ $Order->order_number ?? '' }}</td>
            </tr>
            <tr>
                <th>Contact</th>
                <td>{{ $Order->Company->mobile_number ?? '' }}</td>
                <th>Site</th>
                <td>{{ $Order->site_code ?? '' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $Order->Company->email ?? '' }}</td>
                <th>P.O. Ref #</th>
                <td>{{ $Order->po_reference ? basename($Order->po_reference) : '' }}</td>
            </tr>
            <tr>
                <th>Client Code</th>
                <td>{{ $clientCode ?? '' }}</td>
                <th>Address</th>
                <td>{{ $Order->address ?? '' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $Order->created_at ? $Order->created_at->format('d F Y') : '' }}</td>
                <th>PO Number</th>
                <td>{{ $Order->po_number ?? '' }}</td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Description</th>
                    <th>Qty/Unit</th>
                    <th>Tracking #</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach ($rows as $row)
                    @php $totalQty += (int) ($row['requested_qty'] ?? 0); @endphp
                    <tr>
                        <td class="num">{{ $row['no'] }}</td>
                        <td class="desc">{{ $row['product_name'] }}</td>
                        <td class="qty">{{ $row['requested_qty'] }}</td>
                        <td class="track">
                            @php $series = $row['series'] ?? []; @endphp
                            @if (count($series))
                                {{ implode(', ', $series) }}
                            @endif
                        </td>
                        <td class="remarks">{{ $row['remarks'] ?? '' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="num"></td>
                    <td class="desc" style="text-align:center; font-weight:bold;">TOTAL</td>
                    <td class="qty" style="color:#c00; font-weight:bold;">{{ $totalQty }}</td>
                    <td class="track"></td>
                    <td class="remarks"></td>
                </tr>
            </tbody>
        </table>

        <div class="footnote">
            *Please check the items carefully and do not hesitate to contact us for any queries you may have.
        </div>

        <table class="driver">
            <tr>
                <td class="label">Driver Name</td>
                <td>{{ $Order->driver_name ?? '' }}</td>
                <td class="label">Driver Contact #</td>
                <td>{{ $Order->driver_mobile ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Vehicle</td>
                <td colspan="3">{{ $Order->truck_number ?? '' }}</td>
            </tr>
        </table>

        <table class="sign">
            <tr>
                <th>Delivered By</th>
                <th>Authorised By</th>
                <th>Received By</th>
            </tr>
            <tr>
                <td style="height: 70px; vertical-align: middle;">{{ $currentUserName ?? '' }}</td>
                <td style="height: 70px; vertical-align: middle;">{{ $Order->authorizedBy?->name ?? '' }}</td>
                <td style="height: 70px;"></td>
            </tr>
        </table>

        @include('dashboard.partials.pdf_iso_logos')
    </div>
</body>
</html>
