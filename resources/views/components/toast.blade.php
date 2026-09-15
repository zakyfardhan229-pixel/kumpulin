@if (session('status'))
    <div id="toast" role="status" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-slate-900 text-white text-sm font-medium pl-3 pr-2 py-2 rounded-xl shadow-lg max-w-[calc(100vw-2rem)]">
        <i data-lucide="circle-check" class="w-4 h-4 text-green-400 shrink-0"></i>
        <span class="truncate">{{ session('status') }}</span>
        <button type="button" id="toast-close" class="p-1.5 rounded-lg text-slate-300 hover:bg-slate-700" aria-label="Tutup notifikasi">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <script>
    (function () {
        const toast = document.getElementById('toast');
        if (!toast) return;
        const hide = () => toast.remove();
        document.getElementById('toast-close').addEventListener('click', hide);
        setTimeout(hide, 4000);
    })();
    </script>
@endif
