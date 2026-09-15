<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminReviewRequest;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminReviewController extends Controller
{
    public function store(StoreAdminReviewRequest $request, Submission $submission): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $submission) {
            $submission->adminReviews()->create([
                'admin_id' => $request->user()->id,
                'decision' => $data['decision'],
                'note' => $data['note'] ?? null,
                'reviewed_at' => now(),
            ]);

            // The final status changes only here — AI results are stored
            // separately and are never overwritten by the admin decision.
            $submission->update(['status' => $data['decision']]);
        });

        Log::info('Admin review created.', ['submission_id' => $submission->id]);

        return back()->with('status', 'Keputusan berhasil disimpan.');
    }
}
