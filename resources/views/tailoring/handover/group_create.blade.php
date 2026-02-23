@extends('layouts.vertical', ['subtitle' => 'Group Handover'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Handover', 'subtitle' => 'Group'])

    @php
        $jobNo   = $headerItem->jobBatch?->job?->job_no ?? '-';
        $batchNo = $headerItem->jobBatch?->batch_no ?? '-';
        $customer = $headerItem->jobBatch?->job?->customer?->full_name ?? 'N/A';
        $dress = $headerItem->dressType?->name ?? 'N/A';
    @endphp

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-1">Job: {{ $jobNo }} | Batch: {{ $batchNo }}</h5>
            <p class="card-subtitle mb-0">
                Customer: <b>{{ $customer }}</b> | Dress: <b>{{ $dress }}</b> | Group: <b>#{{ $groupId }}</b>
            </p>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <b>How to use:</b>
                Select the <b>From Stage</b> (example: Cutting) and enter qty to send.
                This will not affect other stages.
            </div>

            <div class="row g-2 mb-3">
                @foreach($stageSummary as $s)
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <div class="text-muted small">{{ $s['stage_name'] }}</div>
                            <div class="fs-4 fw-bold">{{ $s['qty'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="message"></div>

            <form id="groupHandoverForm">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">From Stage</label>
                        <select name="from_stage_id" id="from_stage_id" class="form-select" required>
                            <option value="">Select stage</option>
                            @foreach($stageSummary as $s)
                                <option value="{{ $s['stage_id'] }}" data-qty="{{ $s['qty'] }}">
                                    {{ $s['stage_name'] }} (Available: {{ $s['qty'] }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Choose which stage you are sending from.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Qty to Handover</label>
                        <input type="number" class="form-control" name="qty" id="qty"
                               min="1" value="1" required>
                        <small class="text-muted">Max will be auto set based on selected stage.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Received By (Next Stage Staff)</label>
                        <select name="received_by" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Example: Sent 2 pcs to sewing, remaining later..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tailoring.handover.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Save Handover</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('message');
        const fromStage = document.getElementById('from_stage_id');
        const qtyInput = document.getElementById('qty');

        fromStage.addEventListener('change', () => {
            const opt = fromStage.options[fromStage.selectedIndex];
            const maxQty = parseInt(opt.dataset.qty || "1");
            qtyInput.max = maxQty;
            qtyInput.value = Math.min(parseInt(qtyInput.value || "1"), maxQty);
        });

        document.getElementById('groupHandoverForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("{{ route('tailoring.handover.group.store', $groupId) }}", {
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
                setTimeout(() => window.location.reload(), 700);
            }).catch(err => {
                messageBox.innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
@endsection