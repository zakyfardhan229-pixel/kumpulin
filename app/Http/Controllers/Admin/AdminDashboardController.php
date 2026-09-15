<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSession;
use App\Models\Submission;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'sessions' => AssignmentSession::count(),
            'pending' => Submission::where('status', Submission::STATUS_PENDING_REVIEW)->count(),
            'processing' => Submission::where('status', Submission::STATUS_PROCESSING)->count(),
            'completed' => Submission::where('status', Submission::STATUS_COMPLETED)->count(),
            'revision' => Submission::where('status', Submission::STATUS_REVISION_REQUIRED)->count(),
        ];

        $pending = Submission::with('session')
            ->where('status', Submission::STATUS_PENDING_REVIEW)
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'pending'));
    }
}
