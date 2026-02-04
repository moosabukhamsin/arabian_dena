@extends('dashboard.layout', ['page' => 'categories'])
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
                                <h3 class="card-title">Categories</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#largemodal">
                                            Create Category
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
                                                <th class="border-bottom-0">Product Count</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categories as $category)
                                                <tr>
                                                    <td>{{ $category->id }} </td>
                                                    <td>{{ $category->name }}</td>
                                                    <td>{{ $category->Products->where('is_active', true)->count() }}</td>

                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.delete_category', $category) }}" class="btn btn-sm btn-danger">
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
                <form action="{{ route('dashboard.store_category') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control">
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

    <!-- Edit Category Modals -->
    @foreach ($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category - {{ $category->name }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_category', $category->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                            @if($category->image)
                                <small class="text-muted">Current: <img src="{{ asset('storage/'.$category->image) }}" alt="Current Image" width="50"></small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

@endsection

