<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8 text-center">
        <div class="mx-auto w-14 h-14 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
            <i data-lucide="lock" class="w-7 h-7"></i>
        </div>
        <h1 class="mt-4 font-bold text-xl text-slate-900">Session Ditutup</h1>
        <p class="mt-1 text-sm text-slate-500">Pengumpulan tugas untuk <strong>{{ $session->title }}</strong> sudah ditutup.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
    </div>
</x-student-layout>
