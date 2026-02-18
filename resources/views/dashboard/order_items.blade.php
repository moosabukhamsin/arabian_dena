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
                                                <th class="border-bottom-0">Unit Price</th>
                                                <th class="border-bottom-0">Duration</th>
                                                <th class="border-bottom-0">Total</th>
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
                                                    <td>{{ number_format($orderItem->unit_price ?? 0, 2) }} SAR</td>
                                                    <td>{{ $orderItem->duration_days ?? 0 }} days</td>
                                                    <td>{{ number_format($orderItem->total_price ?? 0, 2) }} SAR</td>
                                                    <td class=" table_input">
                                                        <a href="{{ route('dashboard.delete_order_item', $orderItem->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
                                                            </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No order items found.</td>
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

    <script>
    </script>

@endsection
