@props(['status'])

@php
    [$dot, $pill] = match ($status) {
        'active' => ['bg-primary-600', 'bg-primary-50 text-primary-700'],
        'processing', 'pending_review' => ['bg-amber-500', 'bg-amber-50 text-amber-700'],
        'completed' => ['bg-green-600', 'bg-green-50 text-green-700'],
        'incomplete' => ['bg-red-600', 'bg-red-50 text-red-700'],
        'revision_required' => ['bg-orange-500', 'bg-orange-50 text-orange-700'],
        default => ['bg-slate-400', 'bg-slate-100 text-slate-700'],
    };

    $label = match ($status) {
        'active' => 'Aktif',
        'closed' => 'Ditutup',
        'archived' => 'Diarsipkan',
        'processing' => 'Diproses',
        'pending_review' => 'Menunggu Review',
        'completed' => 'Selesai',
        'incomplete' => 'Belum Selesai',
        'revision_required' => 'Perlu Perbaikan',
        default => ucfirst(str_replace('_', ' ', (string) $status)),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {$pill}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
