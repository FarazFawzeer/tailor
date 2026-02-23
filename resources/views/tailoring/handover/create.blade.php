@extends('layouts.vertical', ['subtitle' => 'Handover Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Handover', 'subtitle' => 'Create'])

    @php
        $jobNo   = $item->jobBatch?->job?->job_no ?? '-';
        $batchNo = $item->jobBatch?->batch_no ?? '-';
        $customer = $item->jobBatch?->job?->customer?->full_name ?? 'N/A';
        $dress = $item->dressType?->name ?? 'N/A';
        $stageName = $item->stage?->name ?? 'N/A';

        // group id to understand split items
        $groupId = $item->parent_item_id ? $item->parent_item_id : $item->id;
        $isPartial = (bool)$item->parent_item_id;
    @endphp

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="card-title mb-1">
                        Job: {{ $jobNo }} | Batch: {{ $batchNo }}
                        @if($isPartial)
                            <span class="badge bg-warning ms-2">Partial Item</span>
                        @endif
                    </h5>

                    <p class="card-subtitle mb-0">
                        Customer: <b>{{ $customer }}</b> |
                        Dress: <b>{{ $dress }}</b> |
                        Current Stage: <b>{{ $stageName }}</b>
                        <span class="text-muted">| Group: #{{ $groupId }}</span>
                    </p>
                </div>

                <div class="text-end">
                    <div class="small text-muted">Available Qty in this stage</div>
                    <div class="fs-4 fw-bold">{{ $item->qty }}</div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="message"></div>

            {{-- Explain next stage & partial handover --}}
            @if(!$nextStage)
                <div class="alert alert-warning mb-3">
                    <b>No next stage found.</b> This means the workflow ends here.
                    You can mark this item as <b>Completed</b>.
                </div>
            @else
                <div class="alert alert-info mb-3">
                    <div><b>Next Stage:</b> {{ $nextStage->name }}</div>
                    <div class="small mt-1">
                        <b>Note:</b> If you handover less than {{ $item->qty }}, the system will keep the remaining qty in
                        <b>{{ $stageName }}</b> and create a new item for <b>{{ $nextStage->name }}</b>.
                    </div>
                </div>
            @endif

            <form id="handoverForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Received By (Next Stage Staff)</label>
                        <select name="received_by" class="form-select" required {{ !$nextStage ? 'disabled' : '' }}>
                            <option value="">Select Staff</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the staff who will work in the next stage.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Qty to Handover</label>
                        <input type="number"
                               class="form-control"
                               name="qty"
                               min="1"
                               max="{{ $item->qty }}"
                               value="{{ $item->qty }}"
                               required {{ !$nextStage ? 'disabled' : '' }}>
                        <small class="text-muted">
                            You can handover partial qty (Ex: 2 now, 3 later).
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Example: 2 pcs ready for sewing, remaining tomorrow..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tailoring.handover.index') }}" class="btn btn-secondary">Back</a>

                    @if($nextStage)
                        <button class="btn btn-primary" type="submit">
                            Save Handover
                        </button>
                    @endif

                    {{-- Complete button --}}
                    <button type="button" class="btn btn-success" id="btnComplete">
                        Mark Completed
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('message');

        // Save handover
        document.getElementById('handoverForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("{{ route('tailoring.handover.store', $item) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    messageBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Validation error'}</div>`;
                    return;
                }

                messageBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.href = "{{ route('tailoring.handover.index') }}", 800);
            }).catch(err => {
                messageBox.innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });

        // Complete
        document.getElementById('btnComplete').addEventListener('click', function() {
            Swal.fire({
                title: 'Mark as Completed?',
                text: "This will complete the item in the current stage.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, complete'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch("{{ route('tailoring.handover.complete', $item) }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                }).then(async res => {
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        messageBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Error'}</div>`;
                        return;
                    }

                    Swal.fire('Completed!', data.message, 'success');
                    setTimeout(() => window.location.href = "{{ route('tailoring.handover.index') }}", 800);
                });
            });
        });
    </script>
@endsection