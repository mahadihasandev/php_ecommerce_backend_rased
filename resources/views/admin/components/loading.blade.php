<div id="admin-loading" hidden class="bg-slate-950 p-6 lg:p-8">
    <div role="status" aria-live="polite" aria-atomic="true" class="mb-6 flex items-center gap-2.5 text-sm font-medium text-brand-400">
        <span class="h-2 w-2 rounded-full bg-brand-400 motion-safe:animate-pulse" aria-hidden="true"></span>
        <span data-loading-status>Loading dashboard…</span>
    </div>
    <div data-loading-recovery hidden class="mb-6 flex flex-wrap items-center gap-3 text-xs text-slate-400">
        <span>This is taking longer than usual.</span>
        <button type="button" data-loading-cancel class="rounded-lg border border-slate-700 px-3 py-2 text-slate-200 hover:bg-slate-800">Return to this page</button>
    </div>
    <div aria-hidden="true">
        @include('admin.components.skeleton')
    </div>
</div>
