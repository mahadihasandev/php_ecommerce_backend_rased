<div id="admin-skeleton-wrapper" class="space-y-8 animate-fade-in">
    <!-- Live Loading Status Pill -->
    <div class="flex items-center justify-between">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold shadow-xs">
            <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
            <span id="skeleton-status-text">Loading live store analytics & catalog metrics...</span>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span>Sub-millisecond cache synchronized</span>
        </div>
    </div>

    <!-- 1. DASHBOARD OVERVIEW SKELETON -->
    <div id="skeleton-dashboard-view" class="space-y-8">
        <!-- Hero / Welcome Banner Skeleton -->
        <div class="relative overflow-hidden rounded-3xl bg-slate-900/90 border border-slate-800/80 p-8 shadow-2xl">
            <div class="max-w-2xl space-y-4">
                <div class="h-6 w-44 rounded-full bg-slate-800/80 shimmer"></div>
                <div class="h-9 w-3/4 rounded-2xl bg-slate-800/80 shimmer"></div>
                <div class="h-4 w-full max-w-lg rounded-lg bg-slate-800/60 shimmer"></div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        <!-- Stats Grid Skeleton (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @for($i = 0; $i < 4; $i++)
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div class="h-3.5 w-24 rounded-md bg-slate-800 shimmer"></div>
                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 shimmer"></div>
                </div>
                <div class="space-y-2 pt-1">
                    <div class="h-8 w-28 rounded-xl bg-slate-800 shimmer"></div>
                    <div class="h-3 w-36 rounded-md bg-slate-800/60 shimmer"></div>
                </div>
            </div>
            @endfor
        </div>

        <!-- Quick Actions Bar Skeleton -->
        <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-sm space-y-4">
            <div class="h-3.5 w-48 rounded-md bg-slate-800 shimmer"></div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/50 border border-slate-700/40">
                    <div class="w-8 h-8 rounded-lg bg-slate-700/60 shimmer shrink-0"></div>
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="h-3.5 w-20 rounded bg-slate-700/70 shimmer"></div>
                        <div class="h-2.5 w-24 rounded bg-slate-800 shimmer"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Two Column Layout: Recent Orders & Recent Products Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders Skeleton -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 shimmer"></div>
                        <div class="h-4 w-36 rounded-lg bg-slate-800 shimmer"></div>
                    </div>
                    <div class="h-3 w-16 rounded bg-slate-800 shimmer"></div>
                </div>

                <div class="space-y-3 pt-2">
                    @for($i = 0; $i < 5; $i++)
                    <div class="flex items-center justify-between py-2 border-b border-slate-800/40">
                        <div class="h-4 w-20 rounded bg-slate-800 shimmer"></div>
                        <div class="h-3.5 w-28 rounded bg-slate-800/70 shimmer"></div>
                        <div class="h-4 w-16 rounded bg-slate-800 shimmer"></div>
                        <div class="h-5 w-16 rounded-full bg-slate-800/80 shimmer"></div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Recent Products Skeleton -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 shimmer"></div>
                        <div class="h-4 w-40 rounded-lg bg-slate-800 shimmer"></div>
                    </div>
                    <div class="h-3 w-16 rounded bg-slate-800 shimmer"></div>
                </div>

                <div class="space-y-3 pt-2">
                    @for($i = 0; $i < 4; $i++)
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-950/40 border border-slate-800/60">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-slate-800 shimmer shrink-0"></div>
                            <div class="space-y-1.5">
                                <div class="h-3.5 w-36 rounded bg-slate-800 shimmer"></div>
                                <div class="h-2.5 w-20 rounded bg-slate-800/70 shimmer"></div>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-slate-800/60 shimmer shrink-0"></div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Best Sellers Showcase Skeleton -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm space-y-5">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 shimmer"></div>
                    <div class="space-y-1">
                        <div class="h-4 w-32 rounded bg-slate-800 shimmer"></div>
                        <div class="h-2.5 w-48 rounded bg-slate-800/50 shimmer"></div>
                    </div>
                </div>
                <div class="h-3 w-20 rounded bg-slate-800 shimmer"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @for($i = 0; $i < 5; $i++)
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="h-5 w-8 rounded-full bg-slate-800 shimmer"></div>
                        <div class="h-5 w-16 rounded-md bg-slate-800/70 shimmer"></div>
                    </div>
                    <div class="aspect-square w-full rounded-xl bg-slate-900 border border-slate-800/60 shimmer"></div>
                    <div class="space-y-1.5">
                        <div class="h-3.5 w-full rounded bg-slate-800 shimmer"></div>
                        <div class="h-3 w-16 rounded bg-slate-800/70 shimmer"></div>
                    </div>
                    <div class="h-7 w-full rounded-lg bg-slate-800 shimmer"></div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- 2. TABLE / CATALOG SKELETON (Products, Orders, Categories, Brands, Users) -->
    <div id="skeleton-table-view" class="hidden space-y-6">
        <!-- Filter Bar Skeleton -->
        <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="h-6 w-44 rounded-lg bg-slate-800 shimmer"></div>
                    <div class="h-3.5 w-72 rounded bg-slate-800/60 shimmer"></div>
                </div>
                <div class="h-10 w-36 rounded-xl bg-brand-600/30 shimmer"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-800/80">
                <div class="sm:col-span-2 h-10 rounded-xl bg-slate-950/60 border border-slate-800/80 shimmer"></div>
                <div class="h-10 rounded-xl bg-slate-950/60 border border-slate-800/80 shimmer"></div>
                <div class="h-10 rounded-xl bg-slate-950/60 border border-slate-800/80 shimmer"></div>
            </div>
        </div>

        <!-- Table Skeleton -->
        <div class="bg-slate-900/80 rounded-3xl border border-slate-800/80 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="p-4 border-b border-slate-800/80 flex items-center justify-between bg-slate-950/40">
                <div class="h-4 w-32 rounded bg-slate-800 shimmer"></div>
                <div class="h-4 w-20 rounded bg-slate-800/60 shimmer"></div>
            </div>
            <div class="divide-y divide-slate-800/60">
                @for($i = 0; $i < 6; $i++)
                <div class="p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 shimmer shrink-0"></div>
                        <div class="space-y-2 flex-1 min-w-0">
                            <div class="h-4 w-48 rounded bg-slate-800 shimmer"></div>
                            <div class="h-3 w-32 rounded bg-slate-800/60 shimmer"></div>
                        </div>
                    </div>
                    <div class="hidden sm:block h-6 w-20 rounded-full bg-slate-800/70 shimmer"></div>
                    <div class="h-4 w-16 rounded bg-slate-800 shimmer"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 shimmer"></div>
                        <div class="w-8 h-8 rounded-lg bg-slate-800 shimmer"></div>
                    </div>
                </div>
                @endfor
            </div>
            <div class="p-4 border-t border-slate-800/80 flex items-center justify-between bg-slate-950/40">
                <div class="h-4 w-28 rounded bg-slate-800 shimmer"></div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-20 rounded-lg bg-slate-800 shimmer"></div>
                    <div class="h-8 w-20 rounded-lg bg-slate-800 shimmer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. FORM / EDITOR SKELETON (Create & Edit views) -->
    <div id="skeleton-form-view" class="hidden space-y-6">
        <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm space-y-2">
            <div class="h-6 w-52 rounded-lg bg-slate-800 shimmer"></div>
            <div class="h-3.5 w-80 rounded bg-slate-800/60 shimmer"></div>
        </div>

        <div class="bg-slate-900/80 rounded-3xl border border-slate-800/80 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <div class="h-3.5 w-24 rounded bg-slate-800 shimmer"></div>
                    <div class="h-11 rounded-xl bg-slate-950/60 border border-slate-800 shimmer"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-3.5 w-24 rounded bg-slate-800 shimmer"></div>
                    <div class="h-11 rounded-xl bg-slate-950/60 border border-slate-800 shimmer"></div>
                </div>
                <div class="sm:col-span-2 space-y-2">
                    <div class="h-3.5 w-32 rounded bg-slate-800 shimmer"></div>
                    <div class="h-28 rounded-xl bg-slate-950/60 border border-slate-800 shimmer"></div>
                </div>
                <div class="sm:col-span-2 space-y-2">
                    <div class="h-3.5 w-36 rounded bg-slate-800 shimmer"></div>
                    <div class="h-32 rounded-2xl border-2 border-dashed border-slate-800 bg-slate-950/40 shimmer flex items-center justify-center"></div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <div class="h-10 w-24 rounded-xl bg-slate-800 shimmer"></div>
                <div class="h-10 w-32 rounded-xl bg-brand-600/50 shimmer"></div>
            </div>
        </div>
    </div>
</div>
