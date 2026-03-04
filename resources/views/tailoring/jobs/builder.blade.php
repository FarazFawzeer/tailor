@extends('layouts.vertical', ['subtitle' => 'Create Job (Easy)'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Tailoring Jobs', 'subtitle' => 'Create (Easy Mode)'])

<div class="row g-3">
    {{-- LEFT: Steps --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create Job — All in One Screen</h5>
                <small class="text-muted">Step 1 → Step 2 → Step 3. No confusing page switching.</small>
            </div>

            <div class="card-body">
                <div id="msg"></div>

                {{-- STEP 1 --}}
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Step 1: Customer & Job Details</h6>
                        <span class="badge bg-primary">Step 1</span>
                    </div>

                    <form id="jobForm" action="{{ route('tailoring.jobs.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">Job Date</label>
                                <input type="date" name="job_date" class="form-control" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">
                            ✅ Create Job
                        </button>
                    </form>
                </div>

                {{-- STEP 2 --}}
                <div class="border rounded p-3 mb-3 opacity-50" id="step2Box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Step 2: Add Batch</h6>
                        <span class="badge bg-primary">Step 2</span>
                    </div>

                    <form id="batchForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Batch Date</label>
                                <input type="date" name="batch_date" class="form-control" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <button class="btn btn-outline-primary w-100" type="submit">
                            ➕ Add Batch
                        </button>
                    </form>
                </div>

                {{-- STEP 3 --}}
                <div class="border rounded p-3 opacity-50" id="step3Box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Step 3: Add Items</h6>
                        <span class="badge bg-primary">Step 3</span>
                    </div>

                    <div class="alert alert-info py-2 small mb-2">
                        Select a batch from the right side, then add items here.
                    </div>

                    <form id="itemForm">
                        @csrf
                        <input type="hidden" name="batch_id" id="activeBatchId">

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Dress Type <span class="text-danger">*</span></label>
                                <select name="dress_type_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($dressTypes as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Template</label>
                                <select name="measurement_template_id" class="form-select">
                                    <option value="">Select</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}">{{ $t->dressType?->name }} - {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label">Qty <span class="text-danger">*</span></label>
                                <input type="number" name="qty" class="form-control" value="1" min="1" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label d-block">Per Piece?</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="per_piece_measurement" value="1">
                                    <label class="form-check-label">Yes</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional">
                        </div>

                        <button class="btn btn-success w-100" type="submit">
                            ✅ Add Item to Selected Batch
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- RIGHT: Batches + Items preview --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Batches & Items</h6>
                <small class="text-muted">Click a batch to select it, then add items.</small>
            </div>

            <div class="card-body">
                <div id="jobInfo" class="alert alert-secondary py-2 small">
                    No job created yet.
                </div>

                <div id="batchesList" class="d-grid gap-2"></div>

                <hr>

                <div id="itemsList" class="small text-muted">
                    No batch selected.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Simple modal for Measurements --}}
<div class="modal fade" id="measureModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Enter Measurements</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="measureModalBody">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
    let jobId = null;
    let selectedBatchId = null;
    let batches = {}; // batchId => {items:[]}

    function toast(type, text){
        document.getElementById('msg').innerHTML = `<div class="alert alert-${type}">${text}</div>`;
        setTimeout(()=>document.getElementById('msg').innerHTML='', 2500);
    }

    function unlockStep2(){
        document.getElementById('step2Box').classList.remove('opacity-50');
        document.getElementById('batchForm').querySelectorAll('input,button').forEach(el=>el.disabled=false);
    }

    function unlockStep3(){
        document.getElementById('step3Box').classList.remove('opacity-50');
        document.getElementById('itemForm').querySelectorAll('input,select,button').forEach(el=>el.disabled=false);
    }

    // Disable step2+3 initially
    document.querySelectorAll('#batchForm input,#batchForm button').forEach(el=>el.disabled=true);
    document.querySelectorAll('#itemForm input,#itemForm select,#itemForm button').forEach(el=>el.disabled=true);

    function renderBatches(){
        const box = document.getElementById('batchesList');
        box.innerHTML = '';

        Object.values(batches).forEach(b=>{
            const active = (b.id === selectedBatchId) ? 'btn-primary' : 'btn-outline-primary';
            const btn = document.createElement('button');
            btn.className = `btn ${active} text-start`;
            btn.innerHTML = `<b>${b.batch_no}</b><br><small>${b.batch_date ?? ''} | Due: ${b.due_date ?? '-'}</small>`;
            btn.onclick = () => {
                selectedBatchId = b.id;
                document.getElementById('activeBatchId').value = selectedBatchId;
                renderBatches();
                renderItems();
                unlockStep3();
            };
            box.appendChild(btn);
        });

        if(Object.keys(batches).length === 0){
            box.innerHTML = `<div class="text-muted small">No batches yet.</div>`;
        }
    }

    function renderItems(){
        const box = document.getElementById('itemsList');
        if(!selectedBatchId){
            box.innerHTML = 'No batch selected.';
            return;
        }

        const b = batches[selectedBatchId];
        if(!b || !b.items || b.items.length === 0){
            box.innerHTML = `<div class="text-muted">No items in this batch.</div>`;
            return;
        }

        box.innerHTML = b.items.map(it => {
            const per = it.per_piece_measurement ? '<span class="badge bg-warning">Per Piece</span>' : '<span class="badge bg-success">Same</span>';
            const ms = it.measurements_done ? '<span class="badge bg-success">✅ Saved</span>' : '<span class="badge bg-secondary">Not Saved</span>';

            return `
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <b>${it.dressType?.name ?? 'Dress'}</b> ${per} ${ms}<br>
                            <small class="text-muted">${it.measurementTemplate?.name ?? 'No template'} | Qty: ${it.qty}</small>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-sm btn-dark" onclick="openMeasurements(${it.id})">
                                Measurements
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Step 1: create job
    document.getElementById('jobForm').addEventListener('submit', async function(e){
        e.preventDefault();

        const res = await fetch(this.action, {
            method: "POST",
            body: new FormData(this),
            headers: {"Accept":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"}
        });
        const data = await res.json().catch(()=>({}));
        if(!res.ok){
            toast('danger', data.message || 'Job create failed');
            return;
        }

        jobId = data.data.id;
        document.getElementById('jobInfo').className = 'alert alert-success py-2 small';
        document.getElementById('jobInfo').innerHTML = `✅ Job created: <b>${data.data.job_no ?? ('#'+jobId)}</b>`;

        unlockStep2();
        toast('success', data.message || 'Job created');
    });

    // Step 2: add batch
    document.getElementById('batchForm').addEventListener('submit', async function(e){
        e.preventDefault();
        if(!jobId) return;

        const url = `{{ url('tailoring/jobs') }}/${jobId}/batches`;

        const res = await fetch(url, {
            method: "POST",
            body: new FormData(this),
            headers: {"Accept":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"}
        });

        const data = await res.json().catch(()=>({}));
        if(!res.ok){
            toast('danger', data.message || 'Batch create failed');
            return;
        }

        const b = data.data;
        batches[b.id] = {...b, items: []};
        selectedBatchId = b.id;
        document.getElementById('activeBatchId').value = selectedBatchId;

        renderBatches();
        renderItems();
        unlockStep3();
        toast('success', data.message || 'Batch created');
    });

    // Step 3: add item
    document.getElementById('itemForm').addEventListener('submit', async function(e){
        e.preventDefault();
        if(!jobId || !selectedBatchId){
            toast('warning', 'Please select a batch first.');
            return;
        }

        const url = `{{ url('tailoring/jobs') }}/${jobId}/batches/${selectedBatchId}/items`;

        const res = await fetch(url, {
            method: "POST",
            body: new FormData(this),
            headers: {"Accept":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"}
        });

        const data = await res.json().catch(()=>({}));
        if(!res.ok){
            if(data.errors) toast('danger', Object.values(data.errors).flat().join('<br>'));
            else toast('danger', data.message || 'Item add failed');
            return;
        }

        batches[selectedBatchId].items.push({...data.data, measurements_done:false});
        renderItems();
        toast('success', data.message || 'Item added');
        this.reset();
    });

    // Open measurements in modal (loads your existing edit page)
    async function openMeasurements(itemId){
        const url = `{{ url('tailoring/jobs') }}/${jobId}/batches/${selectedBatchId}/items/${itemId}/measurements`;

        // if your route is different, replace with your actual URL pattern:
        // ex: route('tailoring.measurements.edit', [job,batch,item])
        // but since we are in JS, easiest is URL.

        const modalBody = document.getElementById('measureModalBody');
        modalBody.innerHTML = 'Loading...';

        const r = await fetch(url);
        const html = await r.text();
        modalBody.innerHTML = html;

        const modal = new bootstrap.Modal(document.getElementById('measureModal'));
        modal.show();
    }
</script>
@endsection