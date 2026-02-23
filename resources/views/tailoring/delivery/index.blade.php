@extends('layouts.vertical', ['subtitle' => 'Delivery'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Tailoring', 'subtitle' => 'Delivery'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Delivery Jobs</h5>
            <p class="card-subtitle mb-0">View invoice and mark jobs as delivered.</p>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Search Job No / Customer">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-dark w-100">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tailoring.delivery.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job No</th>
                            <th>Customer</th>
                            <th>Delivered</th>
                            <th>Delivered Date</th>
                            <th>Grand Total</th>
                            <th width="260">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $j)
                            <tr>
                                <td><b>{{ $j->job_no }}</b></td>
                                <td>{{ $j->customer?->full_name ?? 'N/A' }}</td>
                                <td>
                                    @if($j->delivery)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $j->delivery?->delivered_date?->format('d M Y') ?? '-' }}</td>
                                <td>{{ number_format((float)($j->delivery?->grand_total ?? 0), 2) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-outline-dark btn-sm w-100"
                                           href="{{ route('tailoring.delivery.invoice', $j) }}">Invoice</a>
                                        <a class="btn btn-info btn-sm w-100"
                                           href="{{ route('tailoring.delivery.print', $j) }}" target="_blank">Print</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No jobs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection