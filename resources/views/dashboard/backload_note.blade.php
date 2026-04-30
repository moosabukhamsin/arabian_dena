<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Backload Note - Backload #{{ $Backload->id }}</title>
    <style>
        @page { margin: 16px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }
        .sheet { width: 100%; }
        .top { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .top td { vertical-align: top; }
        .brand {
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 2px;
        }
        .brand small { display: block; font-size: 9px; letter-spacing: 0; font-weight: normal; }
        .right-meta { text-align: right; font-size: 9px; line-height: 1.35; }

        .title { text-align: center; font-weight: bold; margin: 6px 0 8px; }

        .box { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .box td, .box th { border: 1px solid #333; padding: 4px 6px; }
        .box th { background: #f4d45f; text-align: left; font-weight: bold; width: 16%; }

        .items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items th, .items td { border: 1px solid #333; padding: 5px 6px; }
        .items th { background: #f4d45f; text-align: center; }
        .items td.num { width: 6%; text-align: center; }
        .items td.qty { width: 10%; text-align: center; }
        .items td.desc { width: 54%; }
        .items td.track { width: 30%; }

        .footnote { font-size: 8px; margin-top: 6px; }

        .driver { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .driver td { border: 1px solid #333; padding: 5px 6px; }
        .driver .label { background: #f4d45f; font-weight: bold; width: 16%; }

        .sign { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .sign th, .sign td { border: 1px solid #333; padding: 6px; text-align: center; }
        .sign th { background: #f4d45f; font-weight: bold; }
        .sign .approve { color: #c00; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sheet">
        <table class="top">
            <tr>
                <td style="width: 60%;">
                    <div class="brand">
                        ARABIAN DENA
                        <small>CONTRACTING EST.</small>
                    </div>
                    <div style="font-size:9px; font-style: italic;">SERVICE AND PERFECTION AT ITS BEST</div>
                </td>
                <td class="right-meta" style="width: 40%;">
                    <div>P. O Box No. 7969 - 5141, Dammam 32433, Kingdom of Saudi Arabia</div>
                    <div>Off Tel: 013 5804777,</div>
                    <div>Email : info@arabiandena.com</div>
                    <div>Website : www.arabiandena.com</div>
                </td>
            </tr>
        </table>

        <div class="title">BACKLOAD NOTE</div>

        <table class="box">
            <tr>
                <th>Received From</th>
                <td>{{ $Backload->Company->name ?? '' }}</td>
                <th>BLKD#</th>
                <td style="color:#c00; font-weight:bold;">{{ $Backload->backload_number ?? '' }}</td>
            </tr>
            <tr>
                <th>Contact #</th>
                <td>{{ $Backload->Company->mobile_number ?? '' }}</td>
                <th>Site</th>
                <td>{{ $siteText ?? '' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $Backload->Company->email ?? '' }}</td>
                <th>P.O. Ref #</th>
                <td></td>
            </tr>
            <tr>
                <th>Client Code</th>
                <td>{{ $clientCode ?? '' }}</td>
                <th>Address</th>
                <td>{{ $Backload->address ?? '' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $Backload->created_at ? $Backload->created_at->format('d F Y') : '' }}</td>
                <th></th>
                <td></td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Description</th>
                    <th>Qty/Unit</th>
                    <th>Tracking #</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach ($rows as $row)
                    @php $totalQty += (int) ($row['returned_qty'] ?? 0); @endphp
                    <tr>
                        <td class="num">{{ $row['no'] }}</td>
                        <td class="desc">{{ $row['product_name'] }}</td>
                        <td class="qty">{{ $row['returned_qty'] }}</td>
                        <td class="track">
                            @php $series = $row['series'] ?? []; @endphp
                            @if (count($series))
                                {{ implode(', ', $series) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="num"></td>
                    <td class="desc" style="text-align:center; font-weight:bold;">TOTAL</td>
                    <td class="qty" style="color:#c00; font-weight:bold;">{{ $totalQty }}</td>
                    <td class="track"></td>
                </tr>
            </tbody>
        </table>

        <div class="footnote">
            *Please check the items carefully and do not hesitate to contact us for any queries you may have.
        </div>

        <table class="driver">
            <tr>
                <td class="label">Driver Name</td>
                <td>{{ $Backload->driver_name ?? '' }}</td>
                <td class="label">Driver Contact #</td>
                <td>{{ $Backload->driver_mobile ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Vehicle #</td>
                <td>{{ $Backload->truck_number ?? '' }}</td>
                <td class="label">Remarks for Driver</td>
                <td></td>
            </tr>
        </table>

        <table class="sign">
            <tr>
                <th>ADCE Received By</th>
                <th>Authorised By</th>
                <th>CLIENT Received By</th>
            </tr>
            <tr>
                <td style="height: 42px;"></td>
                <td style="height: 42px;"></td>
                <td style="height: 42px;"></td>
            </tr>
            <tr>
                <td colspan="3" class="approve">Electronically Approved</td>
            </tr>
        </table>
    </div>
</body>
</html>

