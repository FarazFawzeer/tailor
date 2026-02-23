@extends('layouts.vertical', ['subtitle' => 'Measurement Template Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurement Templates', 'subtitle' => 'Create'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Measurement Template</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="createTemplateForm" action="{{ route('measurement-templates.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dress Type</label>
                        <select name="dress_type_id" class="form-select" required>
                            <option value="">Select Dress Type</option>
                            @foreach($dressTypes as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Template Name</label>
                        <input name="name" class="form-control" placeholder="Ex: Normal / Slim Fit" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('measurement-templates.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Create Template</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createTemplateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                }
            }).then(async res => {
                let data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        let errors = Object.values(data.errors).flat().join('<br>');
                        document.getElementById('message').innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                        return;
                    }
                    document.getElementById('message').innerHTML = `<div class="alert alert-danger">Something went wrong.</div>`;
                    return;
                }

                document.getElementById('message').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                this.reset();
                setTimeout(() => document.getElementById('message').innerHTML = "", 2500);
            });
        });
    </script>
@endsection