@extends('layouts.vertical', ['subtitle' => 'Hire Agreement Edit'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Hiring', 'subtitle' => 'Edit Agreement'])

    @php
        // availableItems should include images + variants
        $categories = $availableItems->pluck('category')->filter()->unique()->values();

        // agreement should load: customer + items.item.images + items.item.variants (optional)
        // Build initial selected lines from agreement items
        $initialLines = ($agreement->items ?? collect())->map(function ($ai) {
            $item = $ai->item;
            $thumb = $item?->images?->first()?->image_path ? asset($item->images->first()->image_path) : asset('/images/users/avatar-6.jpg');

            return [
                'key' => $ai->hire_item_id . '__' . ($ai->size ?? ''),
                'item_id' => (int)$ai->hire_item_id,
                'size' => (string)($ai->size ?? ''),
                'qty' => (int)($ai->qty ?? 1),
                'price' => (float)($ai->hire_price ?? ($item?->hire_price ?? 0)),
                'deposit' => (float)($ai->deposit_amount ?? ($item?->deposit_amount ?? 0)),
                'code' => (string)($item?->item_code ?? ''),
                'name' => (string)($item?->name ?? ''),
                'thumb' => $thumb,
                // maxQty will be calculated in JS (stock + already selected qty)
                'maxQty' => 0,
            ];
        })->values();
    @endphp

    <style>
        .report-card { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .muted-help { font-size: 12px; color: #6c757d; }
        .pill { padding: 2px 10px; border-radius: 999px; font-size: 12px; background: rgba(13,110,253,.08); }

        .item-card { border:1px solid rgba(0,0,0,.08); border-radius: 14px; cursor: pointer; transition: .15s; }
        .item-card:hover { border-color: rgba(13,110,253,.35); transform: translateY(-1px); }
        .thumb { width:46px; height:46px; border-radius: 12px; object-fit: cover; border:1px solid rgba(0,0,0,.08); }

        .selected-card { border:1px solid rgba(13,110,253,.25); border-radius:14px; }
        .selected-row { border:1px solid rgba(0,0,0,.08); border-radius:14px; }
        .qty-btn { width: 42px; height: 42px; display:flex; align-items:center; justify-content:center; }
        .qty-input { height: 42px; text-align:center; font-weight:600; }

        .sticky-actions { position: sticky; bottom: 0; background: #fff; padding-top: 10px; }
        .big-btn { height: 42px; }
    </style>

    <div class="card report-card">
        <div class="card-header d-flex align-items-start justify-content-between">
            <div>
                <h5 class="card-title mb-0">Edit Hire Agreement</h5>
                <div class="muted-help mt-1">
                    Update customer/dates, add/remove items, change size & qty.
                </div>
            </div>
            <div class="text-end">
                <span class="pill">Agreement: {{ $agreement->agreement_no }}</span>
                <span class="ms-2 badge bg-info">{{ ucfirst($agreement->status) }}</span>
            </div>
        </div>

        <div class="card-body">
            <div id="message"></div>

            <form id="agreementForm" action="{{ route('hiring.agreements.update', $agreement->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Agreement Info --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Agreement No</label>
                        <input name="agreement_no" class="form-control" value="{{ $agreement->agreement_no }}" readonly>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label mb-1">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ (int)$agreement->customer_id === (int)$c->id ? 'selected' : '' }}>
                                    {{ $c->full_name }} {{ $c->phone ? ' - '.$c->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control"
                               value="{{ \Carbon\Carbon::parse($agreement->issue_date)->toDateString() }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1">Expected Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="expected_return_date" class="form-control"
                               value="{{ \Carbon\Carbon::parse($agreement->expected_return_date)->toDateString() }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1">Fine Per Day</label>
                        <input type="number" step="0.01" name="fine_per_day" class="form-control"
                               value="{{ (float)($agreement->fine_per_day ?? 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1">Deposit Received</label>
                        <input type="number" step="0.01" name="deposit_received" class="form-control"
                               value="{{ (float)($agreement->deposit_received ?? 0) }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label mb-1">Notes</label>
                        <input name="notes" class="form-control" placeholder="Optional notes..." value="{{ $agreement->notes }}">
                    </div>
                </div>

                {{-- Selected Summary --}}
                <div class="card selected-card border mt-4 mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <b>Selected Items</b>
                        <div class="text-muted small">
                            <span class="me-3">Lines: <b id="selectedCount">0</b></span>
                            Total Hire: <b>Rs <span id="selectedTotal">0.00</span></b>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="selectedItems" class="d-flex flex-column gap-2"></div>
                        <div id="selectedHint" class="text-muted small mt-2">
                            Please select items below, then choose size & qty.
                        </div>
                        <div class="muted-help mt-2">
                            Note: Stock limits are calculated as (current stock + already selected qty in this agreement).
                        </div>
                    </div>
                </div>

                {{-- Available Items --}}
                <div class="card border mb-3">
                    <div class="card-header">
                        <b>Available Items</b>
                        <div class="text-muted small">Click an item to add. Then choose size & qty in Selected Items.</div>
                    </div>

                    <div class="card-body">
                        {{-- Filters --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body py-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="ti ti-search"></i></span>
                                            <input type="text" id="itemSearch" class="form-control"
                                                   placeholder="Search item code, name or category...">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <select id="categoryFilter" class="form-select">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ strtolower($cat) }}">{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex gap-2">
                                        <button type="button" id="clearSelection" class="btn btn-light border w-100 big-btn">
                                            <i class="ti ti-refresh me-1"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Grid --}}
                        <div class="row g-2" id="availableGrid">
                            @foreach($availableItems as $it)
                                @php
                                    $thumb = $it->images->first()?->image_path ? asset($it->images->first()->image_path) : asset('/images/users/avatar-6.jpg');
                                    $cat = strtolower($it->category ?? '');
                                    $variants = $it->variants ?? collect();
                                    $totalQty = (int)$variants->sum('qty');
                                @endphp

                                <div class="col-md-3 item-wrap"
                                     data-id="{{ $it->id }}"
                                     data-code="{{ strtolower($it->item_code) }}"
                                     data-name="{{ strtolower($it->name) }}"
                                     data-category="{{ $cat }}"
                                     data-price="{{ (float)$it->hire_price }}"
                                     data-deposit="{{ (float)($it->deposit_amount ?? 0) }}"
                                     data-code-original="{{ $it->item_code }}"
                                     data-name-original="{{ $it->name }}"
                                     data-thumb="{{ $thumb }}"
                                     data-variants='@json($variants->map(fn($v)=>["size"=>$v->size,"qty"=>(int)$v->qty])->values())'
                                >
                                    <div class="item-card p-2 h-100">
                                        <div class="d-flex gap-2 align-items-center">
                                            <img src="{{ $thumb }}" class="thumb" alt="img">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $it->item_code }}</div>
                                                <div class="small text-muted">{{ $it->name }}</div>
                                                <div class="small">Rs {{ number_format((float)$it->hire_price, 2) }}</div>
                                            </div>
                                        </div>
                                        <div class="mt-2 d-flex justify-content-between align-items-center">
                                            <span class="badge bg-success">Available</span>
                                            <span class="badge bg-light text-dark border">Stock: {{ $totalQty }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark border w-100 text-center">Click to Add</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($availableItems->count() === 0)
                            <div class="text-center text-muted py-4">No available items right now.</div>
                        @endif
                    </div>
                </div>

                {{-- Submit --}}
                <div class="sticky-actions">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('hiring.agreements.index') }}" class="btn btn-secondary big-btn">Back</a>
                        <button class="btn btn-primary big-btn" type="submit">
                            <i class="ti ti-check me-1"></i> Update Agreement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        /**
         * selected key = `${itemId}__${size}`
         * value = { key, item_id, size, qty, price, deposit, code, name, thumb, maxQty }
         */
        const selected = new Map();

        const selectedCountEl = document.getElementById('selectedCount');
        const selectedTotalEl = document.getElementById('selectedTotal');
        const selectedHintEl  = document.getElementById('selectedHint');

        // Initial lines from PHP
        const initialLines = @json($initialLines);

        function money(n){ return Number(n || 0).toFixed(2); }

        // Build a stock map from available grid: itemId -> { size -> qty }
        const stockMap = new Map(); // itemId => Map(size => qty)

        document.querySelectorAll('.item-wrap').forEach(wrap => {
            const itemId = Number(wrap.dataset.id);
            const variants = JSON.parse(wrap.dataset.variants || '[]');

            const bySize = new Map();
            variants.forEach(v => bySize.set(String(v.size), Number(v.qty||0)));

            stockMap.set(itemId, bySize);
        });

        // Compute max qty for edit:
        // maxQty = currentStock + alreadySelectedQtyFromAgreementForThatLine
        function computeMaxQty(itemId, size, currentSelectedQty){
            const bySize = stockMap.get(Number(itemId));
            const stock = bySize ? (Number(bySize.get(String(size)) || 0)) : 0;
            return stock + Number(currentSelectedQty || 0);
        }

        function updateSummary() {
            selectedCountEl.textContent = selected.size;
            let total = 0;
            selected.forEach(v => total += (Number(v.price||0) * Number(v.qty||0)));
            selectedTotalEl.textContent = money(total);
            selectedHintEl.style.display = selected.size ? 'none' : 'block';
        }

        function renderSelected() {
            const wrap = document.getElementById('selectedItems');
            wrap.innerHTML = "";

            selected.forEach(v => {
                wrap.innerHTML += `
                    <div class="selected-row p-2">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">

                            <div class="d-flex align-items-center gap-2">
                                <img src="${v.thumb}" class="thumb" alt="img">
                                <div>
                                    <div class="fw-bold">${v.code}
                                        <span class="badge bg-light text-dark border ms-1">Size: ${v.size}</span>
                                    </div>
                                    <div class="text-muted small">${v.name}</div>
                                    <div class="small">
                                        Rs ${money(v.price)} × <b>${v.qty}</b> =
                                        <b>Rs ${money(Number(v.price)*Number(v.qty))}</b>
                                        <span class="text-muted"> (Max ${v.maxQty})</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-dark qty-btn" onclick="qtyDown('${v.key}')">-</button>
                                <input type="number" class="form-control qty-input" style="width:90px;"
                                       min="1" max="${v.maxQty}" value="${v.qty}"
                                       onchange="qtySet('${v.key}', this.value)">
                                <button type="button" class="btn btn-outline-dark qty-btn" onclick="qtyUp('${v.key}')">+</button>

                                <button type="button" class="btn btn-danger" onclick="removeLine('${v.key}')">Remove</button>
                            </div>
                        </div>

                        <input type="hidden" name="lines[${v.key}][hire_item_id]" value="${v.item_id}">
                        <input type="hidden" name="lines[${v.key}][size]" value="${v.size}">
                        <input type="hidden" name="lines[${v.key}][qty]" value="${v.qty}" id="hiddenQty-${v.key}">
                    </div>
                `;
            });

            updateSummary();
        }

        window.removeLine = function(key){
            selected.delete(key);
            renderSelected();
        }

        window.qtySet = function(key, val){
            const line = selected.get(key);
            if(!line) return;

            let q = parseInt(val || 1, 10);
            if(isNaN(q) || q < 1) q = 1;
            if(q > line.maxQty) q = line.maxQty;

            line.qty = q;
            selected.set(key, line);

            const hidden = document.getElementById('hiddenQty-' + key);
            if(hidden) hidden.value = q;

            renderSelected();
        }

        window.qtyUp = function(key){
            const line = selected.get(key);
            if(!line) return;
            if(line.qty < line.maxQty){
                line.qty++;
                selected.set(key, line);
            }
            renderSelected();
        }

        window.qtyDown = function(key){
            const line = selected.get(key);
            if(!line) return;
            if(line.qty > 1){
                line.qty--;
                selected.set(key, line);
            }
            renderSelected();
        }

        // Click item to add (SweetAlert)
        document.querySelectorAll('.item-wrap').forEach(wrap => {
            wrap.addEventListener('click', () => {
                const variants = JSON.parse(wrap.dataset.variants || '[]').filter(v => (v.qty || 0) > 0);

                if(variants.length === 0){
                    Swal.fire('No Stock', 'No available sizes/qty for this item.', 'warning');
                    return;
                }

                const optionsHtml = variants.map(v => {
                    // for edit: if user adds new line, max is just current stock
                    return `<option value="${v.size}" data-max="${v.qty}">${v.size} (Stock: ${v.qty})</option>`;
                }).join('');

                Swal.fire({
                    title: 'Select Size & Qty',
                    html: `
                        <div class="text-start">
                            <div class="mb-2"><b>${wrap.dataset.codeOriginal}</b> - ${wrap.dataset.nameOriginal}</div>

                            <label class="form-label mb-1">Size</label>
                            <select id="swalSize" class="form-select mb-2">${optionsHtml}</select>

                            <label class="form-label mb-1">Qty</label>
                            <input id="swalQty" type="number" class="form-control" min="1" value="1">
                            <div class="muted-help mt-2">Qty cannot exceed stock.</div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const sizeEl = document.getElementById('swalSize');
                        const qtyEl  = document.getElementById('swalQty');

                        const size = sizeEl.value;
                        const max  = parseInt(sizeEl.selectedOptions[0].dataset.max || "0", 10);
                        let qty    = parseInt(qtyEl.value || "1", 10);

                        if(!size) return Swal.showValidationMessage('Please select a size');
                        if(isNaN(qty) || qty < 1) qty = 1;
                        if(qty > max) return Swal.showValidationMessage('Qty exceeds available stock');

                        return { size, qty, max };
                    }
                }).then(r => {
                    if(!r.isConfirmed) return;

                    const { size, qty, max } = r.value;

                    const key = `${wrap.dataset.id}__${size}`;
                    const price = Number(wrap.dataset.price || 0);

                    // if exists already, increase but do not exceed maxQty rule:
                    // existing maxQty already includes its previous qty only for initial lines
                    if(selected.has(key)){
                        const existing = selected.get(key);
                        // max for edit line should be stock + originalSelectedQtyForThatLine
                        const maxQty = existing.maxQty; // keep computed one
                        const newQty = Math.min(existing.qty + qty, maxQty);
                        existing.qty = newQty;
                        selected.set(key, existing);
                    } else {
                        selected.set(key, {
                            key,
                            item_id: Number(wrap.dataset.id),
                            size: size,
                            qty: qty,
                            maxQty: max, // new added line max = current stock
                            price: price,
                            deposit: Number(wrap.dataset.deposit || 0),
                            code: wrap.dataset.codeOriginal,
                            name: wrap.dataset.nameOriginal,
                            thumb: wrap.dataset.thumb,
                        });
                    }

                    renderSelected();
                });
            });
        });

        // Filters
        const searchEl = document.getElementById('itemSearch');
        const catEl = document.getElementById('categoryFilter');

        function applyFilter() {
            const q = (searchEl.value || '').trim().toLowerCase();
            const cat = (catEl.value || '').trim().toLowerCase();

            document.querySelectorAll('.item-wrap').forEach(wrap => {
                const text = `${wrap.dataset.code} ${wrap.dataset.name} ${wrap.dataset.category}`;
                const matchesQ = !q || text.includes(q);
                const matchesCat = !cat || wrap.dataset.category === cat;
                wrap.style.display = (matchesQ && matchesCat) ? '' : 'none';
            });
        }
        searchEl.addEventListener('input', applyFilter);
        catEl.addEventListener('change', applyFilter);

        // Clear selection
        document.getElementById('clearSelection').addEventListener('click', () => {
            selected.clear();
            renderSelected();
        });

        // Submit (AJAX)
        document.getElementById('agreementForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (selected.size === 0) {
                Swal.fire('Select Items', 'Please select at least one item with size & qty.', 'warning');
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

                msg.innerHTML = `<div class="alert alert-success">${data.message || 'Agreement updated.'}</div>`;
                setTimeout(() => window.location.href = "{{ route('hiring.agreements.show', $agreement->id) }}", 900);
            }).catch(() => {
                document.getElementById('message').innerHTML = `<div class="alert alert-danger">Network error. Please try again.</div>`;
            });
        });

        // Init selected map from agreement items
        function initSelected(){
            initialLines.forEach(l => {
                if(!l.size) return; // safety
                const maxQty = computeMaxQty(l.item_id, l.size, l.qty);
                selected.set(l.key, { ...l, maxQty });
            });
            renderSelected();
        }

        initSelected();
    </script>
@endsection