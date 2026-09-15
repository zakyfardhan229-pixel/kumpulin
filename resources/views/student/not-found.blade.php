<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8 text-center">
        <div class="mx-auto w-14 h-14 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
            <i data-lucide="search-x" class="w-7 h-7"></i>
        </div>
        <h1 class="mt-4 font-bold text-xl text-slate-900">Submission Tidak Ditemukan</h1>
        <p class="mt-1 text-sm text-slate-500">Tidak ada pengumpulan dengan ID <span class="font-mono font-semibold">{{ $code }}</span>. Periksa kembali ID kamu.</p>
        <a href="{{ route('student.check') }}" class="mt-6 inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-[10px]">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Coba Lagi
        </a>
    </div>
</x-student-layout>
