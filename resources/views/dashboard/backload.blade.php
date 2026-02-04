@extends('dashboard.layout', ['page' => 'backload'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Backload Items</h1>
                </div>
                <!-- PAGE-HEADER END -->
                <!-- ROW-2 -->
                <!-- Row -->
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
                                                <th class="border-bottom-0">Daily Price</th>
                                                <th class="border-bottom-0">Weekly Price</th>
                                                <th class="border-bottom-0">Monthly Price</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($OrderItems as $OrderItem)
                                                <tr>
                                                    <td>{{ $OrderItem->id }} - <img src="{{ asset("storage/".$OrderItem->ProductItem->Product->image) }}" alt="Product Image" width="25"></td>
                                                    <td>{{ $OrderItem->ProductItem->product->name }}</td>
                                                    <td>{{ $OrderItem->ProductItem->series_number }}</td>
                                                    <td>{{ $OrderItem->Order->delivery_date }}</td>
                                                    <td>{{ $OrderItem->end_date }}</td>
                                                    <td>{{ $OrderItem->daily_price }}</td>
                                                    <td>{{ $OrderItem->weekly_price }}</td>
                                                    <td>{{ $OrderItem->monthly_price }}</td>
                                                    <td >



                                                            <form action="{{ route('dashboard.store_backload_item', $Backload) }}" method="POST">
                                                                @csrf
                                                                <input type="text" name="order_item_id" value="{{ $OrderItem->id }}" hidden>
                                                                <button class="btn btn-sm btn-primary">
                                                                    <span class="fe fe-plus"> </span>
                                                                </button>
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
                                <h3 class="card-title">Backloads</h3>

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
                                                <th class="border-bottom-0">Daily Price</th>
                                                <th class="border-bottom-0">Weekly Price</th>
                                                <th class="border-bottom-0">Monthly Price</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Backload->BackloadItems as $BackloadItem)
                                                <tr>
                                                    <td>{{ $BackloadItem->OrderItem->id }} - <img src="{{ asset("storage/".$BackloadItem->OrderItem->ProductItem->Product->image) }}" alt="Product Image" width="25"></td>
                                                    <td>{{ $BackloadItem->OrderItem->ProductItem->product->name }}</td>
                                                    <td>{{ $BackloadItem->OrderItem->ProductItem->series_number }}</td>
                                                    <td>{{ $BackloadItem->OrderItem->Order->delivery_date }}</td>
                                                    <td>{{ $BackloadItem->Backload->date }}</td>
                                                    <td>{{ $BackloadItem->OrderItem->daily_price }}</td>
                                                    <td>{{ $BackloadItem->OrderItem->weekly_price }}</td>
                                                    <td>{{ $BackloadItem->OrderItem->monthly_price }}</td>
                                                    <td>


                                                        <a href="{{ route('dashboard.delete_backload_item', $BackloadItem->id) }}" >
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
