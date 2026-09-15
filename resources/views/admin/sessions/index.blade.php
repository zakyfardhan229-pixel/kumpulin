<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Sessions
            </h2>
            <a href="{{ route('admin.sessions.create') }}" class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-[10px]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Session
            </a>
        </div>
    </x-slot>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        @if ($sessions->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center mb-4">
                    <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                </div>
                <h3 class="font-semibold text-slate-900">Belum Ada Session</h3>
                <p class="mt-1 text-sm text-slate-500">Buat session tugas pertama untuk mulai menerima submission.</p>
                <a href="{{ route('admin.sessions.create') }}" class="mt-4 inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-[10px]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Session
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                            <th class="px-4 py-3 font-semibold">Session</th>
                            <th class="px-4 py-3 font-semibold hidden sm:table-cell">Subject</th>
                            <th class="px-4 py-3 font-semibold hidden md:table-cell">Tanggal</th>
                            <th class="px-4 py-3 font-semibold hidden lg:table-cell">Submission</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold hidden md:table-cell">SoT</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($sessions as $session)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.sessions.show', $session) }}" class="font-semibold text-slate-900 hover:text-primary-600">{{ $session->title }}</a>
                                    <p class="font-mono text-xs text-slate-500">{{ $session->session_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">{{ $session->subject }}</td>
                                <td class="px-4 py-3 text-slate-600 hidden md:table-cell">{{ $session->assignment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 hidden lg:table-cell">
                                    {{ $session->submissions_count }}
                                    @if ($session->pending_submissions_count > 0)
                                        <span class="text-amber-600 font-medium">({{ $session->pending_submissions_count }} menunggu)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$session->status" /></td>
                                <td class="px-4 py-3 text-slate-600 hidden md:table-cell">v{{ $session->currentSourceOfTruth?->version ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.sessions.show', $session) }}" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 font-medium">Buka <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
