@extends('dashboard.layout', ['page' => 'profile'])
@php
    $editing = $errors->any();
@endphp
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">My Profile</h1>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row row-sm">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Account Details</h3>
                                <div class="card-options">
                                    <button type="button"
                                            id="profile-edit-btn"
                                            class="btn btn-primary {{ $editing ? 'd-none' : '' }}">
                                        <span class="fe fe-edit me-1"></span> Edit
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Read-only view --}}
                                <div id="profile-view-panel" class="{{ $editing ? 'd-none' : '' }}">
                                    <div class="mb-4">
                                        <label class="form-label text-muted mb-1 small">Full Name</label>
                                        <p class="mb-0 fs-6">{{ $user->name ?? '—' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label text-muted mb-1 small">Email Address</label>
                                        <p class="mb-0 fs-6">{{ $user->email ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="form-label text-muted mb-1 small">Password</label>
                                        <p class="mb-0 text-muted">Hidden for security. Use <strong>Edit</strong> to change your password.</p>
                                    </div>
                                </div>

                                {{-- Edit form --}}
                                <div id="profile-edit-panel" class="{{ $editing ? '' : 'd-none' }}">
                                    <form action="{{ route('profile.update') }}" method="POST">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text"
                                                   name="name"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name', $user->name) }}"
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Email Address</label>
                                            <input type="email"
                                                   name="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   value="{{ old('email', $user->email) }}"
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <hr class="my-4">

                                        <h5 class="mb-3">Change Password</h5>
                                        <p class="text-muted small mb-3">Leave blank to keep your current password.</p>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password"
                                                   name="current_password"
                                                   class="form-control @error('current_password') is-invalid @enderror"
                                                   autocomplete="current-password"
                                                   placeholder="Required only when setting a new password">
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password"
                                                   name="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   autocomplete="new-password"
                                                   placeholder="Minimum 8 characters">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-4">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password"
                                                   name="password_confirmation"
                                                   class="form-control"
                                                   autocomplete="new-password"
                                                   placeholder="Repeat new password">
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                            <a href="{{ route('profile') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Pending Requests</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Your create and edit actions awaiting approval across categories, products, product items, orders, backloads, and companies.
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom mb-0">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Submitted</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingRequestRows as $row)
                                                <tr>
                                                    <td>{{ $row['type_label'] }}</td>
                                                    <td>{{ $row['entity_id'] }}</td>
                                                    <td>{{ $row['label'] }}</td>
                                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                                    <td>{{ $row['updated_at']?->format('d M Y H:i') ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">You have no pending requests.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Profile Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <span class="avatar avatar-xl brround bg-primary-transparent text-primary me-3">
                                        <i class="fe fe-user fs-24"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-1">{{ $user->name ?? '—' }}</h5>
                                        <span class="text-muted">{{ $user->email ?? '—' }}</span>
                                    </div>
                                </div>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Member since</span>
                                        <span>{{ $user->created_at?->format('d M Y') ?? '—' }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card border-danger">
                            <div class="card-header border-danger">
                                <h3 class="card-title text-danger">Delete Account</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Permanently delete your account. You will be signed out and cannot undo this action.</p>
                                <a href="{{ route('profile.delete') }}"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete your account? You will be signed out permanently.');">
                                    <span class="fe fe-trash-2"></span> Delete Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editBtn = document.getElementById('profile-edit-btn');
        var viewPanel = document.getElementById('profile-view-panel');
        var editPanel = document.getElementById('profile-edit-panel');
        if (!editBtn || !viewPanel || !editPanel) return;

        editBtn.addEventListener('click', function () {
            viewPanel.classList.add('d-none');
            editPanel.classList.remove('d-none');
            editBtn.classList.add('d-none');
            var firstInput = editPanel.querySelector('input[name="name"]');
            if (firstInput) firstInput.focus();
        });
    });
</script>
@endpush
