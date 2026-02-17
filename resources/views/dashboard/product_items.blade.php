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
                                                    <td colspan="5" class="text-center">No product items found.</td>
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
                            <form id="addProductItemForm">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Product</label>
                                        <select name="product_id" class="form-control" required>
                                            <option value="">Select a product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Series Number</label>
                                        <input type="text" name="series_number" class="form-control" placeholder="Enter series number" required>
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
                            <form action="{{ route('dashboard.update_product_item', $productItem->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Series Number</label>
                                        <input type="text" name="series_number" class="form-control" value="{{ $productItem->series_number }}" required>
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
        // Handle add product item form submission
        document.getElementById('addProductItemForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const productId = formData.get('product_id');

            if (!productId) {
                alert('Please select a product.');
                return;
            }

            // Submit to the correct route
            this.action = `/product/${productId}/create_product_item`;
            this.method = 'POST';

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            formData.append('_token', csrfToken);

            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error adding product item.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product item.');
            });
        });
    </script>

@endsection
