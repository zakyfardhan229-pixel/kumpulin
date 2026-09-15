<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubmissionRequest;
use App\Jobs\ValidateSubmissionWithAI;
use App\Models\AssignmentSession;
use App\Models\Submission;
use App\Services\SubmissionCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SubmissionController extends Controller
{
    public function create(string $sessionCode): View
    {
        $session = AssignmentSession::where('session_code', $sessionCode)->firstOrFail();

        if (! $session->isActive()) {
            return view('student.closed', compact('session'));
        }

        return view('student.submit', compact('session'));
    }

    public function store(StoreSubmissionRequest $request, string $sessionCode, SubmissionCodeService $codes): RedirectResponse|View
    {
        $session = AssignmentSession::where('session_code', $sessionCode)->firstOrFail();

        if (! $session->isActive()) {
            return view('student.closed', compact('session'));
        }

        $data = $request->validated();

        $duplicate = Submission::where('assignment_session_id', $session->id)
            ->where('nama_lengkap', $data['nama_lengkap'])
            ->where('kelas', $data['kelas'])
            ->where('jurusan', $data['jurusan'])
            ->exists();

        if ($duplicate && ! ($data['confirm_resubmit'] ?? false)) {
            return back()
                ->withInput($request->except('file'))
                ->with('duplicate_warning', true);
        }

        $file = $request->file('file');
        $path = $file->store('submissions/'.$session->session_code, 'local');

        try {
            $submission = DB::transaction(function () use ($data, $session, $codes, $file, $path) {
                $submission = Submission::create([
                    'assignment_session_id' => $session->id,
                    'submission_code' => $codes->generate(),
                    'nama_lengkap' => $data['nama_lengkap'],
                    'kelas' => $data['kelas'],
                    'jurusan' => $data['jurusan'],
                    'catatan' => $data['catatan'] ?? null,
                    'status' => Submission::STATUS_PROCESSING,
                ]);

                $submission->files()->create([
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                return $submission;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }

        Log::info('Submission created.', ['submission_id' => $submission->id]);

        ValidateSubmissionWithAI::dispatch($submission->id);

        return redirect()->route('student.success', $submission->submission_code);
    }

    public function success(string $submissionCode): View
    {
        $submission = Submission::where('submission_code', $submissionCode)->firstOrFail();

        return view('student.success', compact('submission'));
    }
}
