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
                                                <th class="border-bottom-0">Rental Status</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($productItems as $productItem)
                                                <tr>
                                                    <td>{{ $productItem->id }}</td>
                                                    <td>
                                                        @if($productItem->product->image)
                                                            <img src="{{ asset('storage/'.$productItem->product->image) }}" alt="Product Image" width="30" class="me-2">
                                                        @else
                                                            <div class="bg-light d-inline-block me-2" style="width: 30px; height: 30px; border-radius: 4px;"></div>
                                                        @endif
                                                        {{ $productItem->product->name }}
                                                    </td>
                                                    <td>{{ $productItem->series_number }}</td>
                                                    <td>
                                                        @if($productItem->rental_status === 'free')
                                                            <span class="badge bg-success">
                                                                <i data-feather="check-circle"></i> Free
                                                            </span>
                                                        @elseif($productItem->rental_status === 'on_rental')
                                                            <div>
                                                                <span class="badge bg-warning mb-1">
                                                                    <i data-feather="clock"></i> On Rental
                                                                </span>
                                                                @if($productItem->rental_details)
                                                                    <div class="small text-muted">
                                                                        <strong>Company:</strong> {{ $productItem->rental_details['company'] }}<br>
                                                                        <strong>Order:</strong> #{{ $productItem->rental_details['order_id'] }}<br>
                                                                        <strong>Delivery:</strong> {{ $productItem->rental_details['delivery_date'] }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i data-feather="help-circle"></i> Unknown
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('dashboard.product_item', $productItem->id) }}" class="btn btn-sm btn-info">
                                                            <i data-feather="eye"></i> View
                                                        </a>
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
