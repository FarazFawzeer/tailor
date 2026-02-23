@extends('layouts.vertical', ['subtitle' => 'Agreement View'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Agreement Details'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ $agreement->agreement_no }}</h5>
            <p class="card-subtitle mb-0">
                Customer: <b>{{ $agreement->customer?->full_name }}</b> |
                Status:
                @if($agreement->status === 'issued')
                    <span class="badge bg-warning">Issued</span>
                @elseif($agreement->status === 'returned')
                    <span class="badge bg-success">Returned</span>
                @else
                    <span class="badge bg-secondary">Cancelled</span>
                @endif
            </p>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><b>Issue:</b> {{ $agreement->issue_date?->format('d M Y') }}</div>
                <div class="col-md-3"><b>Expected Return:</b> {{ $agreement->expected_return_date?->format('d M Y') }}</div>
                <div class="col-md-3"><b>Actual Return:</b> {{ $agreement->actual_return_date?->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3"><b>Fine:</b> Rs {{ number_format((float)$agreement->fine_amount,2) }}</div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Hire Price</th>
                            <th>Deposit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agreement->items as $ai)
                            @php
                                $thumb = $ai->item?->images?->first()?->image_path ? asset($ai->item->images->first()->image_path) : asset('/images/users/avatar-6.jpg');
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="rounded" src="{{ $thumb }}" style="width:40px;height:40px;object-fit:cover;">
                                        <div>
                                            <div class="fw-bold">{{ $ai->item?->name }}</div>
                                            <div class="text-muted small">{{ $ai->item?->category ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><b>{{ $ai->item?->item_code }}</b></td>
                                <td>Rs {{ number_format((float)$ai->hire_price,2) }}</td>
                                <td>Rs {{ number_format((float)$ai->deposit_amount,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('hiring.agreements.index') }}" class="btn btn-secondary">Back</a>

                @if($agreement->status === 'issued')
                    <a href="{{ route('hiring.agreements.return', $agreement) }}" class="btn btn-success">
                        Return Items
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection