<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssignmentSessionRequest;
use App\Http\Requests\Admin\UpdateAssignmentSessionRequest;
use App\Models\AssignmentSession;
use App\Models\Submission;
use App\Services\SessionCodeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentSessionController extends Controller
{
    public function index(): View
    {
        $sessions = AssignmentSession::query()
            ->with('currentSourceOfTruth')
            ->withCount([
                'submissions',
                'submissions as pending_submissions_count' => fn ($query) => $query->where('status', Submission::STATUS_PENDING_REVIEW),
            ])
            ->latest()
            ->paginate(15);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('admin.sessions.create');
    }

    public function store(StoreAssignmentSessionRequest $request, SessionCodeService $codes): RedirectResponse
    {
        $data = $request->validated();

        $session = DB::transaction(function () use ($data, $codes, $request) {
            $session = AssignmentSession::create([
                'session_code' => $codes->generate($data['subject'], Carbon::parse($data['assignment_date'])),
                'title' => $data['title'],
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'assignment_date' => $data['assignment_date'],
                'status' => AssignmentSession::STATUS_ACTIVE,
                'created_by' => $request->user()->id,
            ]);

            $session->sourceOfTruths()->create([
                'version' => 1,
                'questions' => $data['questions'],
            ]);

            return $session;
        });

        return redirect()
            ->route('admin.sessions.show', $session)
            ->with('status', 'Session berhasil dibuat.');
    }

    public function show(AssignmentSession $session): View
    {
        $session->load(['currentSourceOfTruth', 'sourceOfTruths']);
        $stats = [
            'total' => $session->submissions()->count(),
            'pending' => $session->submissions()->where('status', Submission::STATUS_PENDING_REVIEW)->count(),
            'processing' => $session->submissions()->where('status', Submission::STATUS_PROCESSING)->count(),
            'completed' => $session->submissions()->where('status', Submission::STATUS_COMPLETED)->count(),
            'incomplete' => $session->submissions()->where('status', Submission::STATUS_INCOMPLETE)->count(),
            'revision' => $session->submissions()->where('status', Submission::STATUS_REVISION_REQUIRED)->count(),
        ];

        return view('admin.sessions.show', compact('session', 'stats'));
    }

    public function edit(AssignmentSession $session): View
    {
        $session->load('currentSourceOfTruth');

        return view('admin.sessions.edit', compact('session'));
    }

    public function update(UpdateAssignmentSessionRequest $request, AssignmentSession $session): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $session) {
            $session->update([
                'title' => $data['title'],
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'assignment_date' => $data['assignment_date'],
                'status' => $data['status'] ?? $session->status,
            ]);

            $current = $session->currentSourceOfTruth;

            if ($this->questionsChanged($current?->questions, $data['questions'])) {
                $session->sourceOfTruths()->create([
                    'version' => ($current?->version ?? 0) + 1,
                    'questions' => $data['questions'],
                ]);
            }
        });

        return redirect()
            ->route('admin.sessions.show', $session->refresh())
            ->with('status', 'Session berhasil diperbarui.');
    }

    public function close(AssignmentSession $session): RedirectResponse
    {
        if (! $session->isActive()) {
            return back()->with('status', 'Session sudah tidak aktif.');
        }

        $session->update(['status' => AssignmentSession::STATUS_CLOSED]);

        return back()->with('status', 'Session berhasil ditutup.');
    }

    /**
     * @param  array<int, mixed>|null  $current
     * @param  array<int, mixed>  $incoming
     */
    private function questionsChanged(?array $current, array $incoming): bool
    {
        return $this->normalize($current ?? []) !== $this->normalize($incoming);
    }

    /** @param  array<int, mixed>  $questions */
    private function normalize(array $questions): string
    {
        return json_encode(array_values($questions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
