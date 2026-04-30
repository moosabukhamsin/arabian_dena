@extends('dashboard.layout', ['page' => 'product_items'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Order #{{ $Order->id }} - {{ $Order->Company->name }}</h1>
                    <div class="page-options">
                        <a href="{{ url()->previous() ?: route('dashboard.orders') }}" class="btn btn-primary me-2">
                            <i class="fe fe-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('dashboard.order_request', $Order->id) }}" class="btn btn-warning me-2">
                            <i class="fe fe-download"></i> Order Request
                        </a>
                        <a href="{{ route('dashboard.delivery_note', $Order->id) }}" class="btn btn-success">
                            <i class="fe fe-download"></i> Delivery Note
                        </a>
                        <button type="button" id="download-time-sheet" class="btn btn-primary ms-2">
                            <i class="fe fe-download"></i> Time Sheet
                        </button>
                        <a href="{{ route('dashboard.time_sheet_pdf', $Order->id) }}" class="btn btn-danger ms-2">
                            <i class="fe fe-download"></i> Time Sheet (PDF)
                        </a>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->
                <!-- ROW-2 -->
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Product Items</h3>
                                <div class="card-options">
                                    <button type="submit" class="btn btn-primary">add as combination</button>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0"></th>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product Name</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Action</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ProductItems as $ProductItem)
                                                <tr>
                                                    <td><input type="checkbox"></td>
                                                    <td>{{ $ProductItem->id }} - @if($ProductItem->Product->image_url)<img src="{{ $ProductItem->Product->image_url }}" alt="Product Image" width="25">@endif</td>
                                                    <td>{{ $ProductItem->product->name }}</td>
                                                    <td>{{ $ProductItem->series_number }}</td>
                                                    <td class=" table_input">
                                                        <form action="{{ route('dashboard.store_order_item',['Order' => $Order,'ProductItem' => $ProductItem]) }}" method="POST" >
                                                            <input type="text" name="product_item_id" value="{{ $ProductItem->id }}" hidden>
                                                            @csrf
                                                            {{-- <div class="form-group">
                                                                <label class="form-label">daily price</label>
                                                                <input type="text" name="daily_price" class="form-control" placeholder="daily price" value="{{$ProductItem->Product->daily_price}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label">weekly price</label>
                                                                <input type="text" name="weekly_price" class="form-control"  placeholder="weekly price" value="{{$ProductItem->Product->weekly_price}}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="form-label">monthly price</label>
                                                                <input type="text" name="monthly_price" class="form-control"  placeholder="monthly price" value="{{$ProductItem->Product->monthly_price}}">
                                                            </div> --}}


                                                            <input type="submit" class="btn btn-primary" value="Add"/>
                                                        </form>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Order Items</h3>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
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
                                            @foreach ($Order->OrderItems as $OrderItem)
                                                <tr>
                                                    <td>{{ $OrderItem->id }} - @if($OrderItem->ProductItem->Product->image_url)<img src="{{ $OrderItem->ProductItem->Product->image_url }}" alt="Product Image" width="25">@endif</td>
                                                    <td>{{ $OrderItem->ProductItem->product->name }}</td>
                                                    <td>{{ $OrderItem->ProductItem->series_number }}</td>
                                                    <td>{{ $OrderItem->Order->delivery_date }}</td>
                                                    <td>
                                                        @php
                                                            $backloadItem = \App\Models\BackloadItem::where('order_item_id', $OrderItem->id)->first();
                                                        @endphp
                                                        @if($backloadItem)
                                                            {{ $backloadItem->Backload->date }}
                                                        @else
                                                            Active
                                                        @endif
                                                    </td>
                                                    <td>{{ $OrderItem->duration_days ?? 0 }} days</td>
                                                    <td>{{ $OrderItem->remarks ?? '' }}</td>
                                                    <td class=" table_input">
                                                        {{-- @if (!$OrderItem->end_date )
                                                            <form action="{{ route('dashboard.update_order_item',$OrderItem) }}" method="POST" >
                                                                @csrf
                                                                <div class="form-group">
                                                                    <label class="form-label">set end date</label>
                                                                    <input type="date" name="end_date" class="form-control" required>
                                                                </div>


                                                                <input type="submit" class="btn btn-primary" value="submit"/>
                                                            </form>
                                                        @endif --}}

                                                        <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editOrderItemRemarksModal{{ $OrderItem->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.delete_order_item', $OrderItem->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
                                                            </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Order Item Remarks Modals -->
                @foreach ($Order->OrderItems as $OrderItem)
                    <div class="modal fade" id="editOrderItemRemarksModal{{ $OrderItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Order Item Remarks #{{ $OrderItem->id }}</h5>
                                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <form action="{{ route('dashboard.update_order_item', $OrderItem->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label class="form-label">Remarks</label>
                                            <input type="text" name="remarks" class="form-control" value="{{ $OrderItem->remarks }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                <!-- End Row -->
                <!-- ROW-2 END -->
            </div>
            <!-- CONTAINER END -->
        </div>
    </div>

    <div style="position:absolute; left:-10000px; top:0; width:1px; height:1px; overflow:hidden;">
        <table id="timesheet-datatable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Site</th>
                    <th>S.No.</th>
                    <th>Description</th>
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
                @foreach (($timesheetRows ?? []) as $row)
                    @php $totalRental += (float) ($row['total_rental_cost'] !== '' ? $row['total_rental_cost'] : 0); @endphp
                    <tr>
                        <td>{{ $row['site'] }}</td>
                        <td>{{ $row['sno'] }}</td>
                        <td>{{ $row['description'] }}</td>
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
                    @for ($c = 0; $c < 14; $c++)
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
                // Always (re)initialize cleanly to ensure Buttons are attached
                try {
                    if ($.fn.dataTable && $.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable('#timesheet-datatable')) {
                        $('#timesheet-datatable').DataTable().destroy();
                    }
                } catch (e) {
                    // ignore
                }

                tsTable = $('#timesheet-datatable').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    buttons: [{
                        extend: 'excelHtml5',
                        title: 'Time Sheet - {{ $Order->order_number ?? ('Order #' . $Order->id) }}',
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

                                // White fill for title row (row 1)
                                fillCount = parseInt($fills.attr('count') || '0', 10);
                                $fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>');
                                $fills.attr('count', fillCount + 1);
                                var whiteFillId = fillCount;

                                // ------- cellXfs (no red borders) -------
                                var $cellXfs = $styles.find('cellXfs');
                                function addXf(fontId, fillId) {
                                    var id = parseInt($cellXfs.attr('count') || '0', 10);
                                    $cellXfs.append('<xf xfId="0" fontId="' + fontId + '" fillId="' + fillId + '" borderId="0" numFmtId="0" applyFont="1" applyFill="1"/>');
                                    $cellXfs.attr('count', id + 1);
                                    return id;
                                }

                                // Row 1: white background (title). Row 2: gold header (column headings).
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

                                // Rental Status column N — data rows start after title (1) + header (2)
                                var statusCol = 'N';
                                for (var r = 3; r <= lastRow; r++) {
                                    var $c = $sheet.find('row[r="' + r + '"] c[r^="' + statusCol + '"]');
                                    if (!$c.length) continue;
                                    var text = (getCellText($c) || '').toString().trim().toUpperCase();
                                    if (text === 'UNDER RENTAL') $c.attr('s', underRentalStyleId);
                                    if (text === 'RETURNED') $c.attr('s', returnedStyleId);
                                }

                                // Total amount cell in last row, column Q
                                var totalAmountCellRef = 'Q' + lastRow;
                                var $totalCell = $sheet.find('c[r="' + totalAmountCellRef + '"]');
                                if ($totalCell.length) $totalCell.attr('s', totalAmountStyleId);
                            } catch (e) {
                                if (window.console && console.error) console.error('Time Sheet Excel styling failed', e);
                            }
                        }
                    }]
                });

                return tsTable;
            }

            $('#download-time-sheet').off('click').on('click', function () {
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
