@extends('layouts.vertical', ['subtitle' => 'Upcoming Returns'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Upcoming Returns'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Upcoming Returns (Next 7 Days)</h5>
            <p class="card-subtitle mb-0">Agreements with items still hired out.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Agreement</th>
                            <th>Customer</th>
                            <th>Expected Return</th>
                            <th>Items Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                            <tr>
                                <td><b>{{ $it->agreement_no }}</b></td>
                                <td>{{ $it->full_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($it->expected_return_date)->format('d M Y') }}</td>
                                <td><span class="badge bg-primary">{{ $it->items_out }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No upcoming returns.</td>
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