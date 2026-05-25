@extends('dashboard.layout', ['page' => 'pending_approvals'])
@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <h1 class="page-title">Pending Approvals</h1>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Items awaiting review</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Modified by</th>
                                                <th>Last updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingRows as $row)
                                                <tr>
                                                    <td>{{ $row['type_label'] }}</td>
                                                    <td>{{ $row['entity_id'] }}</td>
                                                    <td>{{ $row['label'] }}</td>
                                                    <td>{{ $row['modified_by'] }}</td>
                                                    <td>{{ \App\Support\DateTimeFormatter::format($row['updated_at'] ?? null) }}</td>
                                                    <td class="text-nowrap">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-info btn-review-pending me-1"
                                                            data-entity-type="{{ $row['entity_type'] }}"
                                                            data-entity-id="{{ $row['entity_id'] }}"
                                                            data-review-url="{{ route('dashboard.review_pending', ['entityType' => $row['entity_type'], 'id' => $row['entity_id']]) }}"
                                                        >
                                                            Review
                                                        </button>
                                                        <form method="POST" action="{{ route('dashboard.approve_pending', ['entityType' => $row['entity_type'], 'id' => $row['entity_id']]) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('dashboard.reject_pending', ['entityType' => $row['entity_type'], 'id' => $row['entity_id']]) }}" class="d-inline ms-1" onsubmit="return confirm('Reject this item?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No pending approvals.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pendingReviewModal" tabindex="-1" aria-labelledby="pendingReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingReviewModalLabel">Review pending request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="pendingReviewModalBody">
                    <div class="text-center text-muted py-4">Loading…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('pendingReviewModal');
    const modalBody = document.getElementById('pendingReviewModalBody');
    const modalTitle = document.getElementById('pendingReviewModalLabel');

    if (!modalEl || !modalBody) {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.querySelectorAll('.btn-review-pending').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = btn.getAttribute('data-review-url');
            const typeLabel = btn.closest('tr')?.querySelector('td')?.textContent?.trim() || 'Item';

            modalTitle.textContent = 'Review: ' + typeLabel;
            modalBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
            modal.show();

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load review details.');
                    }
                    return response.text();
                })
                .then(function (html) {
                    modalBody.innerHTML = html;
                })
                .catch(function () {
                    modalBody.innerHTML = '<div class="alert alert-danger mb-0">Could not load review details. Please try again.</div>';
                });
        });
    });
});
</script>
@endpush
