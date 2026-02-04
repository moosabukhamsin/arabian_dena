@extends('dashboard.layout', ['page' => 'products'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Dashboard 01</h1>

                </div>
                <!-- PAGE-HEADER END -->



                <!-- ROW-2 -->
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Orders</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Company Name</th>
                                                <th class="border-bottom-0">Site Code</th>
                                                <th class="border-bottom-0">Item Count</th>
                                                <th class="border-bottom-0">Total</th>
                                                <th class="border-bottom-0">Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($orders as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->Company->name }}</td>
                                                    <td>{{ $order->site_code }}</td>
                                                    <td>{{ $order->OrderItems->count() }}</td>
                                                    <td>${{ number_format($order->total_amount ?? 0, 2) }}</td>
                                                    <td>
                                                        <a href="{{ route('dashboard.order', $order->id) }}" >
                                                            <button id="bView" type="button" class="btn btn-sm btn-info me-1">
                                                                <span class="fe fe-eye"> </span> View
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_order', $order->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span> Delete
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
                </div>
                <!-- End Row -->
                <!-- ROW-2 END -->


            </div>
            <!-- CONTAINER END -->
        </div>
    </div>

    <!--app-content close-->

@endsection
