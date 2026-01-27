<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-teal-900/80 via-teal-800/70 to-teal-900/90">
    <div class="w-full max-w-6xl grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 px-4">

        <!-- Left Column - System Information -->
        <div class="flex flex-col justify-center space-y-8">
            <!-- Logo/Title -->
            <div class="space-y-4">
                <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center border border-white/30 shadow-lg">
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" class="w-16 h-16 rounded-full object-cover" loading="lazy">
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-white">POS System</h1>
                    <p class="text-white/70 text-lg mt-2">Point of Sale Management</p>
                </div>
            </div>

            <!-- Features -->
            <div class="space-y-4">
                <h3 class="text-white font-semibold text-lg">Fitur Utama:</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-teal-300 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white/90">Manajemen Penjualan Real-time</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-teal-300 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white/90">Dashboard Analitik Penjualan</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-teal-300 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white/90">Kelola Inventori Produk</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-teal-300 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white/90">Laporan Keuangan Terintegrasi</span>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="bg-teal-800/40 backdrop-blur-md rounded-xl p-4 border border-teal-300/20 shadow-lg">
                <p class="text-white/90 text-sm leading-relaxed">
                    Sistem POS yang modern dan efisien untuk mengoptimalkan operasional bisnis Anda dengan teknologi terkini.
                </p>
            </div>
        </div>

        <!-- Right Column - Login Form -->
        <div class="flex items-center justify-center">
            <div class="bg-teal-900/40 backdrop-blur-lg rounded-2xl border border-teal-300/20 shadow-2xl overflow-hidden w-full max-w-md">
                <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-8 py-6 border-b border-teal-400/30">
                    <h2 class="text-2xl font-bold text-white">Masuk Sistem</h2>
                    <p class="text-teal-50/90 text-sm mt-1">Silakan login untuk melanjutkan</p>
                </div>

                <div class="px-8 py-8">
                    <form wire:submit="login" class="space-y-6">
                <!-- Error Alert -->
                @if ($error)
                    <div
                        class="bg-red-500/20 backdrop-blur border border-red-400/30 text-red-100 px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold">Login Gagal</p>
                            <p class="text-sm">{{ $error }}</p>
                        </div>
                    </div>
                @endif

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white/90 mb-2">
                        Email
                    </label>
                    <input id="email" type="email" wire:model="email" placeholder="your@email.com"
                        class="w-full px-4 py-3 bg-teal-800/30 backdrop-blur border border-teal-300/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-teal-400/50 focus:border-teal-300/50 transition"
                        required>
                    @error('email')
                        <p class="text-red-200 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white/90 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" wire:model="password" placeholder="••••••••"
                            class="w-full px-4 py-3 pr-12 bg-teal-800/30 backdrop-blur border border-teal-300/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-teal-400/50 focus:border-teal-300/50 transition"
                            required>
                        <button type="button" @click="document.getElementById('password').type = document.getElementById('password').type === 'password' ? 'text' : 'password'"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-white/60 hover:text-white/90 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-200 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" type="checkbox" wire:model="remember"
                        class="w-4 h-4 text-teal-300 rounded focus:ring-teal-300/40 border border-teal-300/40 bg-teal-800/30">
                    <label for="remember" class="ml-2 text-sm text-white/90">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <!-- Login Button -->
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 backdrop-blur border border-teal-400/30 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg hover:shadow-teal-500/20">
                    <span wire:loading.remove>
                        <svg class="w-5 h-5 inline mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M10 8l4 4-4 4"></path>
                            <path d="M8 12h8"></path>
                        </svg>
                        Masuk
                    </span>
                    <span wire:loading>
                        <svg class="w-5 h-5 inline animate-spin" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Memproses...
                    </span>
                </button>

                <!-- Info -->
                <div class="bg-teal-800/30 backdrop-blur border border-teal-300/20 text-white/90 px-4 py-3 rounded-lg text-sm">
                    <p class="font-semibold mb-1 text-teal-50">Demo Credentials:</p>
                    <p>Email: <code class="bg-teal-900/50 px-2 py-1 rounded border border-teal-300/20">admin@gmail.com</code></p>
                    <p>Password: <code class="bg-teal-900/50 px-2 py-1 rounded border border-teal-300/20">admin123</code></p>
                </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="border-t border-teal-300/20 px-8 py-4 bg-teal-800/30 backdrop-blur">
                    <p class="text-center text-teal-100/70 text-sm">
                        &copy; {{ date('Y') }} IDNACODE. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
