@extends('dashboard.layout', ['page' => 'companies'])
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
                                <h3 class="card-title">Employees</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#employeemodal">
                                            Add Employee
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
                                                <th class="border-bottom-0">Email</th>
                                                <th class="border-bottom-0">Mobile Number</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Company->CompanyEmployees->where('is_active',true) as $employee)
                                                <tr>
                                                    <td>{{ $employee->id }}</td>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $employee->email }}</td>
                                                    <td>{{ $employee->mobile_number }}</td>
                                                    <td>
                                                        <a href="{{ route('dashboard.delete_employee', $employee->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
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
                <!-- End Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Orders</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#ordermodal">
                                            Add Order
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
                                                <th class="border-bottom-0">Company Name</th>
                                                <th class="border-bottom-0">Site Code</th>
                                                <th class="border-bottom-0">Item Count</th>
                                                <th class="border-bottom-0">Total</th>
                                                <th class="border-bottom-0">Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Company->Orders->where('is_active', true) as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->Company->name }}</td>
                                                    <td>{{ $order->site_code }}</td>
                                                    <td>{{ $order->OrderItems->count() }}</td>
                                                    <td>{{ number_format($order->total_amount ?? 0, 2) }} SAR</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editOrderModal{{ $order->id }}">
                                                            <span class="fe fe-edit"></span> Edit
                                                        </button>
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
                <!-- ROW-2 END -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Backloads</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#backloadmodal">
                                            Add Backload
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
                                                <th class="border-bottom-0">Company Name</th>
                                                <th class="border-bottom-0">Date</th>
                                                <th class="border-bottom-0">Item Count</th>
                                                <th class="border-bottom-0">Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Company->Backloads->where('is_active', true) as $Backload)
                                                <tr>
                                                    <td>{{ $Backload->id }}</td>
                                                    <td>{{ $Backload->Company->name }}</td>
                                                    <td>{{ $Backload->date }}</td>
                                                    <td>{{ $Backload->BackloadItems->count() }}</td>
                                                    <td>
                                                        <a href="{{ route('dashboard.backload', $Backload->id) }}" >
                                                            <button id="bEdit" type="button" class="btn btn-sm btn-primary">
                                                                <span class="fe fe-edit"> </span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_backload', $Backload->id) }}" >
                                                            <button id="bDel" type="button" class="btn  btn-sm btn-danger">
                                                                <span class="fe fe-trash-2"> </span>
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
            <!-- CONTAINER END -->
        </div>
    </div>
    <!--app-content close-->
    <!-- Modal -->
    <div class="modal fade" id="employeemodal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Employee</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.store_employee',$Company) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control">
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
    <div class="modal fade" id="ordermodal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Order</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
                </div>
                <form action="{{ route('dashboard.store_order',$Company) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        <div class="form-group">
                            <label class="form-label">Employee</label>
                            <select name="company_id" class="form-control" required>
                                <option disabled selected>Select Employee</option>
                                @foreach ($Company->CompanyEmployees->where('is_active',true) as $Employee)
                                    <option value="{{ $Employee->id }}">{{ $Employee->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Site Code</label>
                            <input type="text" name="site_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PO Reference</label>
                            <input type="file" name="po_reference" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Truck Number</label>
                            <input type="text" name="truck_number" class="form-control" placeholder="Enter truck number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" placeholder="Enter driver name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Mobile</label>
                            <input type="text" name="driver_mobile" class="form-control" placeholder="Enter driver mobile number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver ID Number</label>
                            <input type="text" name="driver_id_number" class="form-control" placeholder="Enter driver ID number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
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
    <div class="modal fade" id="backloadmodal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Backload</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
                </div>
                <form action="{{ route('dashboard.store_backload',$Company) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">


                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Back Load Note</label>
                            <input type="file" name="back_load_note" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Truck Number</label>
                            <input type="text" name="truck_number" class="form-control" placeholder="Enter truck number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" placeholder="Enter driver name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Mobile</label>
                            <input type="text" name="driver_mobile" class="form-control" placeholder="Enter driver mobile number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver ID Number</label>
                            <input type="text" name="driver_id_number" class="form-control" placeholder="Enter driver ID number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
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

    <!-- Edit Order Modals -->
    @foreach ($Company->Orders->where('is_active', true) as $order)
    <div class="modal fade" id="editOrderModal{{ $order->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order - {{ $order->site_code }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_order', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Employee</label>
                            <select name="company_employe_id" class="form-control" required>
                                @foreach ($Company->CompanyEmployees->where('is_active',true) as $Employee)
                                    <option value="{{ $Employee->id }}" {{ $order->company_employe_id == $Employee->id ? 'selected' : '' }}>
                                        {{ $Employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Code</label>
                            <input type="text" name="site_code" class="form-control" value="{{ $order->site_code }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ $order->delivery_date }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" required>{{ $order->address }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PO Reference</label>
                            <input type="file" name="po_reference" class="form-control">
                            @if($order->po_reference)
                                <small class="text-muted">Current: <a href="{{ asset('storage/'.$order->po_reference) }}" target="_blank">View File</a></small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Truck Number</label>
                            <input type="text" name="truck_number" class="form-control" value="{{ $order->truck_number }}" placeholder="Enter truck number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" value="{{ $order->driver_name }}" placeholder="Enter driver name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver Mobile</label>
                            <input type="text" name="driver_mobile" class="form-control" value="{{ $order->driver_mobile }}" placeholder="Enter driver mobile number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Driver ID Number</label>
                            <input type="text" name="driver_id_number" class="form-control" value="{{ $order->driver_id_number }}" placeholder="Enter driver ID number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                            @if($order->attachment)
                                <small class="text-muted">Current: <a href="{{ asset('storage/'.$order->attachment) }}" target="_blank">View File</a></small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
