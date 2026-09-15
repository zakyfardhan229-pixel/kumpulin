<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex items-center gap-2 text-slate-500">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <p class="text-xs font-semibold uppercase tracking-wide">Sessions</p>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['sessions'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex items-center gap-2 text-amber-600">
                <i data-lucide="clock-3" class="w-4 h-4"></i>
                <p class="text-xs font-semibold uppercase tracking-wide">Menunggu Review</p>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex items-center gap-2 text-slate-500">
                <i data-lucide="loader-circle" class="w-4 h-4"></i>
                <p class="text-xs font-semibold uppercase tracking-wide">Diproses AI</p>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex items-center gap-2 text-green-600">
                <i data-lucide="circle-check" class="w-4 h-4"></i>
                <p class="text-xs font-semibold uppercase tracking-wide">Selesai</p>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex items-center gap-2 text-orange-500">
                <i data-lucide="triangle-alert" class="w-4 h-4"></i>
                <p class="text-xs font-semibold uppercase tracking-wide">Perlu Perbaikan</p>
            </div>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['revision'] }}</p>
        </div>
    </div>

    <div class="mt-4 bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <h3 class="font-semibold text-slate-900">Menunggu Review</h3>
            <a href="{{ route('admin.submissions.index', ['status' => 'pending_review']) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700">
                Lihat semua <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        @if ($pending->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada submission yang menunggu review.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($pending as $submission)
                    <li>
                        <a href="{{ route('admin.submissions.show', $submission) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $submission->nama_lengkap }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $submission->session->title }} · <span class="font-mono">{{ $submission->submission_code }}</span></p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-admin-layout>
