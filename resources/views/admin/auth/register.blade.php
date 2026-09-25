<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Vendor / Admin - ShopEcommerce</title>
    <!-- Tab Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        const _origWarn = console.warn;
        console.warn = function(...args) {
            if (typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
            _origWarn.apply(console, args);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } [x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full flex items-center justify-center p-6 bg-radial-gradient from-slate-900 via-slate-950 to-black relative overflow-hidden" 
      x-data="{ accountType: 'vendor' }">
    
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10 py-8">
        <!-- Logo and Title -->
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 items-center justify-center shadow-xl shadow-indigo-500/25 mb-3">
                <i data-lucide="user-plus" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                Create Portal Account
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Join the multi-vendor marketplace or register store administration</p>
        </div>

        <!-- Registration Card -->
        <div class="bg-slate-900/80 border border-slate-800/90 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
            
            @if($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                <div class="font-semibold mb-1">Registration failed:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Account Type Switcher -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Account Role</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label @click="accountType = 'vendor'" 
                               :class="accountType === 'vendor' ? 'border-indigo-500 bg-indigo-500/10 text-white ring-1 ring-indigo-500' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700'"
                               class="flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition-all">
                            <input type="radio" name="account_type" value="vendor" x-model="accountType" class="sr-only">
                            <i data-lucide="store" class="w-5 h-5 mb-1.5 text-indigo-400"></i>
                            <span class="text-xs font-bold">Multi-Vendor</span>
                            <span class="text-[10px] text-slate-400 text-center mt-0.5">Sell your own products</span>
                        </label>

                        <label @click="accountType = 'admin'" 
                               :class="accountType === 'admin' ? 'border-purple-500 bg-purple-500/10 text-white ring-1 ring-purple-500' : 'border-slate-800 bg-slate-950/40 text-slate-400 hover:border-slate-700'"
                               class="flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition-all">
                            <input type="radio" name="account_type" value="admin" x-model="accountType" class="sr-only">
                            <i data-lucide="shield-alert" class="w-5 h-5 mb-1.5 text-purple-400"></i>
                            <span class="text-xs font-bold">Store Admin</span>
                            <span class="text-[10px] text-slate-400 text-center mt-0.5">Full marketplace control</span>
                        </label>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Alex Morgan">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="alex@store.com">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Phone Number (Optional)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="+1 (555) 000-0000">
                </div>

                <!-- Vendor Store Fields (Conditional) -->
                <div x-show="accountType === 'vendor'" x-transition class="space-y-4 pt-2 border-t border-slate-800/80">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-indigo-400 mb-1.5">Store / Brand Name</label>
                        <input type="text" name="store_name" value="{{ old('store_name') }}"
                               class="w-full px-4 py-2.5 bg-slate-950/60 border border-indigo-900/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               placeholder="e.g. Apex Tech Store">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-indigo-400 mb-1.5">Store Description</label>
                        <textarea name="store_description" rows="2"
                                  class="w-full px-4 py-2.5 bg-slate-950/60 border border-indigo-900/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                  placeholder="Describe what kind of products your vendor store sells...">{{ old('store_description') }}</textarea>
                    </div>
                </div>

                <!-- Password and Confirm Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ showPassword: false, showConfirmPassword: false }">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" name="password" required
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Min 6 characters">
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors"
                                    :title="showPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle password visibility">
                                <svg x-show="!showPassword" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required
                                   class="w-full pl-10 pr-11 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Repeat password">
                            <button type="button" 
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors"
                                    :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle password confirmation visibility">
                                <svg x-show="!showConfirmPassword" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-600/30 transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Register & Launch Dashboard</span>
                    </button>
                </div>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center pt-4 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    Already registered? 
                    <a href="{{ route('admin.login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold underline underline-offset-4">
                        Sign In here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
