<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8 text-center">
        <div class="mx-auto w-14 h-14 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
            <i data-lucide="circle-check" class="w-8 h-8"></i>
        </div>
        <h1 class="mt-4 font-bold text-xl text-slate-900">Tugas Berhasil Dikumpulkan!</h1>
        <p class="mt-1 text-sm text-slate-500">Simpan ID ini untuk mengecek status tugas kamu.</p>

        <div class="mt-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Submission ID</p>
            <p id="submission-code" class="mt-1 font-mono text-2xl font-bold text-slate-900 tracking-wide">{{ $submission->submission_code }}</p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-2">
            <button type="button" id="copy-code" class="inline-flex items-center justify-center gap-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">
                <i data-lucide="copy" class="w-4 h-4"></i><span>Salin ID</span>
            </button>
            <a href="{{ route('student.check.show', $submission->submission_code) }}" class="inline-flex items-center justify-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-[10px]">
                Cek Status
            </a>
        </div>
    </div>

    <script>
    document.getElementById('copy-code').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const text = document.getElementById('submission-code').textContent.trim();
        try {
            await navigator.clipboard.writeText(text);
        } catch (err) {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
        }
        const label = btn.querySelector('span');
        const original = label.textContent;
        label.textContent = 'Disalin!';
        setTimeout(() => { label.textContent = original; }, 1500);
    });
    </script>
</x-student-layout>
