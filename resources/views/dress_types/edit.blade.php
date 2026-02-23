@extends('layouts.vertical', ['subtitle' => 'Dress Type Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Dress Types', 'subtitle' => 'Edit'])

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edit Dress Type</h5>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="editDressTypeForm"
                action="{{ route('dress-types.update', $dressType) }}"
                method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <input name="code" class="form-control" value="{{ $dressType->code }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control" value="{{ $dressType->name }}" required>
                    </div>
                </div>

                <div class="row">
                    {{-- Front --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diagram Front (optional)</label>
                        <input type="file" name="diagram_front" class="form-control" accept="image/*">
                        <small class="text-muted">Upload new image to replace existing.</small>

                        @if($dressType->diagram_front)
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Current Front Image:</div>
                                <img src="{{ asset($dressType->diagram_front) }}" class="img-thumbnail"
                                    style="max-height:150px;">
                            </div>

                            {{-- Optional remove --}}
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_diagram_front" value="1"
                                    id="remove_front">
                                <label class="form-check-label" for="remove_front">Remove front image</label>
                            </div>
                        @endif
                    </div>

                    {{-- Back --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diagram Back (optional)</label>
                        <input type="file" name="diagram_back" class="form-control" accept="image/*">
                        <small class="text-muted">Upload new image to replace existing.</small>

                        @if($dressType->diagram_back)
                            <div class="mt-2">
                                <div class="text-muted small mb-1">Current Back Image:</div>
                                <img src="{{ asset($dressType->diagram_back) }}" class="img-thumbnail"
                                    style="max-height:150px;">
                            </div>

                            {{-- Optional remove --}}
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_diagram_back" value="1"
                                    id="remove_back">
                                <label class="form-check-label" for="remove_back">Remove back image</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $dressType->notes }}</textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            {{ $dressType->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('dress-types.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('editDressTypeForm').addEventListener('submit', function(e) {
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
                const msg = document.getElementById('message');

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        msg.innerHTML = `<div class="alert alert-danger">${Object.values(data.errors).flat().join('<br>')}</div>`;
                        return;
                    }
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong.'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.href = "{{ route('dress-types.index') }}", 1200);
            });
        });
    </script>
@endsection