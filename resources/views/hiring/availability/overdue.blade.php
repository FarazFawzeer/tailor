@extends('layouts.vertical', ['subtitle' => 'Overdue'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Overdue Agreements'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Overdue Agreements</h5>
            <p class="card-subtitle mb-0">Expected return date passed, items are still hired out.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Issue Date</th>
                            <th>Expected Return</th>
                            <th>Items Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                            <tr>
                                <td><b>{{ $it->agreement_no }}</b></td>
                                <td>{{ $it->full_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($it->issue_date)->format('d M Y') }}</td>
                                <td><span class="badge bg-danger">{{ \Carbon\Carbon::parse($it->expected_return_date)->format('d M Y') }}</span></td>
                                <td><span class="badge bg-primary">{{ $it->items_out }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No overdue agreements.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection