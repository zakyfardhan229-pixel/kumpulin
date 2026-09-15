<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ValidateSubmissionWithAI;
use App\Models\AssignmentSession;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $submissions = Submission::query()
            ->with(['session', 'latestAiValidation', 'latestReview'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('session_id'), fn ($query, $sessionId) => $query->where('assignment_session_id', $sessionId))
            ->when($request->string('search')->trim()->toString(), fn ($query, $search) => $query->where(
                fn ($query) => $query->where('submission_code', 'like', "%{$search}%")->orWhere('nama_lengkap', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sessions = AssignmentSession::orderByDesc('id')->get(['id', 'title']);

        return view('admin.submissions.index', compact('submissions', 'sessions'));
    }

    public function show(Submission $submission): View
    {
        $submission->load(['session', 'files', 'aiValidations', 'latestAiValidation', 'adminReviews.admin', 'latestReview']);

        return view('admin.submissions.show', compact('submission'));
    }

    public function retry(Submission $submission): RedirectResponse
    {
        ValidateSubmissionWithAI::dispatch($submission->id);

        return back()->with('status', 'Analisis AI dijalankan ulang.');
    }

    public function file(SubmissionFile $file): BinaryFileResponse
    {
        abort_unless($file->disk === 'local', 404);

        $disk = Storage::disk($file->disk);

        abort_unless($disk->exists($file->path), 404);

        return response()->file($disk->path($file->path), [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="'.$file->original_name.'"',
        ]);
    }
}
