@extends('layouts.vertical', ['subtitle' => 'Hire Agreement Create'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Create Agreement'])

    @php
        // Build unique category list for filter (optional)
        $categories = $availableItems->pluck('category')->filter()->unique()->values();
    @endphp

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Hire Agreement</h5>
            <p class="card-subtitle mb-0">Select available items by clicking on them (no manual typing).</p>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="agreementForm" action="{{ route('hiring.agreements.store') }}" method="POST">
                @csrf

                {{-- Agreement Info --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Agreement No</label>
                        <input name="agreement_no" class="form-control" value="{{ $agreementNo }}" readonly>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->full_name }} {{ $c->phone ? ' - '.$c->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dates + Fine --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Expected Return Date</label>
                        <input type="date" name="expected_return_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fine Per Day</label>
                        <input type="number" step="0.01" name="fine_per_day" class="form-control" value="0">
                    </div>
                </div>

                {{-- Deposit + Notes --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Deposit Received</label>
                        <input type="number" step="0.01" name="deposit_received" class="form-control" value="0">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Notes</label>
                        <input name="notes" class="form-control" placeholder="Optional notes...">
                    </div>
                </div>

                {{-- Selected Summary --}}
                <div class="card border mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <b>Selected Items</b>
                        <div class="text-muted small">
                            <span class="me-3">Selected: <b id="selectedCount">0</b></span>
                            Total Hire: <b>Rs <span id="selectedTotal">0.00</span></b>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="selectedItems" class="row g-2"></div>
                        <div id="selectedHint" class="text-muted small mt-2">
                            Please select at least one available item from below.
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="card border mb-3">
                    <div class="card-header">
                        <b>Available Items</b>
                        <div class="text-muted small">Click an item card to add/remove.</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="text" id="itemSearch" class="form-control"
                                    placeholder="Search by code / name / category...">
                            </div>

                            <div class="col-md-3">
                                <select id="categoryFilter" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ strtolower($cat) }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="button" id="clearSelection" class="btn btn-outline-secondary w-100">
                                    Clear Selected
                                </button>
                            </div>
                        </div>

                        <div class="row g-2" id="availableGrid">
                            @foreach($availableItems as $it)
                                @php
                                    $thumb = $it->images->first()?->image_path ? asset($it->images->first()->image_path) : asset('/images/users/avatar-6.jpg');
                                    $cat = strtolower($it->category ?? '');
                                @endphp

                                <div class="col-md-3 item-card-wrap"
                                    data-id="{{ $it->id }}"
                                    data-code="{{ strtolower($it->item_code) }}"
                                    data-name="{{ strtolower($it->name) }}"
                                    data-category="{{ $cat }}"
                                    data-price="{{ (float)$it->hire_price }}"
                                    data-code-original="{{ $it->item_code }}"
                                    data-name-original="{{ $it->name }}"
                                    data-thumb="{{ $thumb }}">

                                    <div class="border rounded p-2 h-100 item-card" style="cursor:pointer;">
                                        <div class="d-flex gap-2 align-items-center">
                                            <img src="{{ $thumb }}" class="rounded" style="width:44px;height:44px;object-fit:cover;">
                                            <div>
                                                <div class="fw-bold">{{ $it->item_code }}</div>
                                                <div class="small text-muted">{{ $it->name }}</div>
                                                <div class="small">Rs {{ number_format((float)$it->hire_price, 2) }}</div>
                                            </div>
                                        </div>

                                        <div class="mt-2 d-flex justify-content-between align-items-center">
                                            <span class="badge bg-success">Available</span>
                                            <span class="badge bg-light text-dark border selectBadge">Click to Select</span>
                                        </div>

                                        {{-- checkbox hidden but still usable for accessibility --}}
                                        <input class="d-none pickItem"
                                            type="checkbox"
                                            value="{{ $it->id }}"
                                            data-code="{{ $it->item_code }}"
                                            data-name="{{ $it->name }}"
                                            data-price="{{ (float)$it->hire_price }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($availableItems->count() === 0)
                            <div class="text-center text-muted py-4">
                                No available items right now.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('hiring.agreements.index') }}" class="btn btn-secondary">Back</a>
                    <button class="btn btn-primary" type="submit">Issue Items</button>
                </div>

            </form>
        </div>
    </div>

    <style>
        .item-card.selected {
            outline: 2px solid rgba(13,110,253,.5);
            background: rgba(13,110,253,.06);
        }
        .item-card.selected .selectBadge {
            background: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }
    </style>

    <script>
        const selected = new Map(); // id => {id, code, name, price, thumb}

        const selectedCountEl = document.getElementById('selectedCount');
        const selectedTotalEl = document.getElementById('selectedTotal');
        const selectedHintEl = document.getElementById('selectedHint');

        function money(n){ return Number(n || 0).toFixed(2); }

        function updateSummary() {
            selectedCountEl.textContent = selected.size;
            let total = 0;
            selected.forEach(v => total += Number(v.price || 0));
            selectedTotalEl.textContent = money(total);

            selectedHintEl.style.display = selected.size ? 'none' : 'block';
        }

        function renderSelected() {
            const wrap = document.getElementById('selectedItems');
            wrap.innerHTML = "";

            selected.forEach(v => {
                wrap.innerHTML += `
                    <div class="col-md-4" id="sel-${v.id}">
                        <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="${v.thumb}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                <div>
                                    <div class="fw-bold">${v.code}</div>
                                    <div class="text-muted small">${v.name}</div>
                                    <div class="small">Rs ${money(v.price)}</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeSelected(${v.id})">
                                Remove
                            </button>
                        </div>
                        <input type="hidden" name="item_ids[]" value="${v.id}">
                    </div>
                `;
            });

            updateSummary();
        }

        window.removeSelected = function(id) {
            selected.delete(Number(id));
            const cb = document.querySelector(`.pickItem[value="${id}"]`);
            if (cb) cb.checked = false;

            const cardWrap = document.querySelector(`.item-card-wrap[data-id="${id}"] .item-card`);
            if (cardWrap) cardWrap.classList.remove('selected');

            renderSelected();
        }

        // Click card to toggle selection
        document.querySelectorAll('.item-card-wrap').forEach(wrap => {
            const card = wrap.querySelector('.item-card');
            const cb = wrap.querySelector('.pickItem');

            card.addEventListener('click', () => {
                cb.checked = !cb.checked;

                const id = Number(cb.value);
                if (cb.checked) {
                    selected.set(id, {
                        id,
                        code: wrap.dataset.codeOriginal,
                        name: wrap.dataset.nameOriginal,
                        price: wrap.dataset.price,
                        thumb: wrap.dataset.thumb
                    });
                    card.classList.add('selected');
                } else {
                    selected.delete(id);
                    card.classList.remove('selected');
                }
                renderSelected();
            });
        });

        // Search + filter
        const searchEl = document.getElementById('itemSearch');
        const catEl = document.getElementById('categoryFilter');

        function applyFilter() {
            const q = (searchEl.value || '').trim().toLowerCase();
            const cat = (catEl.value || '').trim().toLowerCase();

            document.querySelectorAll('.item-card-wrap').forEach(wrap => {
                const text = `${wrap.dataset.code} ${wrap.dataset.name} ${wrap.dataset.category}`;
                const matchesQ = !q || text.includes(q);
                const matchesCat = !cat || wrap.dataset.category === cat;

                wrap.style.display = (matchesQ && matchesCat) ? '' : 'none';
            });
        }

        searchEl.addEventListener('input', applyFilter);
        catEl.addEventListener('change', applyFilter);

        // Clear selected
        document.getElementById('clearSelection').addEventListener('click', () => {
            selected.clear();
            document.querySelectorAll('.pickItem').forEach(cb => cb.checked = false);
            document.querySelectorAll('.item-card').forEach(c => c.classList.remove('selected'));
            renderSelected();
        });

        // Submit (AJAX)
        document.getElementById('agreementForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (selected.size === 0) {
                Swal.fire('Select Items', 'Please select at least one available item.', 'warning');
                return;
            }

            const form = this;
            const fd = new FormData(form);

            fetch(form.action, {
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
                    if (res.status === 422 && data.errors) {
                        msg.innerHTML = `<div class="alert alert-danger">${Object.values(data.errors).flat().join('<br>')}</div>`;
                        return;
                    }
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Something went wrong.'}</div>`;
                    return;
                }

                msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => window.location.href = "{{ route('hiring.agreements.index') }}", 900);
            }).catch(err => {
                document.getElementById('message').innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
            });
        });

        // initial
        renderSelected();
    </script>
@endsection