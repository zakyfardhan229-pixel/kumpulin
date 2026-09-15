<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionStatusController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        $code = trim((string) $request->query('code', ''));

        if ($code !== '') {
            return redirect()->route('student.check.show', strtoupper($code));
        }

        return view('student.check');
    }

    public function show(string $submissionCode): View
    {
        $submission = Submission::with(['session', 'latestReview'])
            ->where('submission_code', strtoupper($submissionCode))
            ->first();

        if (! $submission) {
            return view('student.not-found', ['code' => strtoupper($submissionCode)]);
        }

        return view('student.status', compact('submission'));
    }
}
