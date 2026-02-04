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
                                <h3 class="card-title">Products</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#largemodal">
                                                Create Product
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
                                                <th class="border-bottom-0">Name</th>
                                                <th class="border-bottom-0">Category</th>
                                                <th class="border-bottom-0">Item Count</th>
                                                <th class="border-bottom-0">Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                <tr>
                                                    <td>{{ $product->id }} - <img src="{{ asset("storage/".$product->image) }}" alt="Product Image" width="25"> </td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->category->name }}</td>
                                                    <td>{{ $product->ProductItems->where('is_active', true)->count() }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.product', $product) }}" class="btn btn-sm btn-info me-1">
                                                            <span class="fe fe-eye"></span>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_product', $product) }}" class="btn btn-sm btn-danger">
                                                            <span class="fe fe-trash-2"></span>
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
    <!-- Modal -->
    <div class="modal fade" id="largemodal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
                </div>
                <form action="{{ route('dashboard.store_product') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                                <div class="form-group">
                                    <label class="form-label">name</label>
                                    <input type="text" name="name" class="form-control"  >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">image</label>
                                    <input type="file" name="image" class="form-control" >
                                <div class="form-group">
                                    <label class="form-label">default daily price</label>
                                    <input type="number" name="daily_price" class="form-control"  >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">default weekly price</label>
                                    <input type="number" name="weekly_price" class="form-control"  >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">default monthly price</label>
                                    <input type="number" name="monthly_price" class="form-control"  >
                                </div>
                                <div class="form-group">
                                    <label class="form-label">category</label>
                                    <select name="category_id" class="form-control" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputEmail1" class="form-label">description</label>
                                    <textarea name="description" rows="3" class="form-control"></textarea>
                                </div>
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

    <!-- Edit Product Modals -->
    @foreach ($products as $product)
    <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product - {{ $product->name }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_product', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                            @if($product->image)
                                <small class="text-muted">Current: <img src="{{ asset('storage/'.$product->image) }}" alt="Current Image" width="50"></small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Daily Price</label>
                            <input type="number" name="daily_price" class="form-control" value="{{ $product->daily_price }}" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Weekly Price</label>
                            <input type="number" name="weekly_price" class="form-control" value="{{ $product->weekly_price }}" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Monthly Price</label>
                            <input type="number" name="monthly_price" class="form-control" value="{{ $product->monthly_price }}" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ $product->description }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

@endsection
