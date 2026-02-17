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
                                <h3 class="card-title">Companies</h3>
                                <div class="card-options">
                                    <div class="btn-group">
                                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="modal" data-bs-target="#largemodal">
                                            Create Company
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
                                                <th class="border-bottom-0">Pricing Type</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($companies as $company)
                                                <tr>
                                                    <td>{{ $company->id }} - @if($company->image_url)<img src="{{ $company->image_url }}" alt="Company Logo" width="50">@endif</td>
                                                    <td>{{ $company->name }}</td>
                                                    <td>{{ $company->email }}</td>
                                                    <td>{{ $company->mobile_number }}</td>
                                                    <td>
                                                        <span class="badge {{ $company->pricing_type === 'daily_monthly' ? 'bg-info' : 'bg-success' }}">
                                                            {{ $company->pricing_type === 'daily_monthly' ? 'Daily & Monthly' : 'Daily, Weekly & Monthly' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editCompanyModal{{ $company->id }}">
                                                            <span class="fe fe-edit"></span>
                                                        </button>
                                                        <a href="{{ route('dashboard.company', $company) }}" class="btn btn-sm btn-info me-1">
                                                            <span class="fe fe-eye"></span>
                                                        </a>
                                                        <a href="{{ route('dashboard.company_price_lists', $company) }}" class="btn btn-sm btn-success me-1">
                                                            <span class="fe fe-dollar-sign"></span>
                                                        </a>
                                                        <a href="{{ route('dashboard.delete_company', $company) }}" class="btn btn-sm btn-danger">
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
                <form action="{{ route('dashboard.store_company') }}" method="POST" enctype="multipart/form-data">
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
                        <div class="form-group">
                            <label class="form-label">Pricing Type</label>
                            <select name="pricing_type" class="form-control" required>
                                <option value="daily_monthly">Daily & Monthly Only</option>
                                <option value="daily_weekly_monthly" selected>Daily, Weekly & Monthly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Logo</label>
                            <input type="file" name="image" class="form-control">
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

    <!-- Edit Company Modals -->
    @foreach ($companies as $company)
    <div class="modal fade" id="editCompanyModal{{ $company->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Company - {{ $company->name }}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('dashboard.update_company', $company->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $company->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $company->email }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control" value="{{ $company->mobile_number }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pricing Type</label>
                            <select name="pricing_type" class="form-control" required>
                                <option value="daily_monthly" {{ $company->pricing_type === 'daily_monthly' ? 'selected' : '' }}>Daily & Monthly Only</option>
                                <option value="daily_weekly_monthly" {{ $company->pricing_type === 'daily_weekly_monthly' ? 'selected' : '' }}>Daily, Weekly & Monthly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Logo</label>
                            <input type="file" name="image" class="form-control">
                            @if($company->image_url)
                                <small class="text-muted">Current: <img src="{{ $company->image_url }}" alt="Current Logo" width="50"></small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

@endsection
