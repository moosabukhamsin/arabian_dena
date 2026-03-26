@extends('dashboard.layout', ['page' => 'product-items'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Product Items</h1>
                    <div class="page-options">
                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#addProductItemModal">
                            Add Product Item
                        </button>
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

                <!-- ROW-1 -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Product Items</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Product</th>
                                                <th class="border-bottom-0">Series Number</th>
                                                <th class="border-bottom-0">Inspection Date</th>
                                                <th class="border-bottom-0">Due Days</th>
                                                <th class="border-bottom-0">Status</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($productItems as $productItem)
                                                <tr>
                                                    <td>{{ $productItem->id }}</td>
                                                    <td>
                                                        @if($productItem->product->image)
                                                            <img src="{{ $productItem->product->image_url }}" alt="Product Image" width="30" class="me-2">
                                                        @else
                                                            <div class="bg-light d-inline-block me-2" style="width: 30px; height: 30px; border-radius: 4px;"></div>
                                                        @endif
                                                        {{ $productItem->product->name }}
                                                    </td>
                                                    <td>{{ $productItem->series_number }}</td>
                                                    <td>{{ $productItem->inspection_date ?? '-' }}</td>
                                                    <td>
                                                        @if($productItem->inspection_date)
                                                            @php
                                                                $expiryDate = \Carbon\Carbon::parse($productItem->inspection_date)->addYear();
                                                                $dueDays = (int) floor(now()->floatDiffInDays($expiryDate, false));
                                                            @endphp
                                                            {{ max($dueDays, 0) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($productItem->status === 'In Stock')
                                                            <span class="badge bg-success">
                                                                <i data-feather="check-circle"></i> In Stock
                                                            </span>
                                                        @elseif($productItem->status === 'Under Rental')
                                                            <span class="badge bg-warning">
                                                                <i data-feather="clock"></i> Under Rental
                                                            </span>
                                                        @elseif($productItem->status === 'Backloaded')
                                                            <span class="badge bg-info">
                                                                <i data-feather="package"></i> Backloaded
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i data-feather="help-circle"></i> {{ ucfirst($productItem->status ?? 'Unknown') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('dashboard.product_item', $productItem->id) }}" class="btn btn-sm btn-info">
                                                            <i data-feather="eye"></i> View
                                                        </a>
                                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductItemModal{{ $productItem->id }}">
                                                            <i data-feather="edit"></i> Edit
                                                        </button>
                                                        <a href="{{ route('dashboard.delete_product_item', $productItem->id) }}"
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to delete this product item?')">
                                                            <i data-feather="trash-2"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No product items found.</td>
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

                <!-- Add Product Item Modal -->
                <div class="modal fade" id="addProductItemModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Product Item</h5>
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form id="addProductItemForm" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Product</label>
                                        <select name="product_id" class="form-control @if($errors->createProductItem->has('product_id')) is-invalid @endif" required>
                                            <option value="">Select a product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->createProductItem->has('product_id'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->createProductItem->first('product_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Series Number</label>
                                        <input type="text" name="series_number" class="form-control @if($errors->createProductItem->has('series_number')) is-invalid @endif" placeholder="Enter series number" value="{{ old('series_number') }}" required>
                                        @if($errors->createProductItem->has('series_number'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->createProductItem->first('series_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Inspection Date</label>
                                        <input type="date" name="inspection_date" class="form-control @if($errors->createProductItem->has('inspection_date')) is-invalid @endif" value="{{ old('inspection_date') }}">
                                        @if($errors->createProductItem->has('inspection_date'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->createProductItem->first('inspection_date') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Certificate</label>
                                        <input type="file" name="certificate" class="form-control @if($errors->createProductItem->has('certificate')) is-invalid @endif">
                                        @if($errors->createProductItem->has('certificate'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->createProductItem->first('certificate') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Add Product Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Item Modals -->
                @foreach ($productItems as $productItem)
                <div class="modal fade" id="editProductItemModal{{ $productItem->id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Product Item #{{ $productItem->id }}</h5>
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form action="{{ route('dashboard.update_product_item', $productItem->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="editing_product_item_id" value="{{ $productItem->id }}">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Series Number</label>
                                        <input type="text" name="series_number" class="form-control @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('series_number')) is-invalid @endif" value="{{ old('editing_product_item_id') == $productItem->id ? old('series_number') : $productItem->series_number }}" required>
                                        @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('series_number'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->updateProductItem->first('series_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control" required>
                                            <option value="In Stock" {{ $productItem->status === 'In Stock' ? 'selected' : '' }}>In Stock</option>
                                            <option value="Under Rental" {{ $productItem->status === 'Under Rental' ? 'selected' : '' }}>Under Rental</option>
                                            <option value="Backloaded" {{ $productItem->status === 'Backloaded' ? 'selected' : '' }}>Backloaded</option>
                                        </select>
                                        <small class="form-text text-muted">Status is automatically updated based on rental activity. Manual changes may be overridden.</small>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Inspection Date</label>
                                        <input type="date" name="inspection_date" class="form-control @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('inspection_date')) is-invalid @endif" value="{{ old('editing_product_item_id') == $productItem->id ? old('inspection_date') : $productItem->inspection_date }}">
                                        @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('inspection_date'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->updateProductItem->first('inspection_date') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Certificate</label>
                                        <input type="file" name="certificate" class="form-control @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('certificate')) is-invalid @endif">
                                        @if($productItem->certificate)
                                            <small class="form-text text-muted">
                                                Current: <a href="{{ URL('storage/' . $productItem->certificate) }}" target="_blank">View certificate</a>
                                            </small>
                                        @endif
                                        @if(old('editing_product_item_id') == $productItem->id && $errors->updateProductItem->has('certificate'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->updateProductItem->first('certificate') }}</strong>
                                            </span>
                                        @endif
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

            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
    <!--app-content close-->

    <script>
        // Handle add product item form submission as a normal POST
        document.getElementById('addProductItemForm').addEventListener('submit', function(e) {
            const productId = this.querySelector('[name="product_id"]').value;
            if (!productId) {
                e.preventDefault();
                return;
            }

            this.action = `/product/${productId}/create_product_item`;
            this.method = 'POST';
        });

        // Reopen relevant modal after validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->createProductItem->any())
                const addModalEl = document.getElementById('addProductItemModal');
                if (addModalEl) {
                    new bootstrap.Modal(addModalEl).show();
                }
            @endif

            @if ($errors->updateProductItem->any() && old('editing_product_item_id'))
                const editModalEl = document.getElementById('editProductItemModal{{ old('editing_product_item_id') }}');
                if (editModalEl) {
                    new bootstrap.Modal(editModalEl).show();
                }
            @endif
        });
    </script>

@endsection
