@extends('layouts.vertical', ['subtitle' => 'Measurements'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurements', 'subtitle' => 'Template Missing'])

    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger mb-0">
                Measurement template is not selected for this item.
                <br>
                Please go back to the batch item and select a template.
            </div>

            <div class="mt-3">
                <a href="{{ route('tailoring.jobs.show', $job) }}" class="btn btn-secondary">Back to Job</a>
            </div>
        </div>
    </div>
@endsection