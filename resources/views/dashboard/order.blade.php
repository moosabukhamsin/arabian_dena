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
                    </div>
                </div>
                <!-- PAGE-HEADER END -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @can('modify-inventory')
                <form id="add-combination-form" action="{{ route('dashboard.store_order_items', $Order) }}" method="POST">
                    @csrf
                </form>
                @endcan
                <!-- ROW-2 -->
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Product Items</h3>
                                <div class="card-options">
                                    @can('modify-inventory')
                                    <button type="submit" form="add-combination-form" class="btn btn-primary">add as combination</button>
                                    @endcan
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                @can('modify-inventory')
                                                <th class="border-bottom-0"></th>
                                                @endcan
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product Name</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Certificate</th>
                                                @can('modify-inventory')
                                                <th class="border-bottom-0">Action</th>
                                                @endcan

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ProductItems as $ProductItem)
                                                <tr>
                                                    @can('modify-inventory')
                                                    <td>
                                                        <input
                                                            type="checkbox"
                                                            form="add-combination-form"
                                                            name="product_item_ids[]"
                                                            value="{{ $ProductItem->id }}"
                                                        >
                                                    </td>
                                                    @endcan
                                                    <td>{{ $ProductItem->id }} - @if($ProductItem->Product->image_url)<img src="{{ $ProductItem->Product->image_url }}" alt="Product Image" width="25">@endif</td>
                                                    <td>{{ $ProductItem->product->name }}</td>
                                                    <td>{{ $ProductItem->series_number }}</td>
                                                    <td>
                                                        @php
                                                            $latestCert = $ProductItem->Certificates->first();
                                                        @endphp
                                                        @if($latestCert)
                                                            <a href="{{ URL('storage/' . $latestCert->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ route('dashboard.download_product_item_certificate_version', $latestCert) }}" class="btn btn-sm btn-primary">Download</a>
                                                        @elseif($ProductItem->certificate)
                                                            <a href="{{ URL('storage/' . $ProductItem->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ route('dashboard.download_product_item_certificate', $ProductItem) }}" class="btn btn-sm btn-primary">Download</a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    @can('modify-inventory')
                                                    <td class=" table_input">
                                                        <form action="{{ route('dashboard.store_order_item',['Order' => $Order,'ProductItem' => $ProductItem]) }}" method="POST" >
                                                            <input type="text" name="product_item_id" value="{{ $ProductItem->id }}" hidden>
                                                            @csrf
                                                            <input type="submit" class="btn btn-primary" value="Add"/>
                                                        </form>
                                                    </td>
                                                    @endcan

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
                                <div class="card-options">
                                    <a href="{{ route('dashboard.order_certificates_zip', $Order->id) }}" class="btn btn-primary">
                                        <i class="fe fe-download"></i> Certificate Download All (ZIP)
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product Name</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Certificate</th>
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
                                                    <td>
                                                        @php
                                                            $oiPi = $OrderItem->ProductItem;
                                                            $oiLatestCert = $oiPi->Certificates->first();
                                                        @endphp
                                                        @if($oiLatestCert)
                                                            <a href="{{ URL('storage/' . $oiLatestCert->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ route('dashboard.download_product_item_certificate_version', $oiLatestCert) }}" class="btn btn-sm btn-primary">Download</a>
                                                        @elseif($oiPi->certificate)
                                                            <a href="{{ URL('storage/' . $oiPi->certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                            <a href="{{ route('dashboard.download_product_item_certificate', $oiPi) }}" class="btn btn-sm btn-primary">Download</a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
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
                                                        @can('modify-inventory')
                                                        <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editOrderItemRemarksModal{{ $OrderItem->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        @can('delete-records')
                                                        <a href="{{ route('dashboard.delete_order_item', $OrderItem->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
                                                            </button>
                                                        </a>
                                                        @endcan
                                                        @endcan
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
                @can('modify-inventory')
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
                @endcan
                <!-- End Row -->
                <!-- ROW-2 END -->
            </div>
            <!-- CONTAINER END -->
        </div>
    </div>

@endsection
