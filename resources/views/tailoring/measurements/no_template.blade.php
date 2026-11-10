@extends('layouts.vertical', ['subtitle' => 'Measurements'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurements', 'subtitle' => 'No Template'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Job: {{ $job->job_no }} | {{ $batch->batch_no }}</h5>
            <p class="card-subtitle mb-0">
                Dress: <b>{{ $item->dressType?->name ?? '-' }}</b> |
                Qty: <b>{{ $item->qty }}</b>
            </p>
        </div>
        <div class="card-body">
            @if (!empty($savedRows))
                <div class="alert alert-warning">
                    This item has no measurement template, but these saved measurements were found (view only).
                </div>

                @foreach ($savedRows as $row)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-2">{{ $row['title'] }}</h6>
                        <table class="table table-sm table-bordered mb-2">
                            <tbody>
                                @foreach ($row['values'] as $v)
                                    <tr>
                                        <th style="width: 40%;">{{ $v['label'] }}</th>
                                        <td>{{ $v['value'] }} {{ $v['unit'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($row['notes'])
                            <div class="text-muted"><b>Notes:</b> {{ $row['notes'] }}</div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="alert alert-info mb-0">
                    No measurements were recorded for this item, because no measurement template was selected
                    when the order was created.
                    <br>
                    To add measurements, open <b>Edit Job</b>, select a template for this item and enter the measurements.
                </div>
            @endif

            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('tailoring.jobs.show', $job) }}" class="btn btn-secondary">Back to Job</a>
                @can('jobs.edit')
                    <a href="{{ route('tailoring.jobs.editWizard', $job) }}" class="btn btn-primary">Edit Job</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
