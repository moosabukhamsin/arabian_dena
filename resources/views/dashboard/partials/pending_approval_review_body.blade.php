<div class="pending-approval-review">
    <p class="text-muted mb-3">
        <strong>{{ $typeLabel }}</strong> — {{ $displayName }}
    </p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <tbody>
                @foreach ($fields as $field)
                    <tr>
                        <th class="bg-light text-nowrap" style="width: 35%;">{{ $field['label'] }}</th>
                        <td>
                            @if (($field['type'] ?? 'text') === 'image' && $field['value'] !== '—')
                                <img src="{{ $field['value'] }}" alt="{{ $field['label'] }}" class="img-thumbnail" style="max-height: 120px;">
                            @elseif (($field['type'] ?? 'text') === 'link' && $field['value'] !== '—')
                                <a href="{{ $field['value'] }}" target="_blank" rel="noopener noreferrer">View file</a>
                            @elseif (($field['type'] ?? 'text') === 'multiline')
                                <div style="white-space: pre-line;">{{ $field['value'] }}</div>
                            @else
                                {{ $field['value'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <form method="POST" action="{{ route('dashboard.reject_pending', ['entityType' => $entityType, 'id' => $entityId]) }}" class="d-inline" onsubmit="return confirm('Reject this item?');">
            @csrf
            <button type="submit" class="btn btn-danger">Reject</button>
        </form>
        <form method="POST" action="{{ route('dashboard.approve_pending', ['entityType' => $entityType, 'id' => $entityId]) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">Approve</button>
        </form>
    </div>
</div>
