<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-mono text-sm font-bold text-slate-900">{{ $submission->submission_code }}</p>
                <h1 class="mt-0.5 font-bold text-lg text-slate-900 leading-tight">{{ $submission->session->title }}</h1>
                <p class="text-sm text-slate-500">{{ $submission->nama_lengkap }} · {{ $submission->kelas }} · {{ $submission->jurusan }}</p>
            </div>
            <x-status-badge :status="$submission->status" class="shrink-0" />
        </div>

        @php
            $headings = [
                'processing' => ['Sedang diproses', 'Tugas kamu sedang dianalisis.'],
                'pending_review' => ['Sedang ditinjau Admin', 'Analisis AI telah selesai dan submission sedang menunggu validasi Admin.'],
                'completed' => ['Tugas Selesai', 'Tugas kamu telah divalidasi oleh Admin.'],
                'incomplete' => ['Tugas Belum Selesai', 'Silakan periksa kembali tugas kamu.'],
                'revision_required' => ['Perlu Perbaikan', 'Terdapat bagian yang perlu diperbaiki.'],
            ];
            [$heading, $subheading] = $headings[$submission->status] ?? ['Status tidak dikenal', ''];

            $stepState = [
                'processing' => ['done', 'active', 'todo', 'todo'],
                'pending_review' => ['done', 'done', 'active', 'todo'],
                'completed' => ['done', 'done', 'done', 'done'],
                'incomplete' => ['done', 'done', 'done', 'done'],
                'revision_required' => ['done', 'done', 'done', 'attention'],
            ][$submission->status] ?? ['todo', 'todo', 'todo', 'todo'];

            $steps = [
                ['label' => 'Tugas Dikumpulkan', 'icon' => 'upload'],
                ['label' => 'AI Memproses', 'icon' => 'sparkles'],
                ['label' => 'Review Admin', 'icon' => 'clipboard-check'],
                ['label' => match ($submission->status) {
                    'incomplete' => 'Belum Selesai',
                    'revision_required' => 'Perlu Perbaikan',
                    default => 'Selesai',
                }, 'icon' => match ($submission->status) {
                    'incomplete' => 'circle-x',
                    'revision_required' => 'triangle-alert',
                    default => 'circle-check',
                }],
            ];
        @endphp

        <div class="mt-4 bg-slate-50 border border-slate-200 rounded-[10px] px-4 py-3">
            <p class="font-semibold text-slate-900">{{ $heading }}</p>
            <p class="text-sm text-slate-500">{{ $subheading }}</p>
        </div>

        @if ($submission->latestReview?->isPublic())
            <div class="mt-3 bg-orange-50 border border-orange-200 rounded-[10px] px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-orange-600 font-semibold">Catatan dari Admin</p>
                <p class="mt-1 text-sm text-slate-700">{{ $submission->latestReview->note }}</p>
            </div>
        @endif

        <ol class="mt-5 space-y-0">
            @foreach ($steps as $i => $step)
                @php
                    $state = $stepState[$i];
                    $isLast = $i === count($steps) - 1;
                    $isFinalBad = $isLast && in_array($submission->status, ['incomplete', 'revision_required'], true);
                    $bubble = match (true) {
                        $state === 'done' && ! $isFinalBad => 'bg-primary-600 text-white border-primary-600',
                        $state === 'done' && $isFinalBad => $submission->status === 'incomplete' ? 'bg-red-600 text-white border-red-600' : 'bg-orange-500 text-white border-orange-500',
                        $state === 'active' => 'bg-white text-primary-600 border-primary-600 ring-4 ring-primary-50',
                        $state === 'attention' => $submission->status === 'incomplete' ? 'bg-white text-red-600 border-red-600 ring-4 ring-red-50' : 'bg-white text-orange-500 border-orange-500 ring-4 ring-orange-50',
                        default => 'bg-white text-slate-300 border-slate-200',
                    };
                    $labelClass = in_array($state, ['done', 'active', 'attention'], true) ? 'text-slate-900 font-semibold' : 'text-slate-400';
                @endphp
                <li class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span class="w-9 h-9 rounded-full border-2 flex items-center justify-center {{ $bubble }}">
                            <i data-lucide="{{ $step['icon'] }}" class="w-4 h-4"></i>
                        </span>
                        @unless ($isLast)
                            <span class="w-0.5 flex-1 {{ $state === 'done' ? 'bg-primary-600' : 'bg-slate-200' }}"></span>
                        @endunless
                    </div>
                    <p class="pt-2 pb-6 text-sm {{ $labelClass }}">{{ $step['label'] }}</p>
                </li>
            @endforeach
        </ol>

        <p class="text-xs text-slate-400">Dikumpulkan {{ $submission->created_at->format('d M Y, H:i') }}</p>
    </div>
</x-student-layout>
