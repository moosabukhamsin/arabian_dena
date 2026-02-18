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
                        <a href="{{ route('dashboard.delivery_note', $Order->id) }}" class="btn btn-success">
                            <i class="fe fe-download"></i> Delivery Note
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
                                                <th class="border-bottom-0">Unit Price</th>
                                                <th class="border-bottom-0">Duration</th>
                                                <th class="border-bottom-0">Total</th>
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
                                                    <td>{{ number_format($OrderItem->unit_price ?? 0, 2) }} SAR</td>
                                                    <td>{{ $OrderItem->duration_days ?? 0 }} days</td>
                                                    <td>{{ number_format($OrderItem->total_price ?? 0, 2) }} SAR</td>
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
                <!-- End Row -->
                <!-- ROW-2 END -->
            </div>
            <!-- CONTAINER END -->
        </div>
    </div>

@endsection
