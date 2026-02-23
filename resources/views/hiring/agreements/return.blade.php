@extends('layouts.vertical', ['subtitle' => 'Return Items'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Return'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Return Items - {{ $agreement->agreement_no }}</h5>
            <p class="card-subtitle mb-0">
                Expected Return: <b>{{ $agreement->expected_return_date?->format('d M Y') }}</b> |
                Fine per day: <b>Rs {{ number_format((float)$agreement->fine_per_day,2) }}</b>
            </p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="returnForm">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Actual Return Date</label>
                        <input type="date" name="actual_return_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <input name="notes" class="form-control" placeholder="Any notes...">
                    </div>
                </div>

                <div class="alert alert-info">
                    On submit, system will:
                    <b>calculate fine</b> and set all items back to <b>Available</b>.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('hiring.agreements.show', $agreement) }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-success" type="submit">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const fd = new FormData(this);

            fetch("{{ route('hiring.agreements.return.store', $agreement) }}", {
                method: "POST",
                body: fd,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                const msg = document.getElementById('message');

                if (!res.ok) {
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.href = "{{ route('hiring.agreements.show', $agreement) }}", 900);
            });
        });
    </script>
@endsection