@extends('layouts.vertical', ['subtitle' => 'Measurements'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Measurements', 'subtitle' => 'Entry'])

    @php
        // Diagram images from DressType (fallback to defaults)
$frontImg = $item->dressType?->diagram_front ? asset($item->dressType->diagram_front) : asset('/images/diagrams/default-front.png');
$backImg  = $item->dressType?->diagram_back  ? asset($item->dressType->diagram_back)  : asset('/images/diagrams/default-back.png');

        // Map measurement field "key" -> zone class
        $highlightMap = [
            'chest' => 'zone-chest',
            'shoulder' => 'zone-shoulder',
            'sleeve_length' => 'zone-sleeve',
            'shirt_length' => 'zone-length',
            'neck' => 'zone-neck',

            'waist' => 'zone-waist',
            'hip' => 'zone-hip',
            'trouser_length' => 'zone-length',
            'bottom' => 'zone-bottom',
        ];
    @endphp

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Job: {{ $job->job_no }} | {{ $batch->batch_no }}</h5>
            <p class="card-subtitle mb-0">
                Dress: <b>{{ $item->dressType?->name }}</b> |
                Template: <b>{{ $item->measurementTemplate?->name }}</b> |
                Qty: <b>{{ $item->qty }}</b> |
                Mode:
                @if($item->per_piece_measurement)
                    <span class="badge bg-warning">Per Piece</span>
                @else
                    <span class="badge bg-success">Same for All</span>
                @endif
            </p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="measurementForm">
                @csrf

                <div class="row">
                    {{-- LEFT: DIAGRAM --}}
                    <div class="col-md-4 mb-3">
                        <div class="card border h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <b>Diagram</b>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-dark active" id="btnFront">Front</button>
                                    <button type="button" class="btn btn-outline-dark" id="btnBack">Back</button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="diagram-wrap">
                                    <img id="diagramImage" src="{{ $frontImg }}" alt="Diagram" class="img-fluid rounded">

                                    {{-- Highlight layers --}}
                                    <div class="zone zone-neck"></div>
                                    <div class="zone zone-shoulder"></div>
                                    <div class="zone zone-chest"></div>
                                    <div class="zone zone-sleeve"></div>
                                    <div class="zone zone-waist"></div>
                                    <div class="zone zone-hip"></div>
                                    <div class="zone zone-length"></div>
                                    <div class="zone zone-bottom"></div>
                                </div>

                                <div class="mt-2 text-muted small">
                                    Click a measurement field to highlight area.
                                </div>

                                @if($item->per_piece_measurement)
                                    <div class="mt-2 small">
                                        <span class="badge bg-info">Tip</span>
                                        Use Piece tabs to enter different measurements.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: MEASUREMENT FORM --}}
                    <div class="col-md-8">

                        @if(!$item->per_piece_measurement)
                            {{-- SAME MODE --}}
                            <div class="card border mb-3">
                                <div class="card-header">
                                    <b>Same measurements for all pieces (Qty {{ $item->qty }})</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($fields as $f)
                                            @php
                                                $saved = $existing['same'][$f->id] ?? '';
                                                $zoneClass = $highlightMap[$f->key] ?? '';
                                            @endphp

                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">
                                                    {{ $f->label }}
                                                    <small class="text-muted">({{ $f->unit }})</small>
                                                    @if($f->is_required) <span class="text-danger">*</span> @endif
                                                </label>

                                                <input
                                                    type="{{ $f->input_type === 'text' ? 'text' : 'number' }}"
                                                    step="0.01"
                                                    class="form-control measure-field"
                                                    data-zone="{{ $zoneClass }}"
                                                    name="measurements[same][{{ $f->id }}]"
                                                    value="{{ $saved }}"
                                                    placeholder="Enter {{ $f->label }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Notes (optional)</label>
                                        <input class="form-control" name="notes[same]" value="{{ $existing['same']['_notes'] ?? '' }}">
                                    </div>
                                </div>
                            </div>

                        @else
                            {{-- PER PIECE MODE (TABS) --}}
                            <ul class="nav nav-tabs mb-3" id="pieceTabs" role="tablist">
                                @for($p = 1; $p <= $item->qty; $p++)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $p === 1 ? 'active' : '' }}"
                                                id="piece-tab-{{ $p }}"
                                                data-bs-toggle="tab"
                                                data-bs-target="#piece-pane-{{ $p }}"
                                                type="button"
                                                role="tab"
                                                aria-controls="piece-pane-{{ $p }}"
                                                aria-selected="{{ $p === 1 ? 'true' : 'false' }}">
                                            Piece {{ $p }}
                                        </button>
                                    </li>
                                @endfor
                            </ul>

                            <div class="tab-content" id="pieceTabContent">
                                @for($p = 1; $p <= $item->qty; $p++)
                                    @php
                                        $pieceKey = (string)$p;
                                    @endphp

                                    <div class="tab-pane fade {{ $p === 1 ? 'show active' : '' }}"
                                         id="piece-pane-{{ $p }}"
                                         role="tabpanel"
                                         aria-labelledby="piece-tab-{{ $p }}">

                                        <div class="card border mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <b>Piece {{ $p }} Measurements</b>
                                                <span class="text-muted small">Enter actual values for this piece</span>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($fields as $f)
                                                        @php
                                                            $saved = $existing[$pieceKey][$f->id] ?? '';
                                                            $zoneClass = $highlightMap[$f->key] ?? '';
                                                        @endphp

                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">
                                                                {{ $f->label }}
                                                                <small class="text-muted">({{ $f->unit }})</small>
                                                                @if($f->is_required) <span class="text-danger">*</span> @endif
                                                            </label>

                                                            <input
                                                                type="{{ $f->input_type === 'text' ? 'text' : 'number' }}"
                                                                step="0.01"
                                                                class="form-control measure-field"
                                                                data-zone="{{ $zoneClass }}"
                                                                name="measurements[{{ $pieceKey }}][{{ $f->id }}]"
                                                                value="{{ $saved }}"
                                                                placeholder="Enter {{ $f->label }}">
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="mb-0">
                                                    <label class="form-label">Notes (optional)</label>
                                                    <input class="form-control" name="notes[{{ $pieceKey }}]"
                                                           value="{{ $existing[$pieceKey]['_notes'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endfor
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tailoring.jobs.show', $job) }}" class="btn btn-secondary">Back</a>
                            <button class="btn btn-primary" type="submit">Save Measurements</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Diagram Styles --}}
    <style>
        .diagram-wrap { position: relative; width: 100%; }
        .diagram-wrap img { width: 100%; height: auto; display: block; }

        .zone {
            position: absolute;
            border-radius: 10px;
            opacity: 0;
            transition: opacity .15s ease-in-out;
            outline: 2px dashed rgba(0,0,0,.35);
            background: rgba(255, 193, 7, 0.25);
            pointer-events: none;
        }
        .zone.active { opacity: 1; }

        /* Approximate positions - adjust based on your diagram */
        .zone-neck { top: 6%; left: 38%; width: 24%; height: 10%; }
        .zone-shoulder { top: 12%; left: 20%; width: 60%; height: 12%; }
        .zone-chest { top: 25%; left: 25%; width: 50%; height: 16%; }
        .zone-sleeve { top: 20%; left: 5%; width: 20%; height: 22%; }
        .zone-waist { top: 42%; left: 28%; width: 44%; height: 14%; }
        .zone-hip { top: 55%; left: 28%; width: 44%; height: 14%; }
        .zone-length { top: 68%; left: 32%; width: 36%; height: 24%; }
        .zone-bottom { top: 86%; left: 32%; width: 36%; height: 10%; }

        /* Tabs scroll if qty large */
        #pieceTabs { overflow-x: auto; flex-wrap: nowrap; }
        #pieceTabs .nav-link { white-space: nowrap; }
    </style>

    <script>
        // Diagram front/back switching
        const frontImg = @json($frontImg);
        const backImg  = @json($backImg);

        const diagramImage = document.getElementById('diagramImage');
        const btnFront = document.getElementById('btnFront');
        const btnBack = document.getElementById('btnBack');

        function clearZones() {
            document.querySelectorAll('.zone').forEach(z => z.classList.remove('active'));
        }

        btnFront?.addEventListener('click', () => {
            diagramImage.src = frontImg;
            btnFront.classList.add('active');
            btnBack.classList.remove('active');
            clearZones();
        });

        btnBack?.addEventListener('click', () => {
            diagramImage.src = backImg;
            btnBack.classList.add('active');
            btnFront.classList.remove('active');
            clearZones();
        });

        // Highlight zones on focus/click
        document.addEventListener('focusin', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones();
            if (!zone) return;
            const el = document.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            const zone = e.target.dataset.zone;
            clearZones();
            if (!zone) return;
            const el = document.querySelector('.' + zone);
            if (el) el.classList.add('active');
        });

        document.addEventListener('focusout', function(e) {
            if (!e.target.classList.contains('measure-field')) return;
            setTimeout(clearZones, 120);
        });

        // Save measurements (AJAX)
        document.getElementById('measurementForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("{{ route('tailoring.measurements.store', [$job, $batch, $item]) }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                const msg = document.getElementById('message');

                if (!res.ok) {
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Validation error'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => msg.innerHTML = "", 2500);
            }).catch(err => {
                document.getElementById('message').innerHTML =
                    `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });
    </script>
@endsection