@extends('dashboard.layout', ['page' => 'product_items'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Product Items</h1>
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
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#largemodal">
                                            Create Product Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Status</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Product->ProductItems->where('is_active', true) as $ProductItem)
                                                <tr>
                                                    <td>{{ $ProductItem->id }}</td>
                                                    <td>
                                                        @if($ProductItem->product->image)
                                                            <img src="{{ $ProductItem->product->image_url }}" alt="Product Image" width="30" class="me-2">
                                                        @else
                                                            <div class="bg-light d-inline-block me-2" style="width: 30px; height: 30px; border-radius: 4px;"></div>
                                                        @endif
                                                        {{ $ProductItem->product->name }}
                                                    </td>
                                                    <td>{{ $ProductItem->series_number }}</td>
                                                    <td>
                                                        @if($ProductItem->status === 'In Stock')
                                                            <span class="badge bg-success">
                                                                <i data-feather="check-circle"></i> In Stock
                                                            </span>
                                                        @elseif($ProductItem->status === 'Under Rental')
                                                            <span class="badge bg-warning">
                                                                <i data-feather="clock"></i> Under Rental
                                                            </span>
                                                        @elseif($ProductItem->status === 'Backloaded')
                                                            <span class="badge bg-info">
                                                                <i data-feather="package"></i> Backloaded
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i data-feather="help-circle"></i> {{ ucfirst($ProductItem->status ?? 'Unknown') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editProductItemModal{{ $ProductItem->id }}">
                                                            <span class="fe fe-edit"></span> Edit
                                                        </button>
                                                        <a href="{{ route('dashboard.product_item', $ProductItem) }}" class="btn btn-sm btn-info me-1">
                                                            <i data-feather="eye"></i> View
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_product_item', $ProductItem) }}"
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to delete this product item?')">
                                                            <i data-feather="trash-2"></i> Delete
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

    <!--app-content close-->
    <!-- Modal -->
    <div class="modal fade" id="largemodal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Product Item</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.store_product_item',$Product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">series number</label>
                            <input type="text" name="series_number" class="form-control" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Item Modals -->
    @foreach ($Product->ProductItems->where('is_active', true) as $ProductItem)
    <div class="modal fade" id="editProductItemModal{{ $ProductItem->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product Item #{{ $ProductItem->id }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_product_item', $ProductItem->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Series Number</label>
                            <input type="text" name="series_number" class="form-control" value="{{ $ProductItem->series_number }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="In Stock" {{ $ProductItem->status === 'In Stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="Under Rental" {{ $ProductItem->status === 'Under Rental' ? 'selected' : '' }}>Under Rental</option>
                                <option value="Backloaded" {{ $ProductItem->status === 'Backloaded' ? 'selected' : '' }}>Backloaded</option>
                            </select>
                            <small class="form-text text-muted">Status is automatically updated based on rental activity. Manual changes may be overridden.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Product Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
