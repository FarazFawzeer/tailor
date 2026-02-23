<?php

namespace App\Http\Controllers\Tailoring;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobBatch;
use App\Models\JobBatchItem;
use App\Models\Tailoring\ItemMeasurementSet;
use App\Models\Tailoring\ItemMeasurementValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeasurementEntryController extends Controller
{
    public function edit(Job $job, JobBatch $batch, JobBatchItem $item)
    {
        if ($batch->job_id !== $job->id) abort(404);
        if ($item->job_batch_id !== $batch->id) abort(404);

        $item->load(['dressType', 'measurementTemplate.fields' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        if (!$item->measurementTemplate) {
            return view('tailoring.measurements.no_template', compact('job', 'batch', 'item'));
        }

        // Existing saved sets + values
        $sets = ItemMeasurementSet::query()
            ->where('job_batch_item_id', $item->id)
            ->with(['values'])
            ->get();

        // existing map: key => [field_id => value]
        $existing = [];
        foreach ($sets as $set) {
            $key = $set->piece_no === null ? 'same' : (string)$set->piece_no;

            $existing[$key] = [
                '_notes' => $set->notes,
                '_set_id' => $set->id,
            ];

            foreach ($set->values as $v) {
                $existing[$key][$v->measurement_field_id] = $v->value;
            }
        }

        $fields = $item->measurementTemplate->fields;

        return view('tailoring.measurements.edit', compact('job', 'batch', 'item', 'fields', 'existing'));
    }

    public function store(Request $request, Job $job, JobBatch $batch, JobBatchItem $item)
    {
        if ($batch->job_id !== $job->id) abort(404);
        if ($item->job_batch_id !== $batch->id) abort(404);

        $item->load(['measurementTemplate.fields' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        if (!$item->measurementTemplate) {
            return response()->json(['success' => false, 'message' => 'Measurement template not selected for this item.'], 422);
        }

        $fields = $item->measurementTemplate->fields;
        $fieldIds = $fields->pluck('id')->all();

        $data = $request->validate([
            'measurements' => ['required', 'array'], // measurements[same|1..N][field_id] => value
            'notes' => ['nullable', 'array'],
        ]);

        $measurements = $data['measurements'];
        $notes = $data['notes'] ?? [];

        // Validate required sets
        if (!$item->per_piece_measurement) {
            if (!isset($measurements['same']) || !is_array($measurements['same'])) {
                return response()->json(['success' => false, 'message' => 'Please enter same measurements.'], 422);
            }
        } else {
            for ($p = 1; $p <= $item->qty; $p++) {
                $k = (string)$p;
                if (!isset($measurements[$k]) || !is_array($measurements[$k])) {
                    return response()->json(['success' => false, 'message' => "Please enter measurements for Piece {$p}."], 422);
                }
            }
        }

        // Optional: validate required fields are not empty (strict)
        foreach ($fields as $f) {
            if (!$f->is_required) continue;

            if (!$item->per_piece_measurement) {
                $v = $measurements['same'][$f->id] ?? null;
                if ($v === null || $v === '') {
                    return response()->json(['success' => false, 'message' => "{$f->label} is required."], 422);
                }
            } else {
                for ($p = 1; $p <= $item->qty; $p++) {
                    $k = (string)$p;
                    $v = $measurements[$k][$f->id] ?? null;
                    if ($v === null || $v === '') {
                        return response()->json(['success' => false, 'message' => "{$f->label} is required for Piece {$p}."], 422);
                    }
                }
            }
        }

        DB::transaction(function () use ($item, $measurements, $notes, $fieldIds) {

            if (!$item->per_piece_measurement) {
                // SAME
                $values = $measurements['same'];

                $set = ItemMeasurementSet::updateOrCreate(
                    ['job_batch_item_id' => $item->id, 'piece_no' => null],
                    ['captured_by' => auth()->id(), 'notes' => $notes['same'] ?? null]
                );

                foreach ($fieldIds as $fid) {
                    $val = $values[$fid] ?? null;
                    $val = ($val === '') ? null : $val;

                    ItemMeasurementValue::updateOrCreate(
                        ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                        ['value' => $val]
                    );
                }

            } else {
                // PER PIECE
                for ($p = 1; $p <= $item->qty; $p++) {
                    $k = (string)$p;
                    $values = $measurements[$k];

                    $set = ItemMeasurementSet::updateOrCreate(
                        ['job_batch_item_id' => $item->id, 'piece_no' => $p],
                        ['captured_by' => auth()->id(), 'notes' => $notes[$k] ?? null]
                    );

                    foreach ($fieldIds as $fid) {
                        $val = $values[$fid] ?? null;
                        $val = ($val === '') ? null : $val;

                        ItemMeasurementValue::updateOrCreate(
                            ['item_measurement_set_id' => $set->id, 'measurement_field_id' => $fid],
                            ['value' => $val]
                        );
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Measurements saved successfully',
        ]);
    }
}