@extends('dashboard.layout', ['page' => 'product_item'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Product Item</h1>
                </div>
                <!-- PAGE-HEADER END -->
                <!-- ROW-2 -->
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Certificates</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#largemodal">
                                            Create Certificate
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

                                                <th class="border-bottom-0">Start Date</th>
                                                <th class="border-bottom-0">End Date</th>

                                                <th class="border-bottom-0">Status</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ProductItem->ProductItemCertifications as $certificate)
                                                <tr>
                                                    <td>{{ $certificate->id }} </td>

                                                    <td>{{ $certificate->start_date }}</td>
                                                    <td>{{ $certificate->end_date }}</td>
                                                    <td>to be done</td>
                                                    <td>
                                                        <a href="{{ URL('storage/' . $certificate->file) }}" >
                                                            <button id="bEdit" type="button" class="btn btn-sm btn-primary">
                                                                <span class="fe fe-eye"> </span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_certification', $certificate) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
                                                            </button>
                                                        </a>
                                                    </td>
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
                    <h5 class="modal-title">Add Certification</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.store_certification',$ProductItem) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">start date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">end date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">file</label>
                            <input type="file" name="file" class="form-control" required>
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
@endsection
