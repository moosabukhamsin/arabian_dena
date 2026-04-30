@extends('dashboard.layout', ['page' => 'order-items'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Order Items</h1>
                    <div class="page-options">
                        <button type="button" id="download-time-sheet-all" class="btn btn-primary">
                            <i class="fe fe-download"></i> Time Sheet
                        </button>
                        <a href="{{ route('dashboard.time_sheet_all_pdf') }}" class="btn btn-danger ms-2">
                            <i class="fe fe-download"></i> Time Sheet (PDF)
                        </a>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Order Items</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product Name</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Start Date</th>
                                                <th class="border-bottom-0">End Date</th>
                                                <th class="border-bottom-0">Duration</th>
                                                <th class="border-bottom-0">Remarks</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($orderItems as $orderItem)
                                                <tr>
                                                    <td>{{ $orderItem->id }} - @if($orderItem->ProductItem->Product->image_url)<img src="{{ $orderItem->ProductItem->Product->image_url }}" alt="Product Image" width="25">@endif</td>
                                                    <td>{{ $orderItem->ProductItem->product->name }}</td>
                                                    <td>{{ $orderItem->ProductItem->series_number }}</td>
                                                    <td>{{ $orderItem->Order->delivery_date }}</td>
                                                    <td>
                                                        @php
                                                            $backloadItem = \App\Models\BackloadItem::where('order_item_id', $orderItem->id)->first();
                                                        @endphp
                                                        @if($backloadItem)
                                                            {{ $backloadItem->Backload->date }}
                                                        @else
                                                            Active
                                                        @endif
                                                    </td>
                                                    <td>{{ $orderItem->duration_days ?? 0 }} days</td>
                                                    <td>{{ $orderItem->remarks ?? '' }}</td>
                                                    <td class=" table_input">
                                                        <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editOrderItemModal{{ $orderItem->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.delete_order_item', $orderItem->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
                                                            </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No order items found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row -->


                <!-- Edit Order Item Modals -->
                @foreach ($orderItems as $orderItem)
                <div class="modal fade" id="editOrderItemModal{{ $orderItem->id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Order Item #{{ $orderItem->id }}</h5>
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form action="{{ route('dashboard.update_order_item', $orderItem->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control" required>
                                            <option value="pending" {{ $orderItem->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="active" {{ $orderItem->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="completed" {{ $orderItem->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Remarks</label>
                                        <input type="text" name="remarks" class="form-control" value="{{ $orderItem->remarks }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Update Order Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
    <!--app-content close-->

    <div style="position:absolute; left:-10000px; top:0; width:1px; height:1px; overflow:hidden;">
        <table id="timesheet-all-datatable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Company</th>
                    <th>Site</th>
                    <th>S.No.</th>
                    <th>Description</th>
                    <th>Remarks</th>
                    <th>Tracking Number</th>
                    <th>Invoice Number</th>
                    <th>P.O. Reference</th>
                    <th>Delivery Note</th>
                    <th>Delivery Date</th>
                    <th>Delivery Time</th>
                    <th>Backload Note</th>
                    <th>Backload Date</th>
                    <th>Time BLKD</th>
                    <th>Rental Status</th>
                    <th>Rental Period</th>
                    <th>Unit Rental Cost SAR</th>
                    <th>Total Rental Cost SAR</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRental = 0; @endphp
                @foreach (($timesheetAllRows ?? []) as $row)
                    @php $totalRental += (float) ($row['total_rental_cost'] !== '' ? $row['total_rental_cost'] : 0); @endphp
                    <tr>
                        <td>{{ $row['order_number'] }}</td>
                        <td>{{ $row['company_name'] }}</td>
                        <td>{{ $row['site'] }}</td>
                        <td>{{ $row['sno'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td>{{ $row['remarks'] }}</td>
                        <td>{{ $row['tracking_number'] }}</td>
                        <td>{{ $row['invoice_number'] }}</td>
                        <td>{{ $row['po_reference'] }}</td>
                        <td>{{ $row['delivery_note'] }}</td>
                        <td>{{ $row['delivery_date'] }}</td>
                        <td>{{ $row['delivery_time'] }}</td>
                        <td>{{ $row['backload_note'] }}</td>
                        <td>{{ $row['backload_date'] }}</td>
                        <td>{{ $row['time_blkd'] }}</td>
                        <td>{{ $row['rental_status'] }}</td>
                        <td>{{ $row['rental_period'] }}</td>
                        <td>{{ $row['unit_rental_cost'] !== '' ? ('SAR ' . $row['unit_rental_cost']) : '' }}</td>
                        <td>{{ $row['total_rental_cost'] !== '' ? ('SAR ' . $row['total_rental_cost']) : '' }}</td>
                    </tr>
                @endforeach
                <tr>
                    @for ($c = 0; $c < 17; $c++)
                        <td></td>
                    @endfor
                    <td style="text-align: right; font-weight: bold;">Total Rental</td>
                    <td style="font-weight: bold;">{{ 'SAR ' . $totalRental }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) return;

            var $ = window.jQuery;
            var tsTable = null;

            function initTimeSheetTable() {
                try {
                    if ($.fn.dataTable && $.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable('#timesheet-all-datatable')) {
                        $('#timesheet-all-datatable').DataTable().destroy();
                    }
                } catch (e) {}

                tsTable = $('#timesheet-all-datatable').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    buttons: [{
                        extend: 'excelHtml5',
                        title: 'Time Sheet - All Order Items',
                        customize: function (xlsx) {
                            try {
                                var $styles = $(xlsx.xl['styles.xml']);

                                // ------- fonts -------
                                var $fonts = $styles.find('fonts');
                                var fontCount = parseInt($fonts.attr('count') || '0', 10);
                                $fonts.append('<font><b/><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>');
                                $fonts.attr('count', fontCount + 1);
                                var boldFontId = fontCount;

                                fontCount = parseInt($fonts.attr('count') || '0', 10);
                                $fonts.append('<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>');
                                $fonts.attr('count', fontCount + 1);
                                var boldWhiteFontId = fontCount;

                                // ------- fills -------
                                var $fills = $styles.find('fills');
                                var fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFF4D45F"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var headerFillId = fillCount;

                                fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FF00B050"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var greenFillId = fillCount;

                                fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFC000"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var orangeFillId = fillCount;

                                fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFC00000"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var redFillId = fillCount;

                                fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var whiteFillId = fillCount;

                                // ------- cellXfs (no borders) -------
                                var $cellXfs = $styles.find('cellXfs');
                                function addXf(fontId, fillId) {
                                    var id = parseInt($cellXfs.attr('count') || '0', 10);
                                    $cellXfs.append('<xf xfId="0" fontId="' + fontId + '" fillId="' + fillId + '" borderId="0" numFmtId="0" applyFont="1" applyFill="1"/>');
                                    $cellXfs.attr('count', id + 1);
                                    return id;
                                }

                                var titleRowStyleId = addXf(0, whiteFillId);
                                var headerStyleId = addXf(boldFontId, headerFillId);
                                var underRentalStyleId = addXf(boldWhiteFontId, greenFillId);
                                var returnedStyleId = addXf(boldWhiteFontId, orangeFillId);
                                var totalAmountStyleId = addXf(boldWhiteFontId, redFillId);

                                var $sheet = $(xlsx.xl.worksheets['sheet1.xml']);
                                $sheet.find('row[r="1"] c').attr('s', titleRowStyleId);
                                $sheet.find('row[r="2"] c').attr('s', headerStyleId);

                                var lastRow = 1;
                                $sheet.find('sheetData row').each(function () {
                                    var rr = parseInt($(this).attr('r'), 10);
                                    if (rr > lastRow) lastRow = rr;
                                });

                                var $shared = xlsx.xl['sharedStrings.xml'] ? $(xlsx.xl['sharedStrings.xml']) : null;
                                function getCellText($c) {
                                    var t = $c.attr('t');
                                    if (t === 'inlineStr') return $c.find('is t').text();
                                    if (t === 's' && $shared) {
                                        var idx = parseInt($c.find('v').text(), 10);
                                        if (Number.isFinite(idx)) return $shared.find('si').eq(idx).find('t').text();
                                    }
                                    return $c.find('v').text();
                                }

                                function colFromRef(ref) {
                                    return (ref || '').replace(/[0-9]/g, '');
                                }

                                function findColByHeader(headerText) {
                                    var target = (headerText || '').toString().trim().toUpperCase();
                                    var found = null;
                                    $sheet.find('row[r="2"] c').each(function () {
                                        var $c = $(this);
                                        var txt = (getCellText($c) || '').toString().trim().toUpperCase();
                                        if (txt === target) {
                                            found = colFromRef($c.attr('r'));
                                            return false;
                                        }
                                    });
                                    return found;
                                }

                                var statusCol = findColByHeader('Rental Status');
                                var totalCostCol = findColByHeader('Total Rental Cost SAR');

                                if (statusCol) {
                                    for (var r = 3; r <= lastRow; r++) {
                                        var $c = $sheet.find('row[r="' + r + '"] c[r^="' + statusCol + '"]');
                                        if (!$c.length) continue;
                                        var text = (getCellText($c) || '').toString().trim().toUpperCase();
                                        if (text === 'UNDER RENTAL') $c.attr('s', underRentalStyleId);
                                        if (text === 'RETURNED') $c.attr('s', returnedStyleId);
                                    }
                                }

                                if (totalCostCol) {
                                    var totalAmountCellRef = totalCostCol + lastRow;
                                    var $totalCell = $sheet.find('c[r="' + totalAmountCellRef + '"]');
                                    if ($totalCell.length) $totalCell.attr('s', totalAmountStyleId);
                                }
                            } catch (e) {
                                if (window.console && console.error) console.error('Time Sheet Excel styling failed', e);
                            }
                        }
                    }]
                });

                return tsTable;
            }

            $('#download-time-sheet-all').off('click').on('click', function () {
                try {
                    if (!$.fn.dataTable || !$.fn.dataTable.Buttons) {
                        console.error('Time Sheet Excel export failed: DataTables Buttons not loaded');
                        return;
                    }

                    var t = initTimeSheetTable();
                    if (!t || !t.buttons || typeof t.buttons !== 'function') {
                        console.error('Time Sheet Excel export failed: Buttons API not available on table');
                        return;
                    }
                    t.button(0).trigger();
                } catch (e) {
                    if (window.console && console.error) console.error('Time Sheet Excel export failed', e);
                }
            });
        });
    </script>

@endsection
