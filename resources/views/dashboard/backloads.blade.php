@extends('dashboard.layout', ['page' => 'backloads'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Back Loads</h1>
                </div>
                <!-- PAGE-HEADER END -->
                <!-- ROW-2 -->
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Back Loads</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="file-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Company</th>
                                                <th class="border-bottom-0">Date</th>
                                                <th class="border-bottom-0">Item Count</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($backloads as $backload)
                                                <tr>
                                                    <td>{{ $backload->id }}</td>
                                                    <td>{{ $backload->Company->name }}</td>
                                                    <td>{{ $backload->date }}</td>
                                                    <td>{{ $backload->BackloadItems->count() }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editBackloadModal{{ $backload->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.backload', $backload->id) }}" class="btn btn-sm btn-info me-1">
                                                            <span class="fe fe-eye"></span>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_backload', $backload->id) }}" class="btn btn-sm btn-danger">
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

    <!-- Edit Back Load Modals -->
    @foreach ($backloads as $backload)
    <div class="modal fade" id="editBackloadModal{{ $backload->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Back Load - {{ $backload->Company->name }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_backload', $backload->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <select name="company_id" class="form-control" required>
                                @foreach (\App\Models\Company::where('is_active', 1)->get() as $company)
                                    <option value="{{ $company->id }}" {{ $backload->company_id == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $backload->date }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Back Load Note</label>
                            <input type="file" name="back_load_note" class="form-control">
                            @if($backload->back_load_note)
                                <small class="text-muted">Current: <a href="{{ asset('storage/'.$backload->back_load_note) }}" target="_blank">View File</a></small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Truck Number</label>
                            <input type="text" name="truck_number" class="form-control" value="{{ $backload->truck_number }}" placeholder="Enter truck number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" value="{{ $backload->driver_name }}" placeholder="Enter driver name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Mobile</label>
                            <input type="text" name="driver_mobile" class="form-control" value="{{ $backload->driver_mobile }}" placeholder="Enter driver mobile number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver ID Number</label>
                            <input type="text" name="driver_id_number" class="form-control" value="{{ $backload->driver_id_number }}" placeholder="Enter driver ID number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                            @if($backload->attachment)
                                <small class="text-muted">Current: <a href="{{ asset('storage/'.$backload->attachment) }}" target="_blank">View File</a></small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Back Load</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <script>
    </script>

@endsection
