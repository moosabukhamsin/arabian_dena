@extends('dashboard.layout', ['page' => 'users'])
@section('content')
    <!--app-content open-->
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <h1 class="page-title">Users</h1>
                </div>
                <!-- PAGE-HEADER END -->
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <!-- Row -->
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Users</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="users-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0">ID</th>
                                                <th class="border-bottom-0">Name</th>
                                                <th class="border-bottom-0">Email</th>
                                                <th class="border-bottom-0">Role</th>
                                                <th class="border-bottom-0">Registered</th>
                                                <th class="border-bottom-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($users as $user)
                                                @php
                                                    $currentRole = $user->role?->value ?? 'engineer';
                                                @endphp
                                                <tr>
                                                    <td>{{ $user->id }}</td>
                                                    <td>{{ $user->name ?? '—' }}</td>
                                                    <td>{{ $user->email ?? '—' }}</td>
                                                    <td>
                                                        <form action="{{ route('dashboard.update_user_role', $user) }}" method="POST" class="d-flex align-items-center gap-2 user-role-form">
                                                            @csrf
                                                            <select name="role" class="form-control form-control-sm user-role-select">
                                                                @foreach ($roles as $value => $label)
                                                                    <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                        </form>
                                                    </td>
                                                    <td data-order="{{ $user->created_at?->timestamp ?? 0 }}">
                                                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('dashboard.delete_user', $user) }}" class="btn btn-sm btn-danger">
                                                            <span class="fe fe-trash-2"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No users found.</td>
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
            </div>
            <!-- CONTAINER END -->
        </div>
    </div>
    <!--app-content close-->
@endsection

@push('scripts')
<script>
    $(function () {
        if (!$.fn.DataTable) {
            return;
        }

        $('#users-datatable').DataTable({
            language: {
                searchPlaceholder: 'Search...',
                sSearch: '',
            },
            columnDefs: [
                { orderable: false, targets: [3, 5] },
            ],
        });
    });
</script>
@endpush
